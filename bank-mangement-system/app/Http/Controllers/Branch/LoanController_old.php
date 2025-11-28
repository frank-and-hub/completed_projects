<?php
namespace App\Http\Controllers\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Response;
use Validator;
use Session;  
use Redirect;
use URL;
use DB;
use App\Models\User;
use App\Models\Member;
use App\Models\Loans;
use App\Models\Files;
use App\Models\Memberloans;
use App\Models\Loanapplicantdetails;
use App\Models\Loanscoapplicantdetails;
use App\Models\Loansguarantordetails;
use App\Models\Loanotherdocs;
use App\Models\Grouploanmembers;
use App\Models\Grouploans;
use App\Models\Loaninvestmentmembers;
use App\Models\Plans;
use App\Models\SavingAccount;
use App\Models\SavingAccountTranscation;
use App\Models\TransactionReferences;
use App\Models\LoanDayBooks;
use App\Models\SamraddhCheque;
use App\Models\AccountHeads;
use App\Models\ReceivedChequePayment;
use App\Models\ReceivedCheque;
use App\Models\acountheads;
use App\Models\SamraddhBank;
use DateTime;
use PDF;
use App\Services\Sms;
use Carbon\Carbon;
use App\Http\Traits\EmiDatesTraits;
use App\Models\LoanEmisNew;
class LoanController extends Controller
{
    use EmiDatesTraits;
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the registered loans.
     *
     * @return \Illuminate\Http\Response
     */
    public function loans()
    {
        if (!in_array('Loan View', auth()
            ->user()
            ->getPermissionNames()
            ->toArray()))
        {
            return redirect()
                ->route('branch.dashboard');
        }
        $data['title'] = "Loans";
        $BranchId = branchName()->id;
        $data['bId'] = Auth::user()->id;
        $data['cBanks'] = SamraddhBank::with('bankAccount')->get();
        $data['cheques'] = SamraddhCheque::select('cheque_no', 'account_id')->where('status', 1)
            ->get();
        return view('templates.branch.loan_management.loan-listing', $data);
    }
    /**
     * Fetch loan listing data.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    
     public function loanListing(Request $request)
     {
         if ($request->ajax()) {
             $BranchId = branchName()->id;
             $arrFormData = array();
             if (!empty($_POST['searchform'])) {
                 foreach ($_POST['searchform'] as $frm_data) {
                     $arrFormData[$frm_data['name']] = $frm_data['value'];
                 }
             }
             
             $data = Memberloans::with('loan', 'savingAccountCustom')
                 ->with(['CollectorAccount' => function ($q) 
                 {
                     $q->with(['member_collector']);
                 }])
                 ->with(['loanMemberCustom' => function ($q) 
                 {
                     $q->select('id', 'member_id', 'first_name', 'last_name', 'associate_no');
                 }])
                 ->with(['loanBranch' => function ($q) 
                 {
                     $q->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                 }, 'loanMemberBankDetails' => function ($q) 
                 {
                     $q->select('id', 'member_id', 'bank_name', 'account_no', 'ifsc_code');
                 }, 'loanSavingAccount',
                 'getOutstanding' => function ($q) 
                 {
                     $q->select('loan_id', 'id', 'out_standing_amount','is_deleted','loan_type');
                 }])
                 ->where('branch_id', $BranchId)
                 ->where('company_id',$arrFormData['company_id'])
                 ->where('loan_type', '!=', 3)->orderBy('id', 'DESC');
 
 
 
             if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                 if ($arrFormData['date_from'] != '') {
                     $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));
                     if ($arrFormData['date_to'] != '') {
                         $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));
                     } else {
                         $endDate = '';
                     }
                     $data = $data->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
                 }
                 if ($arrFormData['plan'] != '') {
                     $planId = $arrFormData['plan'];
                     $data = $data->where('loan_type', '=', $planId);
                 }
                 if ($arrFormData['loan_account_number'] != '') {
                     $loan_account_number = $arrFormData['loan_account_number'];
                     $data = $data->where('account_number', '=', $loan_account_number);
                 }
                 if ($arrFormData['member_name'] != '') {
                     $name = $arrFormData['member_name'];
                     $data = $data->whereHas('loanMember', function ($query) use ($name) {
                         $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
                     });
                 }
                 if ($arrFormData['member_id'] != '') {
                     $meid = $arrFormData['member_id'];
                     $data = $data->whereHas('loanMember', function ($query) use ($meid) {
                         $query->where('members.member_id', 'LIKE', '%' . $meid . '%');
                     });
                 }
                 if ($arrFormData['associate_code'] != '') {
                     $associateCode = $arrFormData['associate_code'];
                     $data = $data->whereHas('loanMemberAssociate', function ($query) use ($associateCode) {
                         $query->where('members.associate_no', 'LIKE', '%' . $associateCode . '%');
                     });
                 }
                 if ($arrFormData['status'] != '') {
                     $status = $arrFormData['status'];
                     $data = $data->where('status', '=', $status);
                 }
             } else {
                 $data = $data->where('status', '=', 3);
             }
             $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length']);
             $data = $data->orderby('id', 'DESC')->get();
             $count = count($data);
             $totalCount = $count;
             $sno = $_POST['start'];
             $rowReturn = array();
             $authUser = auth()->user()->getPermissionNames()->toArray();
             foreach ($data as $row) {
                 //dd($row);
                 $sno++;
                 $val['DT_RowIndex'] = $sno;
                 $val['branch']  =  $row['loanBranch']->name;
                 $val['branch_code'] = $row['loanBranch']->branch_code;
                 $val['sector_name'] = $row['loanBranch']->sector;
                 $val['region_name'] = $row['loanBranch']->regan;
                 $val['zone_name'] = $row['loanBranch']->zone;
                 $val['account_number'] = $row->account_number;
                 $val['member_id'] = $row['loanMember']->member_id;
                 if ($row->status == 0) {
                     $status = 'Pending';
                 } else if ($row->status == 1) {
                     $status = 'Approved';
                 } else if ($row->status == 2) {
                     $status = 'Rejected';
                 } else if ($row->status == 3) {
                     $status = 'Clear';
                 } else if ($row->status == 4) {
                     $status = 'Due';
                 }
                 else if ($row->status == 5)
                 {
                     $status = 'Rejected';
                 }
                 else if ($row->status == 6)
                 {
                     $status = 'Hold';
                 }
                 else if ($row->status == 7)
                 {
                     $status = 'Approved Hold';
                 }
                 $val['status'] = $status;
                 $val['reason'] = $row->rejection_description;
                 $dateSys=date("Y-m-d");                
                 $balanceTotal=getAllDeposit($row['loanMember']->id,$dateSys);    
                 $val['total_deposit'] = $balanceTotal;
                 $val['insurance_charge'] = $row->insurance_charge . ' <i class="fa fa-inr"></i>';;
                 $member_name = '';
                 $plan_name = '';
                 $tenure = '';
                 if (isset($row['loanMember']->first_name)) {
                     $val['member_name'] = $row['loanMember']->first_name . ' ' . $row['loanMember']->last_name;
                 } else {
                     $val['member_name'] = 'N/A';
                 }
                 if ($row->loan_type == 1) {
                     $plan_name = 'Personal Loan';
                 } elseif ($row->loan_type == 2) {
                     $plan_name = 'Staff Loan(SL)';
                 } elseif ($row->loan_type == 3) {
                     $plan_name = 'Group Loan';
                 } elseif ($row->loan_type == 4) {
                     $plan_name = 'Loan against Investment plan(DL) ';
                 }
                 $val['plan_name'] = $plan_name;
                 if ($row->emi_option == 1) {
                     $tenure = $row->emi_period . ' Months';
                 } elseif ($row->emi_option == 2) {
                     $tenure = $row->emi_period . ' Weeks';
                 } elseif ($row->emi_option == 3) {
                     $tenure = $row->emi_period . ' Days';
                 }
                 $val['tenure'] = $tenure;
                 $val['transfer_amount'] = $row->transfer_amount . ' <i class="fas fa-rupee-sign"></i>';
                 $val['emi_amount'] = $row->emi_amount . ' <i class="fas fa-rupee-sign"></i>';
                 $val['loan_amount'] = $row->amount . ' <i class="fas fa-rupee-sign"></i>';
                 if ($row->file_charges) {
                     $val['file_charges'] = $row->file_charges . ' <i class="fas fa-rupee-sign"></i>';
                 } else {
                     $val['file_charges'] = 'N/A';
                 }
                 $file_charges_payment_mode = 'N/A';
                 if ($row->file_charge_type ==1) {
                     $file_charges_payment_mode = 'Loan Amount';
                 } else if($row->file_charge_type ==0) {
                     $file_charges_payment_mode = 'Cash';
                 }
                 else{
                     $file_charges_payment_mode = 'N/A';
                 }
                 $val['file_charges_payment_mode'] = $file_charges_payment_mode;
                 $totalbalance = $row->emi_period * $row->emi_amount;
                 $Finaloutstanding_amount = $totalbalance - $row->received_emi_amount;
                 $val['outstanding_amount'] = (isset($row['getOutstanding']->out_standing_amount )) ? $row['getOutstanding']->out_standing_amount.' <i class="fa fa-inr"></i>' : 0 ;
                 $last_recovery_date = '';
                 if ($row->approve_date) {
                     if ($row->emi_option == 1) {
                         $last_recovery_date = date('d/m/Y', strtotime("+" . $row->emi_period . " months", strtotime($row->approve_date)));
                     } elseif ($row->emi_option == 2) {
                         $days = $row->emi_period * 7;
                         $start_date = $row->approve_date;
                         $date = strtotime($start_date);
                         $date = strtotime("+" . $days . " day", $date);
                         $last_recovery_date = date('d/m/Y', $date);
                     } elseif ($row->emi_option == 3) {
                         $days = $row->emi_period;
                         $start_date = $row->approve_date;
                         $date = strtotime($start_date);
                         $date = strtotime("+" . $days . " day", $date);
                         $last_recovery_date = date('d/m/Y', $date);
                     }
                 } else {
                     $last_recovery_date = 'N/A';
                 }
                 $val['last_recovery_date'] = $last_recovery_date;
                 //$associate_code = getMemberData($row->associate_member_id);
                 if (isset($row['loanMemberCustom']->associate_no)) {
                     $val['associate_code'] = $row['loanMemberCustom']->associate_no;
                 } else {
                     $val['associate_code'] = 'N/A'; //$associate_code->associate_no;
                 }
                 $associate_name = '';
                 //$member = Member::where('id', $row->associate_member_id)->first(['id', 'first_name', 'last_name']);
                 if (isset($row['loanMemberCustom']->id)) {
                     $associate_name = $row['loanMemberCustom']->first_name . ' ' . $row['loanMemberCustom']->last_name;
                 }
                 $val['associate_name'] = $associate_name;
                 if (isset($row['loanMemberBankDetails']->bank_name)) {
                     $bankName = $row['loanMemberBankDetails']->bank_name;
                 } else {
                     $bankName = 'N/A';
                 }
                 $val['bank_name'] = $bankName;
                 if (isset($row['loanMemberBankDetails']->account_no)) {
                     $bankAccount = $row['loanMemberBankDetails']->account_no;
                 } else {
                     $bankAccount = 'N/A';
                 }
                 $val['bank_account_number'] = $bankAccount;
                 if (isset($row['loanMemberBankDetails']->ifsc_code)) {
                     $ifscCode = $row['loanMemberBankDetails']->ifsc_code;
                 } else {
                     $ifscCode = 'N/A';
                 }
                 $val['ifsc_code'] = $ifscCode;
                 if (isset($row->insurance_cgst) || isset($row->filecharge_sgst))
                 {
                     $insurance_cgst = $row->insurance_cgst . ' <i class="fa fa-inr"></i>';
                     $insurance_sgst = $row->insurance_sgst . ' <i class="fa fa-inr"></i>';
                     $insurance_igst = 'N/A';
                     $filecharge_cgst = $row->filecharge_cgst . ' <i class="fa fa-inr"></i>';
                     $filecharge_sgst = $row->filecharge_sgst . ' <i class="fa fa-inr"></i>';
                     $filecharge_igst = 'N/A';
                 }
                 if (isset($row->insurance_charge_igst) || isset($row->filecharge_igst))
                 {
                     $insurance_igst = $row->insurance_charge_igst . ' <i class="fa fa-inr"></i>';
                     $insurance_cgst = 'N/A';
                     $insurance_sgst = 'N/A';
                     $filecharge_igst = $row->filecharge_igst . ' <i class="fa fa-inr"></i>';
                     $filecharge_cgst = 'N/A' . ' <i class="fa fa-inr"></i>';
                     $filecharge_sgst = 'N/A';
                 }
                 else
                 {
                     $insurance_cgst = 'N/A';
                     $insurance_sgst = 'N/A';
                     $insurance_igst='N/A';
                     $filecharge_igst = 'N/A';
                     $filecharge_sgst = 'N/A';
                     $filecharge_cgst='N/A';
                 }
                 $val['cgst_insurance_charge'] = $insurance_cgst;
                 $val['sgst_insurance_charge'] = $insurance_sgst;
                 $val['igst_insurance_charge'] = $insurance_igst;
                 $val['igst_file_charge'] = $filecharge_igst;
                 $val['cgst_file_charge'] = $filecharge_cgst;
                 $val['sgst_file_charge'] = $filecharge_sgst;
 
                 $val['total_payment'] = loanOutsandingAmount($row->id, $row->account_number) . ' <i class="fas fa-rupee-sign"></i>';
                 if (!empty($row['approve_date'])) {
                     $val['sanction_date'] = date("d/m/Y", strtotime($row['approve_date']));
                 } else {
                     $val['sanction_date'] = 'N/A';
                 }
                 if (!empty($row['approved_date'])) {
                     $val['approved_date'] = date("d/m/Y", strtotime($row['approved_date']));
                 } else {
                     $val['approved_date'] = 'N/A';
                 }
                 $val['application_date'] = date("d/m/Y", strtotime($row['created_at']));
                 //dd($row['CollectorAccount']['member_collector']['associate_no']);
                 if (isset($row['CollectorAccount']['member_collector']['associate_no'])) {
                     $val['collectorcode'] = $row['CollectorAccount']['member_collector']['associate_no'];
                 } else {
                     $val['collectorcode'] = "N/A";
                 }
                 if (isset($row['CollectorAccount']['member_collector']['first_name'])) {
                     $val['collectorname'] = $row['CollectorAccount']['member_collector']['first_name'] . ' ' . $row['CollectorAccount']['member_collector']['last_name'];
                 } else {
                     $val['collectorname'] = "N/A";
                 }
                 $val['status'] = $row->status;
                 if ($row->emi_option == 1) {
                     $closingAmountROI = $row->due_amount * $row->ROI / 1200;
                 } elseif ($row->emi_option == 2) {
                     $closingAmountROI = $row->due_amount * $row->ROI / 5200;
                 } elseif ($row->emi_option == 3) {
                     $closingAmountROI = $row->due_amount * $row->ROI / 36500;
                 } else {
                     $closingAmountROI = 0;
                 }
                 $closingAmount = round($row->due_amount + $closingAmountROI);
                 $ssbAmount = $row['savingAccountCustom']; //getMemberSsbAccountDetail($row['loanMember']->id);
                 if (isset($row->loanSavingAccount)) {
                     $ssbBalance = $row->loanSavingAccount->balance; //$ssbAmount['balance'];
                     $ssbId = $row->loanSavingAccount->id; //$ssbAmount['id'];
                     $ssbAccount = $row->loanSavingAccount->account_no;
                 } else {
                     $ssbBalance = 0;
                     $ssbId = 0;
                     $ssbAccount = 0;
                 }
                 $btn = '';
                 $urlCom = URL::to("branch/loan/commission/" . $row->id . "");
                 $purl = URL::to("branch/loan/print/" . $row->id . "/" . $row->loan_type . "");
                 $turl = URL::to("branch/loan/emi-transactions/" . $row->id . "/" . $row->loan_type . "");
                 $pdf = URL::to("branch/loan/download-recovery-clear/" . $row->id . "/" . $row->loan_type . "");
                 $print = URL::to("branch/loan/print-recovery-clear/" . $row->id . "/" . $row->loan_type . "");
                 if (in_array('Loan Commission', $authUser)) {
                     $btn .= '<a href="' . $urlCom . '" title="Loan Commission"><i class="fas fa-percent text-default mr-2"></i></a>';
                 }
                 if (in_array('Loan View Detail', $authUser)) {
                     $url = URL::to("branch/loan/view/" . $row->id . "");
                     $btn .= '<a href="' . $url . '" title="View"><i class="fas fa-eye text-default mr-2"></i></a>';
                 }
                 $eurl = URL::to("branch/loan/" . $row->id . "");
                 if ($row->status == 0) {
                     if (in_array('Loan Edit', $authUser)) {
                         $btn .= '<a href="' . $eurl . '" title="Edit"><i class="fa fa-edit mr-2" aria-hidden="true"></i></a>';
                     }
                 }
                 if ($row->status == 2 && $row->edit_reject_request == 0) {
                     $approvalrequesturl = URL::to("branch/loan/sendapprovalrequest/" . $row->id . "");
                     $btn .= '<a href="' . $approvalrequesturl . '" title="Send Approval Request"><i class="fa fa-thumbs-up mr-2" aria-hidden="true"></i></a>';
                 }
                 if ($row->status == 4) {
                     if ($row->emi_option == 1) {
                         $closingDate = date('Y-m-d', strtotime("+" . $row->emi_period . " months", strtotime($row->created_at)));
                     } elseif ($row->emi_option == 2) {
                         $days = $row->emi_period * 7;
                         $start_date = $row->created_at;
                         $date = strtotime($start_date);
                         $date = strtotime("+" . $days . " day", $date);
                         $closingDate = date('Y-m-d', $date);
                     } elseif ($row->emi_option == 3) {
                         $days = $row->emi_period;
                         $start_date = $row->created_at;
                         $date = strtotime($start_date);
                         $date = strtotime("+" . $days . " day", $date);
                         $closingDate = date('Y-m-d', $date);
                     }
                     if (date('Y-m-d') > $closingDate) {
                         if ($row->emi_option == 1) {
                             $loanStartDate = $closingDate;
                             $loanComplateDate = date('Y-m-d');
                             $ts1 = strtotime($loanStartDate);
                             $ts2 = strtotime($loanComplateDate);
                             $year1 = date('Y', $ts1);
                             $year2 = date('Y', $ts2);
                             $month1 = date('m', $ts1);
                             $month2 = date('m', $ts2);
                             $penaltyTime = (($year2 - $year1) * 12) + ($month2 - $month1);
                             $penaltyAmount = round($penaltyTime * $closingAmountROI);
                         } elseif ($row->emi_option == 2) {
                             $loanStartDate = $closingDate;
                             $startDate = date("m/d/Y", strtotime(convertDate($loanStartDate)));
                             $endDate = date('m/d/Y');
                             $first = DateTime::createFromFormat('m/d/Y', $startDate);
                             $second = DateTime::createFromFormat('m/d/Y', $endDate);
                             $penaltyTime = floor($first->diff($second)->days / 7);
                             $penaltyAmount = round($penaltyTime * $closingAmountROI);
                         } elseif ($row->emi_option == 3) {
                             $startDate = strtotime($closingDate);
                             $endDate = strtotime(date('Y-m-d'));
                             $datediff = $endDate - $startDate;
                             $penaltyTime = round($datediff / (60 * 60 * 24));
                             $penaltyAmount = round($penaltyTime * $closingAmountROI);
                         } elseif ($row->emi_option == 4) {
                             $penaltyTime = '';
                             $penaltyAmount = '';
                         }
                     } else {
                         $penaltyTime = '';
                         $penaltyAmount = '';
                     }
                     if ($row->emi_option == 1) {
                         $loanComplateDate = date('Y-m-d');
                         $dueStartDate = $row->approve_date;
                         $dts1 = strtotime($dueStartDate);
                         $dts2 = strtotime($loanComplateDate);
                         $dyear1 = date('Y', $dts1);
                         $dyear2 = date('Y', $dts2);
                         $dmonth1 = date('m', $dts1);
                         $dmonth2 = date('m', $dts2);
                         $dueTime = (($dyear2 - $dyear1) * 12) + ($dmonth2 - $dmonth1);
                         $cAmount = round($dueTime * $row->emi_amount);
                         $dueAmount = round($cAmount - $row->received_emi_amount);
                     } elseif ($row->emi_option == 2) {
                         $loanStartDate = $row->approve_date;
                         $startDate = date("m/d/Y", strtotime(convertDate($loanStartDate)));
                         $endDate = date('m/d/Y');
                         $first = DateTime::createFromFormat('m/d/Y', $startDate);
                         $second = DateTime::createFromFormat('m/d/Y', $endDate);
                         $dueTime = floor($first->diff($second)->days / 7);
                         $cAmount = round($dueTime * $row->emi_amount);
                         $dueAmount = round($cAmount - $row->received_emi_amount);
                     } elseif ($row->emi_option == 3) {
                         $startDate = strtotime($row->approve_date);
                         $endDate = strtotime(date('Y-m-d'));
                         $datediff = $endDate - $startDate;
                         $dueTime = round($datediff / (60 * 60 * 24));
                         $cAmount = round($dueTime * $row->emi_amount);
                         $dueAmount = round($cAmount - $row->received_emi_amount);
                     } elseif ($row->emi_option == 4) {
                         $dueAmount = 0;
                     }
                     $LoanCreatedDate = date('Y-m-d', strtotime($row->approve_date));
                     $LoanCreatedYear = date('Y', strtotime($row->approve_date));
                     $LoanCreatedMonth = date('m', strtotime($row->approve_date));
                     $LoanCreateDate = date('d', strtotime($row->approve_date));
                     $currentDate = date('Y-m-d');
                     $CurrentDate = date('d');
                     $CurrentDateYear = date('Y');
                     $CurrentDateMonth = date('m');
                     if ($row->emi_option == 1) {
                         $daysDiff = (($CurrentDateYear - $LoanCreatedYear) * 12) + ($CurrentDateMonth - $LoanCreatedMonth);
                     }
                     if ($row->emi_option == 2) {
                         $daysDiff = today()->diffInDays($LoanCreatedDate);
                         $daysDiff = $daysDiff / 7;
                     }
                     if ($row->emi_option == 3) {
                         $daysDiff = today()->diffInDays($LoanCreatedDate);
                     }
                     $nextEmiDates = $this->nextEmiDates($daysDiff, $LoanCreatedDate);
                     $EmiDates = $this->emiDates($LoanCreatedDate, $row->emi_period);
                     $emi_date = $nextEmiDates;
                     if (in_array('Loan Pay EMI', $authUser)) {
                         $btn .= '<a class="pay-emi" href="javascript:void(0);" title="Pay EMI" data-loan-id="' . $row->id . '" data-loan-emi="' . $row->emi_amount . '" data-ssb-amount="' . $ssbBalance . '" data-ssb-id="' . $ssbId . '" data-recovered-amount="' . loanOutsandingAmount($row->id, $row->account_number) . '"  data-last-recovered-amount="' . lastLoanRecoveredAmount($row->id, 'loan_id') . '" data-closing-amount="' . $closingAmount . '" data-due-amount="' . $dueAmount . '" data-penalty-amount="' . $penaltyAmount . '" data-penalty-time="' . $penaltyTime . '"  data-toggle="modal" data-target="#pay-loan-emi"data-ssb-account="' . $ssbAccount . '"  data-emiDate = "' . implode(',', $emi_date) . '" data-AllemiDate = "' . implode(',', $EmiDates) . '" data-emiOption = "' . $row->emi_option . '"><i class="fas fa-hand-holding-usd mr-2"></i></a>  ';
                         if ($row->emi_option == 1) {
                             $btn .= '<a class="pay-emi" href="javascript:void(0);" title="Advance Pay EMI" data-loan-id="' . $row->id . '" data-loan-emi="' . $row->emi_amount . '" data-ssb-amount="' . $ssbBalance . '" data-ssb-id="' . $ssbId . '" data-recovered-amount="' . loanOutsandingAmount($row->id, $row->account_number) . '"  data-last-recovered-amount="' . lastLoanRecoveredAmount($row->id, 'loan_id') . '" data-closing-amount="' . $closingAmount . '" data-due-amount="' . $dueAmount . '" data-penalty-amount="' . $penaltyAmount . '" data-penalty-time="' . $penaltyTime . '"  data-toggle="modal" data-target="#pay-loan-emi"data-ssb-account="' . $ssbAccount . '"  data-emiDate = "' . implode(',', $emi_date) . '" data-AllemiDate = "' . implode(',', $EmiDates) . '" data-emiOption = "' . $row->emi_option . '"><i class="fas fa-hand-holding-usd mr-2"></i></a>  ';
                         }
                     }
                 }
                 if (in_array('Loan Transactions', $authUser)) {
                     $btn .= '<a href="' . $turl . '" title="transactions"><i class="ni ni-chart-bar-32 text-default mr-2" aria-hidden="true"></i></a>';
                 }
                 if (in_array('Loan Print PDF form', $authUser)) {
                     if ($row->status != 1) {
                         $btn .= '<a href="' . $purl . '" title="print" target="_blank"><i class="fa fa-print mr-2" aria-hidden="true"></i></a>';
                     }
                 }
                 if ($row->status == 3) {
                     if (in_array('Loan Print No Dues', $authUser)) {
                         if ($row->no_dues_print == 0) {
                             $btn .= '<a  title="print No Dues" href="' . $print . '" target="_blank"><i class="fas fa-print text-default mr-2" ></i></a>';
                         }
                     }
                 }
                 $val['action'] = $btn;
                 $rowReturn[] = $val;
             }
             $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn,);
             return json_encode($output);
         }
     }
    /**
     * Group Loan requests listing view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function groupLoan(Request $request)
    {
        if (!in_array('Group Loans Details', auth()->user()
            ->getPermissionNames()
            ->toArray()))
        {
            return redirect()
                ->route('branch.dashboard');
        }
        $data['title'] = 'Group Loan Registration Details';
        $BranchId = branchName()->id;
        $data['bId'] = Auth::user()->id;
        $data['cBanks'] = SamraddhBank::with('bankAccount')->get();
        $data['cheques'] = SamraddhCheque::select('cheque_no', 'account_id')->where('status', 1)
            ->get();
        return view('templates.branch.loan_management.group-loan-listing', $data);
    }
    /**
     * Show group loan requests listing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function groupLoanListing(Request $request)
    {
        if ($request->ajax()) {
            $BranchId = branchName()->id;
            // $data = Memberloans::with('loan')->where('status',0)->orderBy('id', 'DESC')->get();
            //$auth = auth()->user()->getPermissionNames()->toArray();
            $arrFormData = array();
            if (!empty($_POST['searchGroupLoanForm'])) {
                foreach ($_POST['searchGroupLoanForm'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            
            $data = Grouploans::with(['loanMember', 'gloanBranch', 'loanMemberBankDetails2' => function ($q) {
                $q->select('id', 'member_id', 'bank_name', 'account_no', 'ifsc_code');
            }])->with(['CollectorAccount' => function ($q) {
                $q->with(['member_collector']);
            },'getOutstanding' => function ($q) {
                $q->select('loan_id', 'id', 'out_standing_amount','is_deleted','loan_type');
            }])->where('branch_id', $BranchId)->where('company_id',$arrFormData['company_id']);

            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['date_from'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));
                    if ($arrFormData['date_to'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
                }
                if ($arrFormData['loan_account_number'] != '') {
                    $loan_account_number = $arrFormData['loan_account_number'];
                    $data = $data->where('account_number', '=', $loan_account_number);
                }
                if ($arrFormData['member_name'] != '') {
                    $name = $arrFormData['member_name'];
                    $data = $data->whereHas('loanMember', function ($query) use ($name) {
                        $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
                    });
                }
                if ($arrFormData['member_id'] != '') {
                    $meid = $arrFormData['member_id'];
                    $data = $data->whereHas('loanMember', function ($query) use ($meid) {
                        $query->where('members.member_id', 'LIKE', '%' . $meid . '%');
                    });
                }
                if ($arrFormData['associate_code'] != '') {
                    $associateCode = $arrFormData['associate_code'];
                    $data = $data->whereHas('loanMemberAssociate', function ($query) use ($associateCode) {
                        $query->where('members.associate_no', 'LIKE', '%' . $associateCode . '%');
                    });
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', '=', $status);
                }
				
				if ($arrFormData['group_loan_common_id'] != '') {
                    $data = $data->where('group_loan_common_id', '=', $arrFormData['group_loan_common_id']);
                }
				
				// dd($data->get('branch_id')->toArray()); 
            } else {
                $data = $data->where('status', '=', 3);
            }
            $data = $data->orderby('id', 'DESC')
                ->get();
            return Datatables::of($data)
                ->addIndexColumn()->addColumn('branch', function ($row) {
                    return $row['gloanBranch']->name;
                })
                ->rawColumns(['branch'])->addColumn('branch_code', function ($row) {
                    $branch_code = $row['gloanBranch']->branch_code;
                    return $branch_code;
                })->rawColumns(['branch_code'])->addColumn('sector_name', function ($row) {
                    $sector = $row['gloanBranch']->sector;
                    return $sector;
                })->rawColumns(['sector_name'])->addColumn('region_name', function ($row) {
                    $regan = $row['gloanBranch']->regan;
                    return $regan;
                })->rawColumns(['region_name'])->addColumn('zone_name', function ($row) {
                    $zone = $row['gloanBranch']->zone;
                    return $zone;
                })->rawColumns(['zone_name'])->addColumn('account_number', function ($row) {
                    $account_number = $row->account_number;
                    return $account_number;
                })->rawColumns(['zone_name'])->addColumn('reason', function ($row) {
                    $des = $row->rejection_description;
                    return $des;
                })->rawColumns(['account_number'])->addColumn('member_name', function ($row) {
                    $member_name = $row['loanMember']->first_name . ' ' . $row['loanMember']->last_name;
                    return $member_name;
					
				})->rawColumns(['group_loan_common_id'])->addColumn('group_loan_common_id', function ($row) {
                    $group_loan_common_id = $row->group_loan_common_id;
                    return $group_loan_common_id;
					
                })->rawColumns(['member_name'])->addColumn('member_id', function ($row) {
                    $member_id = $row['loanMember']->member_id;
                    return $member_id;
                })->rawColumns(['member_id'])->addColumn('total_deposit', function ($row) {
                    
                    $dateSys=date("Y-m-d");                
                    $balanceTotal=getAllDeposit( $row['loanMember']->id,$dateSys);    
                    $total_deposit = $balanceTotal;
                    return $total_deposit;
                })->rawColumns(['total_deposit'])->addColumn('plan_name', function ($row) {
                    return 'Group Loan';
                })->rawColumns(['plan_name'])->addColumn('insurance_charge', function ($row) {
                    return $row->insurance_charge;
                })->rawColumns(['insurance_charge'])->addColumn('tenure', function ($row) {
                    $tenure  = '';
                    if ($row->emi_option == 1) {
                        $tenure = $row->emi_period . ' Months';
                    } elseif ($row->emi_option == 2) {
                        $tenure = $row->emi_period . ' Weeks';
                    } elseif ($row->emi_option == 3) {
                        $tenure = $row->emi_period . ' Days';
                    }
                    return $tenure;
                })->rawColumns(['escapeColumns'])->addColumn('emi_amount', function ($row) {
                    $loan_amount = $row->emi_amount . ' <i class="fas fa-rupee-sign"></i>';
                    return $loan_amount;
                })->escapeColumns(['tenure'])->addColumn('file_charges', function ($row) {
                    if ($row->file_charges) {
                        $file_charge = $row->file_charges . ' <i class="fas fa-rupee-sign"></i>';
                    } else {
                        $file_charge = 'N/A';
                    }
                    return $file_charge;
                })->escapeColumns(['escapeColumns'])->addColumn('file_charges_payment_mode', function ($row) {
                    /*
                $file_charges_payment_mode = fileChargePaymentMode($row->id,10);
                return $file_charges_payment_mode;
                */
                    $file_charges_payment_mode = 'N/A';
                    if ($row->file_charge_type) {
                        $file_charges_payment_mode = 'Loan Amount';
                    } else {
                        $file_charges_payment_mode = 'Cash';
                    }
                    return $file_charges_payment_mode;
                })->rawColumns(['file_charges_payment_mode'])->addColumn('loan_amount', function ($row) {
                    $loan_amount = $row->transfer_amount . ' <i class="fas fa-rupee-sign"></i>';
                    return $loan_amount;
                })->escapeColumns(['escapeColumns'])->addColumn('amount', function ($row) {
                    $loan_amount = $row->amount . ' <i class="fas fa-rupee-sign"></i>';
                    return $loan_amount;
                })->escapeColumns(['escapeColumns'])->addColumn('outstanding_amount', function ($row) {
                    $outstanding_amount=   (isset($row['getOutstanding']->out_standing_amount )) ? $row['getOutstanding']->out_standing_amount.' <i class="fa fa-inr"></i>' : 0 ;

                    return $outstanding_amount;
                })->escapeColumns(['escapeColumns'])->addColumn('last_recovery_date', function ($row) {
                    //$last_recovery_date =  getLastGropLoanEmiDate($row->id);
                    if ($row->approve_date) {
                        if ($row->emi_option == 1) {
                            $last_recovery_date = date('d/m/Y', strtotime("+" . $row->emi_period . " months", strtotime($row->approve_date)));
                        } elseif ($row->emi_option == 2) {
                            $days = $row->emi_period * 7;
                            $start_date = $row->approve_date;
                            $date = strtotime($start_date);
                            $date = strtotime("+" . $days . " day", $date);
                            $last_recovery_date = date('d/m/Y', $date);
                        } elseif ($row->emi_option == 3) {
                            $days = $row->emi_period;
                            $start_date = $row->approve_date;
                            $date = strtotime($start_date);
                            $date = strtotime("+" . $days . " day", $date);
                            $last_recovery_date = date('d/m/Y', $date);
                        }
                    } else {
                        $last_recovery_date = 'N/A';
                    }
                    return $last_recovery_date;
                })->rawColumns(['last_recovery_date'])->addColumn('associate_code', function ($row) {
                    $associate_code = getMemberData($row->associate_member_id);
                    return $associate_code->associate_no;
                })
                ->rawColumns(['associate_code'])->addColumn('associate_name', function ($row) {
                    $member = Member::where('id', $row->associate_member_id)
                        ->first(['id', 'first_name', 'last_name']);
                    $associate_name = $member->first_name . ' ' . $member->last_name;
                    return $associate_name;
                })->rawColumns(['associate_name'])->addColumn('total_payment', function ($row) {
                    $total_payment = loanGroupOutsandingAmount($row->id) . ' <i class="fas fa-rupee-sign"></i>';
                    return $total_payment;
                })->rawColumns(['escapeColumns'])->addColumn('bank_name', function ($row) {
                    if (isset($row['loanMemberBankDetails2']->bank_name)) {
                        $bankName = $row['loanMemberBankDetails2']->bank_name;
                    } else {
                        $bankName = 'N/A';
                    }
                    return $bankName;
                })->rawColumns(['escapeColumns'])->addColumn('bank_account_number', function ($row) {
                    if (isset($row['loanMemberBankDetails2']->account_no)) {
                        $bankAccount = $row['loanMemberBankDetails2']->account_no;
                    } else {
                        $bankAccount = 'N/A';
                    }
                    return $bankAccount;
                })->rawColumns(['escapeColumns'])->addColumn('ifsc_code', function ($row) {
                    if (isset($row['loanMemberBankDetails2']->ifsc_code)) {
                        $ifscCode = $row['loanMemberBankDetails2']->ifsc_code;
                    } else {
                        $ifscCode = 'N/A';
                    }
                    return $ifscCode;
                })->escapeColumns(['escapeColumns'])->addColumn('approve_date', function ($row) {
                    if ($row['approved_date']) {
                        return date("d/m/Y", strtotime($row['approved_date']));
                    } else {
                        return 'N/A';
                    }
                })->escapeColumns(['escapeColumns'])->addColumn('sanction_date', function ($row) {
                    if ($row['approve_date']) {
                        return date("d/m/Y", strtotime($row['approve_date']));
                    } else {
                        return 'N/A';
                    }
                })->rawColumns(['sanction_date'])->addColumn('application_date', function ($row) {
                    return date("d/m/Y", strtotime($row['created_at']));
                })->rawColumns(['application_date'])->addColumn('collector_code', function ($row) {
                    if (isset($row['CollectorAccount']['member_collector']->associate_no)) {
                        $collector_code = $row['CollectorAccount']['member_collector']->associate_no;
                    } else {
                        $collector_code = "N/A";
                    }
                    return $collector_code;
                })->rawColumns(['collector_code'])->addColumn('collector_name', function ($row) {
                    if (isset($row['CollectorAccount']['member_collector']['first_name'])) {
                        $collector_name = $row['CollectorAccount']['member_collector']['first_name'] . ' ' . $row['CollectorAccount']['member_collector']['last_name'];
                    } else {
                        $collector_name = "N/A";
                    }
                    return $collector_name;
                })->rawColumns(['collector_name'])->addColumn('status', function ($row) {
                    if ($row->status == 0) {
                        $status = 'Pending';
                    } else if ($row->status == 1) {
                        $status = 'Approved';
                    } else if ($row->status == 2) {
                        $status = 'Rejected';
                    } else if ($row->status == 3) {
                        $status = 'Clear';
                    } else if ($row->status == 4) {
                        $status = 'Due';
                    }
                    else if ($row->status == 5)
                    {
                        $status = 'Rejected';
                    }
                    else if ($row->status == 6)
                    {
                        $status = 'Hold';
                    }
                    else if ($row->status == 7)
                    {
                        $status = 'Approved Hold';
                    }
                    return $status;
                })->rawColumns(['status'])->addColumn('action', function ($row) {
                    $closingAmountROI = 0;
                    if ($row->emi_option == 1) {
                        $closingAmountROI = $row->due_amount * $row->ROI / 1200;
                    } elseif ($row->emi_option == 2) {
                        $closingAmountROI = $row->due_amount * $row->ROI / 5200;
                    } elseif ($row->emi_option == 3) {
                        $closingAmountROI = $row->due_amount * $row->ROI / 36500;
                    }
                    $closingAmount = round($row->due_amount + $closingAmountROI);
                    $ssbAmount = getMemberSsbAccountDetail($row['loanMember']->id);
                    if ($ssbAmount) {
                        $ssbBalance = $ssbAmount['balance'];
                        $ssbId = $ssbAmount['id'];
                        $ssbAccount = $ssbAmount['account_no'];
                    } else {
                        $ssbBalance = 0;
                        $ssbId = 0;
                        $ssbAccount = 0;
                    }
                    $btn = '';
                    $urlCom = URL::to("branch/loan/commission-group/" . $row->id . "");
                    $turl = URL::to("branch/loan/emi-transactions/" . $row->id . "/3");
                    if (in_array('Group Loan Commission', auth()->user()->getPermissionNames()->toArray())) {
                        $btn .= '<a href="' . $urlCom . '" title="Loan Commission"><i class="fas fa-percent text-default mr-2"></i></a>';
                    }
                    /*if( in_array('Group Loan Edit', auth()->user()->getPermissionNames()->toArray() ) ) {
                    $url = URL::to("branch/loan/" . $row->id . "");
                    $btn = '<a class="dropdown-item" href="' . $url . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                } else*/
                    if (in_array('Group Loan View Detail', auth()->user()->getPermissionNames()->toArray())) {
                        $url = URL::to("branch/loan/view/" . $row->member_loan_id . "");
                        $btn .= '<a href="' . $url . '" title="View"><i class="fas fa-eye text-default mr-2"></i></a>';
                    } /*else {
                    $btn = '';
                }*/
                    $eurl = URL::to("branch/loan/" . $row->member_loan_id . "");
                    if ($row->status == 0) {
                        if (in_array('Group Loan Edit', auth()->user()->getPermissionNames()->toArray())) {
                            $btn .= '<a href="' . $eurl . '" title="Edit"><i class="fa fa-edit mr-2" aria-hidden="true"></i></a>';
                        }
                    }
                    if ($row->status == 2 && $row->edit_reject_request == 0) {
                        $approvalrequesturl = URL::to("branch/loan/sendapprovalrequest/" . $row->id . "");
                        if (in_array('Group Loan Send Approval Request', auth()->user()->getPermissionNames()->toArray())) {
                            $btn .= '<a href="' . $approvalrequesturl . '" title="Send Approval Request"><i class="fa fa-thumbs-up mr-2" aria-hidden="true"></i></a>';
                        }
                    }
                    if (in_array('Group Loan Transactions', auth()->user()->getPermissionNames()->toArray())) {
                        $btn .= '<a href="' . $turl . '" title="transactions"><i class="ni ni-chart-bar-32 text-default mr-2" aria-hidden="true"></i></a>';
                    }
                    if ($row->status == 4) {
                        if ($row->emi_option == 1) {
                            $closingDate = date('Y-m-d', strtotime("+" . $row->emi_period . " months", strtotime($row->created_at)));
                        } elseif ($row->emi_option == 2) {
                            $days = $row->emi_period * 7;
                            $start_date = $row->created_at;
                            $date = strtotime($start_date);
                            $date = strtotime("+" . $days . " day", $date);
                            $closingDate = date('Y-m-d', $date);
                        } elseif ($row->emi_option == 3) {
                            $days = $row->emi_period;
                            $start_date = $row->created_at;
                            $date = strtotime($start_date);
                            $date = strtotime("+" . $days . " day", $date);
                            $closingDate = date('Y-m-d', $date);
                        }
                        if (date('Y-m-d') > $closingDate) {
                            if ($row->emi_option == 1) {
                                $loanStartDate = $closingDate;
                                $loanComplateDate = date('Y-m-d');
                                $ts1 = strtotime($loanStartDate);
                                $ts2 = strtotime($loanComplateDate);
                                $year1 = date('Y', $ts1);
                                $year2 = date('Y', $ts2);
                                $month1 = date('m', $ts1);
                                $month2 = date('m', $ts2);
                                $penaltyTime = (($year2 - $year1) * 12) + ($month2 - $month1);
                                $penaltyAmount = round($penaltyTime * $closingAmountROI);
                            } elseif ($row->emi_option == 2) {
                                $loanStartDate = $closingDate;
                                $startDate = date("m/d/Y", strtotime(convertDate($loanStartDate)));
                                $endDate = date('m/d/Y');
                                $first = DateTime::createFromFormat('m/d/Y', $startDate);
                                $second = DateTime::createFromFormat('m/d/Y', $endDate);
                                $penaltyTime = floor($first->diff($second)->days / 7);
                                $penaltyAmount = round($penaltyTime * $closingAmountROI);
                            } elseif ($row->emi_option == 3) {
                                $startDate = strtotime($closingDate);
                                $endDate = strtotime(date('Y-m-d'));
                                $datediff = $endDate - $startDate;
                                $penaltyTime = round($datediff / (60 * 60 * 24));
                                $penaltyAmount = round($penaltyTime * $closingAmountROI);
                            } elseif ($row->emi_option == 4) {
                                $penaltyTime = '';
                                $penaltyAmount = '';
                            }
                        } else {
                            $penaltyTime = '';
                            $penaltyAmount = '';
                        }
                        if ($row->emi_option == 1) {
                            $loanComplateDate = date('Y-m-d');
                            $dueStartDate = $row->approve_date;
                            $dts1 = strtotime($dueStartDate);
                            $dts2 = strtotime($loanComplateDate);
                            $dyear1 = date('Y', $dts1);
                            $dyear2 = date('Y', $dts2);
                            $dmonth1 = date('m', $dts1);
                            $dmonth2 = date('m', $dts2);
                            $dueTime = (($dyear2 - $dyear1) * 12) + ($dmonth2 - $dmonth1);
                            $cAmount = round($dueTime * $row->emi_amount);
                            $dueAmount = round($cAmount - $row->received_emi_amount);
                        } elseif ($row->emi_option == 2) {
                            $loanStartDate = $row->approve_date;
                            $startDate = date("m/d/Y", strtotime(convertDate($loanStartDate)));
                            $endDate = date('m/d/Y');
                            $first = DateTime::createFromFormat('m/d/Y', $startDate);
                            $second = DateTime::createFromFormat('m/d/Y', $endDate);
                            $dueTime = floor($first->diff($second)->days / 7);
                            $cAmount = round($dueTime * $row->emi_amount);
                            $dueAmount = round($cAmount - $row->received_emi_amount);
                        } elseif ($row->emi_option == 3) {
                            $startDate = strtotime($row->approve_date);
                            $endDate = strtotime(date('Y-m-d'));
                            $datediff = $endDate - $startDate;
                            $dueTime = round($datediff / (60 * 60 * 24));
                            $cAmount = round($dueTime * $row->emi_amount);
                            $dueAmount = round($cAmount - $row->received_emi_amount);
                        } elseif ($row->emi_option == 4) {
                            $dueAmount = 0;
                        }
                        if (in_array('Group Loan Pay EMI', auth()->user()
                            ->getPermissionNames()
                            ->toArray())) {
                            $btn .= '<a class="pay-emi" href="javascript:void(0);" title="Pay EMI" data-loan-id="' . $row->id . '" data-loan-emi="' . $row->emi_amount . '" data-ssb-amount="' . $ssbBalance . '" data-ssb-id="' . $ssbId . '" data-recovered-amount="' . loanGroupOutsandingAmount($row->id) . '"  data-last-recovered-amount="' . lastLoanRecoveredAmount($row->id, 'group_loan_id') . '" data-closing-amount="' . $closingAmount . '" data-due-amount="' . $dueAmount . '" data-penalty-amount="' . $penaltyAmount . '" data-penalty-time="' . $penaltyTime . '" data-toggle="modal" data-target="#pay-loan-emi" data-ssb-account="' . $ssbAccount . '"><i class="fas fa-hand-holding-usd mr-2"></i></a>  ';
                        }
                    }
                    $purl = URL::to("branch/loan/print/" . $row->member_loan_id . "/3");
                    $pdf = URL::to("branch/loan/download-recovery-clear/" . $row->member_loan_id . "/3");
                    $print = URL::to("branch/loan/print-recovery-clear/" . $row->member_loan_id . "/3");
                    if (in_array('Group Loan Print PDF form', auth()->user()->getPermissionNames()->toArray())) {
                        $btn .= '<a href="' . $purl . '" title="print" target="_blank"><i class="fa fa-print mr-2" aria-hidden="true"></i></a>';
                    }
                    $grpCount = Grouploans::where('member_loan_id', $row->member_loan_id)
                        ->count();
                    $grpCountStatus = Grouploans::where('member_loan_id', $row->member_loan_id)
                        ->where('status', 3)
                        ->count();
                    if ($grpCount == $grpCountStatus) {
                        //            if(in_array('Group Loan Download No Dues PDF', auth()->user()->getPermissionNames()->toArray())){
                        //           $btn .= '<a  title="Download No Dues" href="' . $pdf . '"><i class="fas fa-download text-default mr-2"></i></a>';
                        // }
                        if (in_array('Group Loan Print No Dues', auth()->user()->getPermissionNames()->toArray())) {
                            if ($row->no_dues_print == 0) {
                                $btn .= '<a  title="print No Dues" href="' . $print . '" target="_blank"><i class="fas fa-print text-default mr-2" ></i></a>';
                            }
                        }
                    }
                    return $btn;
                })->rawColumns(['action'])
                ->make(true);
        }
    }
    /**
     * Display a form of the register loan.
     *
     * @return \Illuminate\Http\Response
     */
    public function registerLoan()
    {
        $data['title'] = "Loan Registration";
        $data['loans'] = Loans::where('status', 1)->get();
        return view('templates.branch.loan_management.assign', $data);
    }
    /**
     * Get branch member details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getmember(Request $request)
    {
       
        $existRecord  = '';
        $totalDeposit = 0;
        if ($request->memberid)
        {
            if($request->attVal != 'co-applicant')
            {
                $member = Member::where('member_id',$request->memberid)->where('is_block',0)->first('id');
              
                $dateSys=date("Y-m-d",strtotime(convertDate($request['re_member_dob'])));
                if(isset($member->id))
                {

                    $balanceTotal=getAllDeposit($member->id,$dateSys);
                    $totalDeposit=$balanceTotal;;
                    $existRecord = MemberLoans::where('applicant_id',$member->id)->where('loan_type','!=',4)->whereIn('status', [0, 1, 2, 4])->exists();

                    if(!$existRecord )
                    {
                        $existRecord = GroupLoans::where('member_id', $member->id)->whereIn('status', [0, 1, 2, 4])->exists();
                    }
                } 
            }
            if($existRecord != '' && $request->loantype != 4)
            {
                return Response::json(['msg' => 'Loan is Running on this member Id', 'msg_type' => 'warning']);
            }
            else{
                if ($request->loantype == 4)
                {
                    $data = Member::with('savingAccount', 'memberBankDetails',  'memberIdProofs')->with(['associateInvestment'=>function($q){
                        $q->where('is_mature',1);
                    }])->leftJoin('member_investments', 'members.id', '=', 'member_investments.member_id')
                        ->leftJoin('plans', 'member_investments.plan_id', '=', 'plans.id')
                        ->where('members.member_id', $request->memberid)->where('members.is_block',0)
                        ->where('members.status', 1)
                        ->where('members.is_deleted', 0)
                        ->select('members.*', 'plans.name as plan_name')
                        ->first();
                }
                else
                {
                    /*if($request->loantype==1 && $request->type == 'applicant'){
                        for ($i = 5; $i < 6; $i++) {
                        $ldate = date('d-m-Y', strtotime("-$i month"));
                        }
                        $data=Member::with('savingAccount','memberBankDetails')->where('member_id',$request->memberid)->whereDate('created_at','<',$ldate)->where('status',1)->where('is_deleted',0)->first();
                        if(empty($data)){
                            $data=Member::with('memberBankDetails')->where('members.associate_no',$request->memberid)->whereDate('created_at','<',$ldate)->where('status',1)->where('is_deleted',0)->first();
                        }
                    }else{
                        $data=Member::with('savingAccount','memberBankDetails')->where('member_id',$request->memberid)->where('status',1)->where('is_deleted',0)->first();
                        if(empty($data)){
                            $data=Member::with('memberBankDetails')->where('members.associate_no',$request->memberid)->where('status',1)->where('is_deleted',0)->first();
                        }
                    }*/
                    $data = Member::with('savingAccount', 'memberBankDetails', 'memberIdProofs')->where('member_id', $request->memberid)
                        ->where('status', 1)
                        ->where('is_deleted', 0)->where('is_block',0)
                        ->first();
                    if (empty($data))
                    {
                        $data = Member::with('savingAccount', 'memberBankDetails', 'memberIdProofs')->where('members.associate_no', $request->memberid)
                            ->where('status', 1)
                            ->where('is_deleted', 0)->where('is_block',0)
                            ->first();
                    }
                }
                if ($data)
                {
                    $gaurantorCount = Loansguarantordetails::where('member_id', $data->id)
                        ->count();
                    //dd($gaurantorCount);
                    // if($request->attVal =='applicant' && $gaurantorCount > 0){
                    //     return Response::json(['view' => 'No data found','msg_type'=>'warning']);
                    // }
                    // else{
                    $id = $data->id;
                    if (count($data['memberBankDetails']) > 0)
                    {
                        $bAccount = $data['memberBankDetails'][0]->account_no;
                        $bIfsc = $data['memberBankDetails'][0]->ifsc_code;
                        $bName = $data['memberBankDetails'][0]->bank_name;
                    }
                    else
                    {
                        $bAccount = '';
                        $bIfsc = '';
                        $bName = '';
                    }
                    $occupation = getOccupationName($data->occupation_id);
                    $carderName = getCarderName($data->current_carder_id);
                    $maritalStatus = $data->marital_status;
                    $idProofDetail = \App\Models\MemberIdProof::where('member_id', $id)->first();
                    return Response::json(['view' => view('templates.branch.loan_management.partials.member_detail', ['memberData' => $data, 'idProofDetail' => $idProofDetail])->render() , 'msg_type' => 'success', 'id' => $id, 'bAccount' => $bAccount, 'bIfsc' => $bIfsc, 'bName' => $bName, 'occupation' => $occupation, 'occupation_id' => $data->occupation_id, 'maritalStatus' => $maritalStatus, 'carderName' => $carderName, 'member' => $data,'totalDeposit' =>$totalDeposit]);
                    //}
                }
                else
                {
                    return Response::json(['view' => 'No data found', 'msg_type' => 'error']);
                }
        }
    }
        else
        {
            return Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }
    /**
     * Get branch member details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getAccociateMember(Request $request)
    {
        $mId = $request->memberid;
        $member = Member::with('savingAccount', 'memberBankDetails', 'associateInvestment', 'memberIdProofs')->leftJoin('carders', 'members.current_carder_id', '=', 'carders.id')
            ->where('members.associate_no', $mId)->where('members.status', 1)
            ->where('members.associate_status', 1)
            ->where('members.is_deleted', 0)
            ->where('members.is_associate', 1)->where('members.is_block',0)
            ->select('members.id', 'members.first_name', 'members.last_name', 'members.mobile_no', 'carders.name as carders_name')
            ->get();
        $bAccount = '';
        $bIfsc = '';
        $bName = '';
        if ($member)
        {
            foreach ($member as $key => $value)
            {
                foreach ($value['memberBankDetails'] as $key => $value)
                {
                    $bAccount = $value->account_no;
                    $bIfsc = $value->ifsc_code;
                    $bName = $value->bank_name;
                }
            }
        }
        $resCount = count($member);
        $return_array = compact('member', 'resCount', 'bAccount', 'bIfsc', 'bName');
        return json_encode($return_array);
    }
    /**
     * Get group member details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getGroupMember(Request $request)
    {
        if ($request->memberid)
        {
            $data = Member::with('savingAccount', 'memberBankDetails', 'memberIdProofs')->where('member_id', $request->memberid)
                ->where('status', 1)
                ->where('is_deleted', 0)->where('is_block',0)
                ->first();
                $dateSys=date("Y-m-d",strtotime(convertDate($request['re_member_dob'])));
                //$dateSys=date("Y-m-d");                
                if(isset($data->id))
                {
                    $balanceTotal=getAllDeposit($data->id,$dateSys);  
                    $totalDeposit=$balanceTotal; 
                }
                $existRecord = MemberLoans::where('applicant_id', $data->id)->where('loan_type','!=',4)->whereIn('status', [0, 1, 2, 4])->exists();
                if(!$existRecord )
                {
                    $existRecord = GroupLoans::where('member_id', $data->id)->whereIn('status', [0, 1, 2, 4])->exists();
                }
            if($existRecord )
            {
                return Response::json(['msg' => 'Loan is Running on this member Id', 'msg_type' => 'warning']);
            }
            else{
                if ($data)
            {
                $gaurantorCount = Loansguarantordetails::where('member_id', $data->id)
                    ->count();
                if ($gaurantorCount > 0)
                {
                    return Response::json(['view' => 'No data found', 'msg_type' => 'warning']);
                }
                else
                {
                    $id = $data->id;
                    $idProofDetail = \App\Models\MemberIdProof::where('member_id', $id)->first();
                    return Response::json(['view' => view('templates.branch.loan_management.partials.member_detail', ['memberData' => $data, 'idProofDetail' => $idProofDetail])->render() , 'msg_type' => 'success', 'member' => $data,'totalDeposit' => $totalDeposit]);
                }
            }
            else
            {
                return Response::json(['view' => 'No data found', 'msg_type' => 'error']);
            }
            }
        }
        else
        {
            return Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }
    /**
     * Get group member details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getPlanName(Request $request)
    {
        $planId = $request->planid;
        $data = Plans::select('name')->Where('id', $planId)->first();
        $planName = $data['name'];
        $return_array = compact('planName');
        return json_encode($return_array);
    }
    /**
     * Store a newly registered loan in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function Store(Request $request)
    {
       
        //echo $request['co_applicant_checkbox']; die;
        $loantype = $request->input('loan_type');
        /*switch ($loantype) {
          case "personal-loan":
            $rules = [
                'loan' => 'required',
                'amount' => 'required',
                'days' => 'required',
                'months' => 'required',
                'acc_member_id' => 'required',
                //'applicant_id' => 'required',
                'applicant_member_id' => 'required',
                'applicant_address_permanent' => 'required',
                'applicant_address_temporary' => 'required',
                'applicant_occupation' => 'required',
                'applicant_organization' => 'required',
                'applicant_designation' => 'required',
                'applicant_monthly_income' => 'required|integer',
                'applicant_year_from' => 'required|integer',
                'applicant_bank_name' => 'required',
                'applicant_bank_account_number' => 'required|integer',
                'applicant_ifsc_code' => 'required|integer',
                'applicant_cheque_number_1' => 'required|integer',
                'applicant_cheque_number_2' => 'required|integer',
                'applicant_id_proof' => 'required',
                'applicant_id_number' => 'required',
                'applicant_id_file' => 'required',
                'applicant_address_id_proof' => 'required',
                'applicant_address_id_number' => 'required',
                'applicant_address_id_file' => 'required',
                'applicant_income' => 'required',
                'applicant_income_file' => 'required',
                'applicant_security' => 'required',
                'co-applicant_member_id' => 'required',
                'co-applicant_address_permanent' => 'required',
                'co-applicant_address_temporary' => 'required',
                'co-applicant_occupation' => 'required',
                'co-applicant_organization' => 'required',
                'co-applicant_designation' => 'required',
                'co-applicant_monthly_income' => 'required|integer',
                'co-applicant_year_from' => 'required|integer',
                'co-applicant_bank_name' => 'required',
                'co-applicant_bank_account_number' => 'required|integer',
                'co-applicant_ifsc_code' => 'required|integer',
                'co-applicant_cheque_number_1' => 'required|integer',
                'co-applicant_cheque_number_2' => 'required|integer',
                'co-applicant_id_proof' => 'required',
                'co-applicant_id_number' => 'required',
                'co-applicant_id_file' => 'required',
                'co-applicant_address_id_proof' => 'required',
                'co-applicant_address_id_number' => 'required',
                'co-applicant_address_id_file' => 'required',
                'co-applicant_income' => 'required',
                'co-applicant_income_file' => 'required',
                'co-applicant_security' => 'required',
                'guarantor_member_id' => 'required',
                'guarantor_name' => 'required',
                'guarantor_father_name' => 'required',
                'guarantor_dob' => 'required',
                'guarantor_marital_status' => 'required',
                'local_address' => 'required',
                'guarantor_ownership' => 'required',
                'guarantor_temporary_address' => 'required',
                'guarantor_mobile_number' => 'required|integer',
                'guarantor_educational_qualification' => 'required',
                'guarantor_dependents_number' => 'required',
                'guarantor_occupation' => 'required',
                'guarantor_organization' => 'required',
                'guarantor_designation' => 'required',
                'guarantor_monthly_income' => 'required|integer',
                'guarantor_year_from' => 'required|integer',
                'guarantor_bank_name' => 'required',
                'guarantor_bank_account_number' => 'required|integer',
                'guarantor_ifsc_code' => 'required|integer',
                'guarantor_cheque_number_1' => 'required|integer',
                'guarantor_cheque_number_2' => 'required|integer',
                'guarantor_id_proof' => 'required',
                'guarantor_id_number' => 'required',
                'guarantor_id_file' => 'required',
                'guarantor_address_id_proof' => 'required',
                'guarantor_address_id_number' => 'required',
                'guarantor_address_id_file' => 'required',
                'guarantor_income' => 'required',
                'guarantor_income_file' => 'required',
                'guarantor_security' => 'required',
            ];
            break;
          case "kanyadan-yojana":
            break;
        }
        $customMessages = [
            'acc_member_id.required' => 'The accociate member id field is required.',
            'required' => 'The :attribute field is required.'
        ];
        $this->validate($request, $rules, $customMessages);*/
        $type = 'create';
        $BranchId = branchName()->id;
        $stateid = getBranchState(Auth::user()->username);
        $data = $this->getData($request->all() , $type);
        //echo "<pre>"; print_r($request->all()); die;
        DB::beginTransaction();
        try
        {
            switch ($loantype)
            {
                case "personal-loan":
                    $res = Memberloans::create($data);
                    $insertedid = $res->id;
                    $applicantIdFile = $request->file('applicant_id_file');
                    $applicantAddressFile = $request->file('applicant_address_id_file');
                    $applicantIncomeFile = $request->file('applicant_income_file');
                    //echo "<pre>"; print_r($request->all()); die;
                    $applicant = $this->applicantDetailData($request->all() , $applicantIdFile, $applicantAddressFile, $applicantIncomeFile, $insertedid, $type);
                    $res = Loanapplicantdetails::create($applicant);
                    $coapplicantIdFile = $request->file('co-applicant_id_file');
                    $coapplicantAddressFile = $request->file('co-applicant_address_id_file');
                    $coapplicantIncomeFile = $request->file('co-applicant_income_file');
                    $coapplicantUnderTakingDoc = $request->file('co-applicant_under_taking_doc');
                    if ($request['co_applicant_checkbox'] != '')
                    {
                        $coApplicant = $this->coApplicantDetailData($request->all() , $coapplicantIdFile, $coapplicantAddressFile, $coapplicantIncomeFile, $coapplicantUnderTakingDoc, $insertedid, $type);
                        $res = Loanscoapplicantdetails::create($coApplicant);
                    }
                    $guarantorIdFile = $request->file('guarantor_id_file');
                    $guarantorAddressFile = $request->file('guarantor_address_id_file');
                    $guarantorIncomeFile = $request->file('guarantor_income_file');
                    $guarantorOtherFile = $request->file('guarantor_more_upload_file');
                    //$guarantorUnderTakingDoc = $request->file('guarantor_under_taking_doc');
                    $guarantor = $this->guarantorDetailData($request->all() , $guarantorIdFile, $guarantorAddressFile, $guarantorIncomeFile, $guarantorOtherFile, $insertedid, $type);
                    $res = Loansguarantordetails::create($guarantor);
                    if ($request['hidden_more_doc'] == 1)
                    {
                        $guarantorMoreDoc = $this->uploadGuarantorMoreDoc($request['guarantor_more_doc_title'], $request->file('guarantor_more_upload_file') , $insertedid, 'guarantor', 'moredocument');
                    }
                break;
                case "group-loan":
                    $branchCode = getBranchCode($BranchId);
                    $groupLoanCommonId = groupLoanCommonId($branchCode->branch_code);
                    $groupData['loan_type'] = $request['loan'];
                    $groupData['branch_id'] = $BranchId;
                    $groupData['group_loan_common_id'] = $groupLoanCommonId;
                    $groupData['associate_member_id'] = $request['group_associate_id'];
                    $groupData['applicant_id'] = $request['group_member_id'];
                    $groupData['amount'] = $request['amount'];
                    $groupData['emi_option'] = $request['emi_option'];
                    $groupData['emi_period'] = $request['emi_mode_option'];
                    $groupData['file_charges'] = $request['file_charge'];
                    $groupData['created_at'] = checkMonthAvailability(date('d') , date('m') , date('Y') , $stateid);
                    $res = Memberloans::create($groupData);
                    $insertedid = $res->id;
                    $gdata = $this->getGroupLoanData($request->all() , $type, $insertedid, $groupLoanCommonId);
                    /*$groupLoanMembers = $this->storeGroupLoanMembers($request->all(),$insertedid);*/
                    $applicantIdFile = $request->file('applicant_id_file');
                    $applicantAddressFile = $request->file('applicant_address_id_file');
                    $applicantIncomeFile = $request->file('applicant_income_file');
                    $applicant = $this->applicantDetailData($request->all() , $applicantIdFile, $applicantAddressFile, $applicantIncomeFile, $insertedid, $type);
                    $res = Loanapplicantdetails::create($applicant);
                    $coapplicantIdFile = $request->file('co-applicant_id_file');
                    $coapplicantAddressFile = $request->file('co-applicant_address_id_file');
                    $coapplicantIncomeFile = $request->file('co-applicant_income_file');
                    $coapplicantUnderTakingDoc = $request->file('co-applicant_under_taking_doc');
                    if ($request['co_applicant_checkbox'] != '')
                    {
                        $coApplicant = $this->coApplicantDetailData($request->all() , $coapplicantIdFile, $coapplicantAddressFile, $coapplicantIncomeFile, $coapplicantUnderTakingDoc, $insertedid, $type);
                        $res = Loanscoapplicantdetails::create($coApplicant);
                    }
                    $guarantorIdFile = $request->file('guarantor_id_file');
                    $guarantorAddressFile = $request->file('guarantor_address_id_file');
                    $guarantorIncomeFile = $request->file('guarantor_income_file');
                    $guarantorUnderTakingDoc = $request->file('guarantor_under_taking_doc');
                    //$guarantorOtherFile = $request->file('guarantor_more_upload_file');
                    $guarantor = $this->guarantorDetailData($request->all() , $guarantorIdFile, $guarantorAddressFile, $guarantorIncomeFile, $guarantorUnderTakingDoc, $insertedid, $type);
                    $res = Loansguarantordetails::create($guarantor);
                    if ($request['hidden_more_doc'] == 1)
                    {
                        $guarantorMoreDoc = $this->uploadGuarantorMoreDoc($request['guarantor_more_doc_title'], $request->file('guarantor_more_upload_file') , $insertedid, 'guarantor', 'moredocument');
                    }
                break;
                case "staff-loan":
                    $res = Memberloans::create($data);
                    $insertedid = $res->id;
                    $applicantIdFile = $request->file('applicant_id_file');
                    $applicantAddressFile = $request->file('applicant_address_id_file');
                    $applicantIncomeFile = $request->file('applicant_income_file');
                    $applicant = $this->applicantDetailData($request->all() , $applicantIdFile, $applicantAddressFile, $applicantIncomeFile, $insertedid, $type);
                    $res = Loanapplicantdetails::create($applicant);
                    $guarantorIdFile = $request->file('guarantor_id_file');
                    $guarantorAddressFile = $request->file('guarantor_address_id_file');
                    $guarantorIncomeFile = $request->file('guarantor_income_file');
                    $guarantorUnderTakingDoc = $request->file('guarantor_under_taking_doc');
                    //$guarantorOtherFile = $request->file('guarantor_more_upload_file');
                    $guarantor = $this->guarantorDetailData($request->all() , $guarantorIdFile, $guarantorAddressFile, $guarantorIncomeFile, $guarantorUnderTakingDoc, $insertedid, $type);
                    $res = Loansguarantordetails::create($guarantor);
                    if ($request['hidden_more_doc'] == 1)
                    {
                        $guarantorMoreDoc = $this->uploadGuarantorMoreDoc($request['guarantor_more_doc_title'], $request->file('guarantor_more_upload_file') , $insertedid, 'guarantor', 'moredocument');
                    }
                break;
                case "loan-against-investment-plan":
                    $res = Memberloans::create($data);
                    $insertedid = $res->id;
                    $loanInvestmentPlans = $this->storeLoanInvestmentPlans($request->all() , $insertedid);
                    $applicantIdFile = $request->file('applicant_id_file');
                    $applicantAddressFile = $request->file('applicant_address_id_file');
                    $applicantIncomeFile = $request->file('applicant_income_file');
                    $applicant = $this->applicantDetailData($request->all() , $applicantIdFile, $applicantAddressFile, $applicantIncomeFile, $insertedid, $type);
                    $res = Loanapplicantdetails::create($applicant);
                break;
            }
            DB::commit();
        }
        catch(\Exception $ex)
        {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        if ($res)
        {
            if ($loantype == 'group-loan')
            {
                return redirect()->route('loan.grouploan')
                    ->with('success', 'Loan details successfully registered!');
            }
            else
            {
                return redirect()
                    ->route('loan.loans')
                    ->with('success', 'Loan details successfully registered!');
            }
        }
        else
        {
            return back()
                ->with('alert', 'Problem With Register New Plan');
        }
    }
    /**
     * Display created investment by id.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function Edit($id)
    {
        $data['title'] = "Loan Edit";
        $data['loans'] = Loans::all();
        $data['loanDetails'] = Memberloans::with('loan', 'LoanApplicants', 'LoanCoApplicants', 'LoanGuarantor', 'Loanotherdocs', 'GroupLoanMembers', 'loanInvestmentPlans')->findOrFail($id);
        if (!empty($data['loanDetails']))
        {
            $loanDetails = $data['loanDetails']->toArray();
            $loan_type = $loanDetails['loan_type'];
            if ($loan_type == 3)
            {
                if (!in_array('Group Loan Edit', auth()->user()
                    ->getPermissionNames()
                    ->toArray()))
                { //group loan
                    return redirect()
                        ->route('branch.dashboard');
                }
            }
            else
            {
                if (!in_array('Loan Edit', auth()
                    ->user()
                    ->getPermissionNames()
                    ->toArray()))
                {
                    return redirect()
                        ->route('branch.dashboard');
                }
            }
        }
        $data['id'] = $id;
        return view('templates.branch.loan_management.edit', $data);
    }
    /**
     * Display loan details.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function View($id)
    {
        $data['title'] = "Loan Details";
        $data['loanDetails'] = Memberloans::with('loan', 'LoanApplicants', 'LoanCoApplicants', 'LoanGuarantor', 'Loanotherdocs', 'GroupLoanMembers', 'loanInvestmentPlans')->findOrFail($id);
        /*echo '<pre>';
        print_r($data['loanDetails']->toArray());
        echo $id; exit;*/
        if (!empty($data['loanDetails']))
        {
            $loanDetails = $data['loanDetails']->toArray();
            $loan_type = $loanDetails['loan_type'];
            //echo $loan_type; exit;
            /*echo $loan_type;
            echo '<pre>';*/
            if ($loan_type == 3)
            {
                /*echo $loan_type.'3';   exit; */
                if (!in_array('Group Loan View Detail', auth()->user()
                    ->getPermissionNames()
                    ->toArray()))
                { //group loan
                    return redirect()
                        ->route('branch.dashboard');
                }
            }
            else
            {
                /*echo $loan_type.'1'; exit; */
                if (!in_array('Loan View Detail', auth()
                    ->user()
                    ->getPermissionNames()
                    ->toArray()))
                {
                    return redirect()
                        ->route('branch.dashboard');
                }
            }
        }
        return view('templates.branch.loan_management.view', $data);
    }
    /**
     * Update the specified loan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function Update(Request $request)
    {
        $loantype = $request->input('loan_type');
        /*switch ($loantype) {
          case "personal-loan":
            $rules = [
                'loan' => 'required',
                'amount' => 'required',
                'days' => 'required',
                'months' => 'required',
                'acc_member_id' => 'required',
                //'applicant_id' => 'required',
                'applicant_member_id' => 'required',
                'applicant_address_permanent' => 'required',
                'applicant_address_temporary' => 'required',
                'applicant_occupation' => 'required',
                'applicant_organization' => 'required',
                'applicant_designation' => 'required',
                'applicant_monthly_income' => 'required|integer',
                'applicant_year_from' => 'required|integer',
                'applicant_bank_name' => 'required',
                'applicant_bank_account_number' => 'required|integer',
                'applicant_ifsc_code' => 'required|integer',
                'applicant_cheque_number_1' => 'required|integer',
                'applicant_cheque_number_2' => 'required|integer',
                'applicant_id_proof' => 'required',
                'applicant_id_number' => 'required',
                'applicant_address_id_proof' => 'required',
                'applicant_address_id_number' => 'required',
                'applicant_income' => 'required',
                'applicant_security' => 'required',
                'co-applicant_member_id' => 'required',
                'co-applicant_address_permanent' => 'required',
                'co-applicant_address_temporary' => 'required',
                'co-applicant_occupation' => 'required',
                'co-applicant_organization' => 'required',
                'co-applicant_designation' => 'required',
                'co-applicant_monthly_income' => 'required|integer',
                'co-applicant_year_from' => 'required|integer',
                'co-applicant_bank_name' => 'required',
                'co-applicant_bank_account_number' => 'required|integer',
                'co-applicant_ifsc_code' => 'required|integer',
                'co-applicant_cheque_number_1' => 'required|integer',
                'co-applicant_cheque_number_2' => 'required|integer',
                'co-applicant_id_proof' => 'required',
                'co-applicant_id_number' => 'required',
                'co-applicant_address_id_proof' => 'required',
                'co-applicant_address_id_number' => 'required',
                'co-applicant_income' => 'required',
                'co-applicant_security' => 'required',
                'guarantor_member_id' => 'required',
                'guarantor_name' => 'required',
                'guarantor_father_name' => 'required',
                'guarantor_dob' => 'required',
                'guarantor_marital_status' => 'required',
                'local_address' => 'required',
                'guarantor_ownership' => 'required',
                'guarantor_temporary_address' => 'required',
                'guarantor_mobile_number' => 'required|integer',
                'guarantor_educational_qualification' => 'required',
                'guarantor_dependents_number' => 'required',
                'guarantor_occupation' => 'required',
                'guarantor_organization' => 'required',
                'guarantor_designation' => 'required',
                'guarantor_monthly_income' => 'required|integer',
                'guarantor_year_from' => 'required|integer',
                'guarantor_bank_name' => 'required',
                'guarantor_bank_account_number' => 'required|integer',
                'guarantor_ifsc_code' => 'required|integer',
                'guarantor_cheque_number_1' => 'required|integer',
                'guarantor_cheque_number_2' => 'required|integer',
                'guarantor_id_proof' => 'required',
                'guarantor_id_number' => 'required',
                'guarantor_address_id_proof' => 'required',
                'guarantor_address_id_number' => 'required',
                'guarantor_income' => 'required',
                'guarantor_security' => 'required',
            ];
            break;
        }
        $customMessages = [
            'acc_member_id.required' => 'The accociate member id field is required.',
            'required' => 'The :attribute field is required.'
        ];
        $this->validate($request, $rules, $customMessages);*/
        $type = 'update';
        $loanId = $request->input('loanId');
        $data = $this->getData($request->all() , $type);
        $memberLoan = Memberloans::find($loanId);
        $data['edit_reject_request'] = $request->input('edit_reject_request');
        $memberLoan->update($data);
        DB::beginTransaction();
        try
        {
            switch ($loantype)
            {
                case "1":
                    $applicantIdFile = $request->file('applicant_id_file');
                    $applicantAddressFile = $request->file('applicant_address_id_file');
                    $applicantIncomeFile = $request->file('applicant_income_file');
                    $applicant = $this->applicantDetailData($request->all() , $applicantIdFile, $applicantAddressFile, $applicantIncomeFile, $loanId, $type);
                    $applicantId = $request->input('applicant_id');
                    $applicanRes = Loanapplicantdetails::find($applicantId);
                    $applicanRes->update($applicant);
                    $coapplicantIdFile = $request->file('co-applicant_id_file');
                    $coapplicantAddressFile = $request->file('co-applicant_address_id_file');
                    $coapplicantIncomeFile = $request->file('co-applicant_income_file');
                    if ($request['co_applicant_checkbox'] != '')
                    {
                        $coApplicant = $this->coApplicantDetailData($request->all() , $coapplicantIdFile, $coapplicantAddressFile, $coapplicantIncomeFile, $loanId, $type);
                        $coapplicantId = $request->input('coapplicant_id');
                        $coapplicanRes = Loanscoapplicantdetails::find($coapplicantId);
                        $coapplicanRes->update($coApplicant);
                    }
                    $guarantorIdFile = $request->file('guarantor_id_file');
                    $guarantorAddressFile = $request->file('guarantor_address_id_file');
                    $guarantorIncomeFile = $request->file('guarantor_income_file');
                    $guarantorOtherFile = $request->file('guarantor_more_upload_file');
                    //$guarantorUnderTakingDoc = $request->file('guarantor_under_taking_doc');
                    $guarantor = $this->guarantorDetailData($request->all() , $guarantorIdFile, $guarantorAddressFile, $guarantorIncomeFile, $guarantorOtherFile, $loanId, $type);
                    $guarantorId = $request->input('guarantor_id');
                    $guarantorRes = Loansguarantordetails::find($guarantorId);
                    $guarantorRes->update($guarantor);
                    if ($request['hidden_more_doc'] == 1 && !empty($request->file('guarantor_more_upload_file')))
                    {
                        $guarantorMoreDoc = $this->updateGuarantorMoreDoc($request->all() , $request['guarantor_more_doc_title'], $request->file('guarantor_more_upload_file') , $loanId, 'guarantor', 'moredocument', $type);
                    }
                break;
                case "2":
                    $applicantIdFile = $request->file('applicant_id_file');
                    $applicantAddressFile = $request->file('applicant_address_id_file');
                    $applicantIncomeFile = $request->file('applicant_income_file');
                    $applicant = $this->applicantDetailData($request->all() , $applicantIdFile, $applicantAddressFile, $applicantIncomeFile, $loanId, $type);
                    $applicantId = $request->input('applicant_id');
                    $applicanRes = Loanapplicantdetails::find($applicantId);
                    $applicanRes->update($applicant);
                    $coapplicantIdFile = $request->file('co-applicant_id_file');
                    $coapplicantAddressFile = $request->file('co-applicant_address_id_file');
                    $coapplicantIncomeFile = $request->file('co-applicant_income_file');
                    if ($request['co_applicant_checkbox'] != '')
                    {
                        $coApplicant = $this->coApplicantDetailData($request->all() , $coapplicantIdFile, $coapplicantAddressFile, $coapplicantIncomeFile, $loanId, $type);
                        $coapplicantId = $request->input('coapplicant_id');
                        $coapplicanRes = Loanscoapplicantdetails::find($coapplicantId);
                        $coapplicanRes->update($coApplicant);
                    }
                    $guarantorIdFile = $request->file('guarantor_id_file');
                    $guarantorAddressFile = $request->file('guarantor_address_id_file');
                    $guarantorIncomeFile = $request->file('guarantor_income_file');
                    $guarantorOtherFile = $request->file('guarantor_more_upload_file');
                    //$guarantorUnderTakingDoc = $request->file('guarantor_under_taking_doc');
                    $guarantor = $this->guarantorDetailData($request->all() , $guarantorIdFile, $guarantorAddressFile, $guarantorIncomeFile, $guarantorOtherFile, $loanId, $type);
                    $guarantorId = $request->input('guarantor_id');
                    $guarantorRes = Loansguarantordetails::find($guarantorId);
                    $guarantorRes->update($guarantor);
                    if ($request['hidden_more_doc'] == 1 && !empty($request->file('guarantor_more_upload_file')))
                    {
                        $guarantorMoreDoc = $this->updateGuarantorMoreDoc($request->all() , $request['guarantor_more_doc_title'], $request->file('guarantor_more_upload_file') , $loanId, 'guarantor', 'moredocument', $type);
                    }
                break;
                case "3":
                    $applicantIdFile = $request->file('applicant_id_file');
                    $applicantAddressFile = $request->file('applicant_address_id_file');
                    $applicantIncomeFile = $request->file('applicant_income_file');
                    $applicant = $this->applicantDetailData($request->all() , $applicantIdFile, $applicantAddressFile, $applicantIncomeFile, $loanId, $type);
                    $applicantId = $request->input('applicant_id');
                    $applicanRes = Loanapplicantdetails::find($applicantId);
                    $applicanRes->update($applicant);
                    $guarantorIdFile = $request->file('guarantor_id_file');
                    $guarantorAddressFile = $request->file('guarantor_address_id_file');
                    $guarantorIncomeFile = $request->file('guarantor_income_file');
                    $guarantorOtherFile = $request->file('guarantor_more_upload_file');
                    //$guarantorUnderTakingDoc = $request->file('guarantor_under_taking_doc');
                    $guarantor = $this->guarantorDetailData($request->all() , $guarantorIdFile, $guarantorAddressFile, $guarantorIncomeFile, $guarantorOtherFile, $loanId, $type);
                    $guarantorId = $request->input('guarantor_id');
                    $guarantorRes = Loansguarantordetails::find($guarantorId);
                    $guarantorRes->update($guarantor);
                    if ($request['hidden_more_doc'] == 1 && !empty($request->file('guarantor_more_upload_file')))
                    {
                        $guarantorMoreDoc = $this->updateGuarantorMoreDoc($request->all() , $request['guarantor_more_doc_title'], $request->file('guarantor_more_upload_file') , $loanId, 'guarantor', 'moredocument', $type);
                    }
                break;
                case "4":
                    $applicantIdFile = $request->file('applicant_id_file');
                    $applicantAddressFile = $request->file('applicant_address_id_file');
                    $applicantIncomeFile = $request->file('applicant_income_file');
                    $guarantorOtherFile = $request->file('guarantor_more_upload_file');
                    $applicant = $this->applicantDetailData($request->all() , $applicantIdFile, $applicantAddressFile, $applicantIncomeFile, $loanId, $type);
                    $applicantId = $request->input('applicant_id');
                    $applicanRes = Loanapplicantdetails::find($applicantId);
                    $applicanRes->update($applicant);
                break;
            }
            DB::commit();
        }
        catch(\Exception $ex)
        {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return back()
            ->with('success', 'Loan details successfully updated!');
        /*if ($applicanRes) {
            return back()->with('success', 'Update was Successful!');
        } else {
            return back()->with('alert', 'An error occured');
        }*/
    }
    /**
     * upload more documents to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateGuarantorMoreDoc($request, $moredoctitles, $moredocfiles, $loanId, $folder, $moredocfolder, $type)
    {
        $BranchId = branchName()->id;
        $stateid = getBranchState(Auth::user()->username);
        //$created_at = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
        $created_at = date("Y-m-d", strtotime(convertDate($request['created_date'])));
        $mainFolder = storage_path() . '/images/loan/document/' . $loanId;
        File::makeDirectory($mainFolder, $mode = 0777, true, true);
        $loanTypeFolder = $mainFolder . '/' . $folder;
        File::makeDirectory($loanTypeFolder, $mode = 0777, true, true);
        $loanTypeProffFolder = $loanTypeFolder . '/' . $moredocfolder . '/';
        //File::makeDirectory($loanTypeProffFolder, $mode = 0777, true, true);
        foreach ($moredoctitles as $key => $value)
        {
            if (array_key_exists($key, $moredocfiles) && $request['hidden_other_doc_file_id'][$key])
            {
                $file = $moredocfiles[$key];
                $uploadFile = $file->getClientOriginalName();
                $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                $fname = $filename . '_' . time() . $key . '.' . $file->getClientOriginalExtension();
                $file->move($loanTypeProffFolder, $fname);
                $data = [
                'file_name' => $fname,
                'file_path' => $loanTypeProffFolder,
                'file_extension' => $file->getClientOriginalExtension() ,
                'created_at' => $created_at,
                ];
                $fileRes = Files::find($request['hidden_other_doc_file_id'][$key]);
                $fileRes->update($data);
                $filesId = $request['hidden_other_doc_file_id'][$key];
            }
            elseif (array_key_exists($key, $moredocfiles) && $request['hidden_other_doc_file_id'][$key] == '')
            {
                $file = $moredocfiles[$key];
                $uploadFile = $file->getClientOriginalName();
                $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                $fname = $filename . '_' . time() . $key . '.' . $file->getClientOriginalExtension();
                $file->move($loanTypeProffFolder, $fname);
                $data = [
                'file_name' => $fname,
                'file_path' => $loanTypeProffFolder,
                'file_extension' => $file->getClientOriginalExtension() ,
                'created_at' => $created_at,
                ];
                $res = Files::create($data);
                $fId = $res->id;
                $loanOtherData = [
                'member_loan_id' => $loanId,
                'title' => $value,
                'file_id' => $fId,
                'created_at' => $created_at,
                ];
                $res = Loanotherdocs::create($loanOtherData);
            }
        }
    }
    /**
     * Get investment plans data to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getData($request, $type)
    {
        $BranchId = branchName();
        $stateid = getBranchState(Auth::user()->username);
        $created_at = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
        $created_at = date("Y-m-d", strtotime(convertDate($created_at)));
        $loantype = $request['loan_type'];
        switch ($loantype)
        {
            case "personal-loan":
            break;
            case "staff-loan":
                /*$data['emi_mode_in_month'] = $request['staff_emi_mode'];
                $data['group_activity'] = $request['group_activity'];
                $data['group_member_id'] = $request['group_leader_member_id'];
                $data['number_of_member'] = $request['number_of_member'];*/
            break;
            case "group-loan":
                $data['group_activity'] = $request['group_activity'];
                $data['groupleader_member_id'] = $request['group_leader_m_id'];
                $data['group_associate_id'] = $request['group_associate_id'];
                $data['number_of_member'] = $request['number_of_member'];
                $data['group_member_id'] = $request['group_member_id'];
            break;
        }
        if ($type == 'create')
        {
            $data['loan_type'] = $request['loan'];
            $data['branch_id'] = $BranchId->id;
            $data['old_branch_id'] = $BranchId->id;
            $data['associate_member_id'] = $request['acc_member_id'];
            $data['applicant_id'] = $request['applicant_member_id'];
            $data['amount'] = $request['loan_amount'];
            //$data['emi_mode'] = $request['emi_mode'];
            $data['ROI'] = $request['interest_rate'];
            $data['emi_option'] = $request['emi_option'];
            $data['emi_period'] = $request['emi_period'];
            $data['emi_amount'] = $request['loan_emi'];
            $data['file_charges'] = $request['file_charge'];
            $data['loan_purpose'] = $request['loan_purpose'];
            $data['bank_account'] = $request['bank_account'];
            $data['ifsc_code'] = $request['ifsc_code'];
            $data['bank_name'] = $request['bank_name'];
            if ($request['emi_option'] == 1)
            {
                $data['closing_date'] = date('Y-m-d', strtotime("+" . $request['emi_period'] . " months", strtotime($created_at)));
            }
            elseif ($request['emi_option'] == 2)
            {
                $days = $request['emi_period'] * 7;
                $start_date = $created_at;
                $date = strtotime($start_date);
                $date = strtotime("+" . $days . " day", $date);
                $data['closing_date'] = date('Y-m-d', $date);
            }
            elseif ($request['emi_option'] == 3)
            {
                $days = $request['emi_period'];
                $start_date = $created_at;
                $date = strtotime($start_date);
                $date = strtotime("+" . $days . " day", $date);
                $data['closing_date'] = date('Y-m-d', $date);
            }
            //$data['created_at'] = $created_at;
            $data['created_at'] = date("Y-m-d", strtotime(convertDate($request['created_date'])));
            return $data;
        }
        elseif ($type == 'update')
        {
        }
    }
    /**
     * Get investment plans data to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getGroupLoanData($request, $type, $memberLoanId, $groupLoanCommonId)
    {
        $BranchId = branchName()->id;
        $stateid = getBranchState(Auth::user()->username);
        //$created_at = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
        $created_at = date("Y-m-d", strtotime(convertDate($request['created_date'])));
        foreach ($request['m_id'] as $key => $value)
        {
            if ($request['m_amount'][$key])
            {
                $data['member_loan_id'] = $memberLoanId;
                $data['group_loan_common_id'] = $groupLoanCommonId;
                $data['group_activity'] = $request['group_activity'];
                $data['groupleader_member_id'] = $request['group_leader_m_id'];
                $data['group_associate_id'] = $request['group_associate_id'];
                $data['number_of_member'] = $request['number_of_member'];
                $data['group_member_id'] = $request['group_member_id'];
                $data['branch_id'] = $BranchId;
                $data['old_branch_id'] = $BranchId;
                $data['associate_member_id'] = $request['group_associate_id'];
                $data['applicant_id'] = $request['group_leader_m_id'];
                $data['member_id'] = $request['m_id'][$key];
                $data['amount'] = $request['m_amount'][$key];
                $data['ROI'] = $request['ml_interest_rate'][$key];
                $data['emi_option'] = $request['emi_option'];
                $data['emi_period'] = $request['emi_mode_option'];
                $data['emi_amount'] = $request['ml_emi'][$key];
                $data['file_charges'] = $request['ml_file_charge'][$key];
                if ($request['emi_option'] == 1)
                {
                    $data['closing_date'] = date('Y-m-d', strtotime("+" . $request['emi_period'] . " months", strtotime($created_at)));
                }
                elseif ($request['emi_option'] == 2)
                {
                    $days = $request['emi_period'] * 7;
                    $start_date = $created_at;
                    $date = strtotime($start_date);
                    $date = strtotime("+" . $days . " day", $date);
                    $data['closing_date'] = date('Y-m-d', $date);
                }
                elseif ($request['emi_option'] == 3)
                {
                    $days = $request['emi_period'];
                    $start_date = $created_at;
                    $date = strtotime($start_date);
                    $date = strtotime("+" . $days . " day", $date);
                    $data['closing_date'] = date('Y-m-d', $date);
                }
                $data['created_at'] = $created_at;
                $res = Grouploans::create($data);
            }
        }
        return $res;
    }
    /**
     * Get loan applicant data to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function applicantDetailData($request, $idFile, $addressFile, $incomeFile, $loanId, $type)
    {
        $stateid = getBranchState(Auth::user()->username);
        $folder = 'applicant';
        if ($type == 'create')
        {
            $data['member_loan_id'] = $loanId;
            $data['member_id'] = $request['group_member_id'];
        }
        $data['address_permanent'] = $request['applicant_address_permanent'];
        $data['temporary_permanent'] = $request['applicant_address_temporary'];
        $data['occupation'] = $request['applicant_occupation'];
        $data['organization'] = $request['applicant_organization'];
        $data['designation'] = $request['applicant_designation'];
        $data['monthly_income'] = $request['applicant_monthly_income'];
        $data['year_from'] = $request['applicant_year_from'];
        $data['bank_name'] = $request['applicant_bank_name'];
        $data['bank_account_number'] = $request['applicant_bank_account_number'];
        $data['ifsc_code'] = $request['applicant_ifsc_code'];
        $data['cheque_number_1'] = $request['applicant_cheque_number_1'];
        $data['cheque_number_2'] = $request['applicant_cheque_number_2'];
        if ($type == 'update' && $idFile)
        {
            $hiddenFileId = $request['hidden_applicant_file_id'];
            $fileId = $this->updateUploadStoreImage($idFile, $loanId, $folder, 'id_proof', $hiddenFileId);
        }
        elseif ($type == 'update' && $idFile == '')
        {
            $fileId = $request['hidden_applicant_file_id'];
        }
        elseif ($type == 'create' && $idFile)
        {
            $fileId = $this->uploadStoreImage($idFile, $loanId, $folder, 'id_proof');
        }
        if (empty($fileId))
        {
            $fileId = null;
        }
        $data['id_proof_type'] = $request['applicant_id_proof'];
        $data['id_proof_number'] = $request['applicant_id_number'];
        $data['id_proof_file_id'] = $fileId;
        if ($type == 'update' && $addressFile)
        {
            $hiddenFileId = $request['hidden_applicant_address_file_id'];
            $addressFileId = $this->updateUploadStoreImage($addressFile, $loanId, $folder, 'address_proof', $hiddenFileId);
        }
        elseif ($type == 'update' && $addressFile == '')
        {
            $addressFileId = $request['hidden_applicant_address_file_id'];
        }
        elseif ($type == 'create' && $addressFile)
        {
            $addressFileId = $this->uploadStoreImage($addressFile, $loanId, $folder, 'address_proof', $type);
        }
        if (empty($addressFileId))
        {
            $addressFileId = null;
        }
        $data['address_proof_type'] = $request['applicant_address_id_proof'];
        $data['address_proof_id_number'] = $request['applicant_address_id_number'];
        $data['address_proof_file_id'] = $addressFileId;
        if ($type == 'update' && $incomeFile)
        {
            $hiddenFileId = $request['hidden_applicant_income_file_id'];
            $incomeFileId = $this->updateUploadStoreImage($incomeFile, $loanId, $folder, 'income_proof', $hiddenFileId);
        }
        elseif ($type == 'update' && $incomeFile == '')
        {
            $incomeFileId = $request['hidden_applicant_income_file_id'];
        }
        elseif ($type == 'create' && $incomeFile)
        {
            $incomeFileId = $this->uploadStoreImage($incomeFile, $loanId, $folder, 'income_proof');
        }
        if (empty($incomeFileId))
        {
            $incomeFileId = null;
        }
        $data['income_type'] = $request['applicant_income'];
        if ($data['income_type'] == 2)
        {
            $data['income_remark'] = $request['applicant_remark'];
        }
        $data['income_file_id'] = $incomeFileId;
        $data['security'] = $request['applicant_security'];
        //$data['created_at'] = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
        $data['created_at'] = date("Y-m-d", strtotime(convertDate($request['created_date'])));
        return $data;
    }
    /**
     * Get loan co-applicant data to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function coApplicantDetailData($request, $idFile, $addressFile, $incomeFile, $undertakingFile, $loanId, $type)
    {
        $stateid = getBranchState(Auth::user()->username);
        $folder = 'coapplicant';
        if ($type == 'create')
        {
            $data['member_loan_id'] = $loanId;
            $data['member_id'] = $request['co-applicant_member_id'];
        }
        $data['address_permanent'] = $request['co-applicant_address_permanent'];
        $data['temporary_permanent'] = $request['co-applicant_address_temporary'];
        $data['occupation'] = $request['co-applicant_occupation'];
        $data['organization'] = $request['co-applicant_organization'];
        $data['designation'] = $request['co-applicant_designation'];
        $data['monthly_income'] = $request['co-applicant_monthly_income'];
        $data['year_from'] = $request['co-applicant_year_from'];
        $data['bank_name'] = $request['co-applicant_bank_name'];
        $data['bank_account_number'] = $request['co-applicant_bank_account_number'];
        $data['ifsc_code'] = $request['co-applicant_ifsc_code'];
        $data['cheque_number_1'] = $request['co-applicant_cheque_number_1'];
        $data['cheque_number_2'] = $request['co-applicant_cheque_number_2'];
        if ($type == 'update' && $idFile)
        {
            $hiddenFileId = $request['hidden_coapplicant_file_id'];
            $fileId = $this->updateUploadStoreImage($idFile, $loanId, $folder, 'id_proof', $hiddenFileId);
        }
        elseif ($type == 'update' && $idFile == '')
        {
            $fileId = $request['hidden_coapplicant_file_id'];
        }
        elseif ($type == 'create' && $idFile)
        {
            $fileId = $this->uploadStoreImage($idFile, $loanId, $folder, 'id_proof');
        }
        if (empty($fileId))
        {
            $fileId = null;
        }
        $data['id_proof_type'] = $request['co-applicant_id_proof'];
        $data['id_proof_number'] = $request['co-applicant_id_number'];
        $data['id_proof_file_id'] = $fileId;
        if ($type == 'update' && $addressFile)
        {
            $hiddenFileId = $request['hidden_coapplicant_address_file_id'];
            $addressFileId = $this->updateUploadStoreImage($addressFile, $loanId, $folder, 'address_proof', $hiddenFileId);
        }
        elseif ($type == 'update' && $addressFile == '')
        {
            $addressFileId = $request['hidden_coapplicant_address_file_id'];
        }
        elseif ($type == 'create' && $addressFile)
        {
            $addressFileId = $this->uploadStoreImage($addressFile, $loanId, $folder, 'address_proof');
        }
        if (empty($addressFileId))
        {
            $addressFileId = null;
        }
        $data['address_proof_type'] = $request['co-applicant_address_id_proof'];
        $data['address_proof_id_number'] = $request['co-applicant_address_id_number'];
        $data['address_proof_file_id'] = $addressFileId;
        if ($type == 'update' && $incomeFile)
        {
            $hiddenFileId = $request['hidden_coapplicant_income_file_id'];
            $incomeFileId = $this->updateUploadStoreImage($incomeFile, $loanId, $folder, 'income_proof', $hiddenFileId);
        }
        elseif ($type == 'update' && $incomeFile == '')
        {
            $incomeFileId = $request['hidden_coapplicant_income_file_id'];
        }
        elseif ($type == 'create' && $incomeFile)
        {
            $incomeFileId = $this->uploadStoreImage($incomeFile, $loanId, $folder, 'income_proof');
        }
        if (empty($incomeFileId))
        {
            $incomeFileId = null;
        }
        $data['income_type'] = $request['co-applicant_income'];
        if ($data['income_type'] == 2)
        {
            $data['income_remark'] = $request['co_applicant_remark'];
        }
        $data['income_file_id'] = $incomeFileId;
        $data['security'] = $request['co-applicant_security'];
        /*$moreFileId = $this->uploadStoreImage($incomeFile,$loanId,$folder,'other');
        $data['more_doc_title'] = $request['fn_mobile_number'];
        $data['more_doc_file_id'] = $moreFileId;*/
        if ($type == 'update' && $undertakingFile)
        {
            $hiddenFileId = $request['hidden_guarantor_income_file_id'];
            $undertakingFileId = $this->updateUploadStoreImage($undertakingFile, $loanId, $folder, 'undertakingdoc', $hiddenFileId);
        }
        elseif ($type == 'update' && $undertakingFile == '')
        {
            $undertakingFileId = $request['hidden_guarantor_income_file_id'];
        }
        elseif ($type == 'create' && $undertakingFile)
        {
            $undertakingFileId = $this->uploadStoreImage($undertakingFile, $loanId, $folder, 'undertakingdoc');
        }
        if (empty($undertakingFileId))
        {
            $undertakingFileId = null;
        }
        $data['under_taking_doc'] = $undertakingFileId;
        //$data['created_at'] = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
        $data['created_at'] = date("Y-m-d", strtotime(convertDate($request['created_date'])));
        return $data;
    }
    /**
     * Get loan guarantor data to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function guarantorDetailData($request, $idFile, $addressFile, $incomeFile, $guarantorOtherFile, $loanId, $type)
    {
        $folder = 'guarantor';
        $stateid = getBranchState(Auth::user()->username);
        if ($type == 'create')
        {
            $data['member_loan_id'] = $loanId;
            $data['member_id'] = $request['guarantor_member_id'];
        }
        $data['name'] = $request['guarantor_name'];
        $data['father_name'] = $request['guarantor_father_name'];
        $data['dob'] = date("Y-m-d", strtotime(convertDate($request['guarantor_dob'])));
        $data['marital_status'] = $request['guarantor_marital_status'];
        $data['local_address'] = $request['local_address'];
        $data['ownership'] = $request['guarantor_ownership'];
        $data['temporary_permanent'] = $request['guarantor_temporary_address'];
        $data['mobile_number'] = $request['guarantor_mobile_number'];
        $data['educational_qualification'] = $request['guarantor_educational_qualification'];
        $data['number_of_dependents'] = $request['guarantor_dependents_number'];
        $data['occupation'] = $request['guarantor_occupation'];
        $data['organization'] = $request['guarantor_organization'];
        $data['designation'] = $request['guarantor_designation'];
        $data['monthly_income'] = $request['guarantor_monthly_income'];
        $data['year_from'] = $request['guarantor_year_from'];
        $data['bank_name'] = $request['guarantor_bank_name'];
        $data['bank_account_number'] = $request['guarantor_bank_account_number'];
        $data['ifsc_code'] = $request['guarantor_ifsc_code'];
        $data['cheque_number_1'] = $request['guarantor_cheque_number_1'];
        $data['cheque_number_2'] = $request['guarantor_cheque_number_2'];
        if ($type == 'update' && $idFile)
        {
            $hiddenFileId = $request['hidden_guarantor_file_id'];
            $fileId = $this->updateUploadStoreImage($idFile, $loanId, $folder, 'id_proof', $hiddenFileId);
        }
        elseif ($type == 'update' && $idFile == '')
        {
            $fileId = $request['hidden_guarantor_file_id'];
        }
        elseif ($type == 'create' && $idFile)
        {
            $fileId = $this->uploadStoreImage($idFile, $loanId, $folder, 'id_proof');
        }
        if (empty($fileId))
        {
            $fileId = null;
        }
        $data['id_proof_type'] = $request['guarantor_id_proof'];
        $data['id_proof_number'] = $request['guarantor_id_number'];
        $data['id_proof_file_id'] = $fileId;
        if ($type == 'update' && $addressFile)
        {
            $hiddenFileId = $request['hidden_guarantor_address_file_id'];
            $addressFileId = $this->updateUploadStoreImage($addressFile, $loanId, $folder, 'address_proof', $hiddenFileId);
        }
        elseif ($type == 'update' && $addressFile == '')
        {
            $addressFileId = $request['hidden_guarantor_address_file_id'];
        }
        elseif ($type == 'create' && $addressFile)
        {
            $addressFileId = $this->uploadStoreImage($addressFile, $loanId, $folder, 'address_proof');
        }
        if (empty($addressFileId))
        {
            $addressFileId = null;
        }
        $data['address_proof_type'] = $request['guarantor_address_id_proof'];
        $data['address_proof_id_number'] = $request['guarantor_address_id_number'];
        $data['address_proof_file_id'] = $addressFileId;
        if ($type == 'update' && $incomeFile)
        {
            $hiddenFileId = $request['hidden_guarantor_income_file_id'];
            $incomeFileId = $this->updateUploadStoreImage($incomeFile, $loanId, $folder, 'income_proof', $hiddenFileId);
        }
        elseif ($type == 'update' && $incomeFile == '')
        {
            $incomeFileId = $request['hidden_guarantor_income_file_id'];
        }
        elseif ($type == 'create' && $incomeFile)
        {
            $incomeFileId = $this->uploadStoreImage($incomeFile, $loanId, $folder, 'income_proof');
        }
        if (empty($incomeFileId))
        {
            $incomeFileId = null;
        }
        /*if($type=='update' && $undertakingFile){
            $hiddenFileId = $request['hidden_guarantor_under_taking_file_id'];
            $undertakingFileId = $this->updateUploadStoreImage($undertakingFile,$loanId,$folder,'undertakingdoc',$hiddenFileId);
        }elseif($type=='update' && $undertakingFile == ''){
            $undertakingFileId = $request['hidden_guarantor_under_taking_file_id'];
        }elseif ($type=='create' && $undertakingFile) {
            $undertakingFileId = $this->uploadStoreImage($undertakingFile,$loanId,$folder,'undertakingdoc');
        }
        if(empty($undertakingFileId)){
            $undertakingFileId = null;
        }
        $data['under_taking_doc'] = $undertakingFileId;*/
        $data['income_type'] = $request['guarantor_income'];
        if ($data['income_type'] == 2)
        {
            $data['income_remark'] = $request['guarantor_income_remark'];
        }
        $data['income_file_id'] = $incomeFileId;
        $data['security'] = $request['guarantor_security'];
        if ($type == 'update')
        {
            $data['updated_at'] = checkMonthAvailability(date('d') , date('m') , date('Y') , $stateid);
        }
        else
        {
            //$data['created_at'] = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
            $data['created_at'] = date("Y-m-d", strtotime(convertDate($request['created_date'])));
        }
        /*if($request['hidden_more_doc']==1){
            if($type=='update' && $guarantorOtherFile && $request['hidden_other_doc_file_id']){
                $hiddenFileId = $request['hidden_other_doc_file_id'];
                $moreFileId = $this->updateUploadStoreImage($guarantorOtherFile,$loanId,$folder,'other',$hiddenFileId);
            }elseif($type=='update' && $guarantorOtherFile == '' && $request['hidden_other_doc_file_id'] != ''){
                $moreFileId = $request['hidden_other_doc_file_id'];
                //$moreFileId = $this->uploadStoreImage($guarantorOtherFile,$loanId,$folder,'other');
            }elseif($type=='update' && $guarantorOtherFile && $request['hidden_other_doc_file_id'] == ''){
                $moreFileId = $this->uploadStoreImage($guarantorOtherFile,$loanId,$folder,'other');
                //$moreFileId = $this->uploadStoreImage($guarantorOtherFile,$loanId,$folder,'other');
            }elseif ($type=='create' && $guarantorOtherFile) {
                $moreFileId = $this->uploadStoreImage($guarantorOtherFile,$loanId,$folder,'other');
            }
            $data['more_doc_title'] = $request['guarantor_more_doc_title'];
            $data['more_doc_file_id'] = $moreFileId;
        }*/
        return $data;
    }
    /**
     * group loan members to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeGroupLoanMembers($request, $loanId)
    {
        $stateid = getBranchState(Auth::user()->username);
        foreach ($request['m_id'] as $key => $value)
        {
            if ($request['m_amount'][$key])
            {
                $data = [
                'member_loan_id' => $loanId,
                'member_id' => $request['m_id'][$key],
                'amount' => $request['m_amount'][$key],
                'created_at' => checkMonthAvailability(date('d') , date('m') , date('Y') , $stateid) ,
                ];
                $res = Grouploanmembers::create($data);
            }
        }
        return $res;
    }
    /**
     * invest plans for loan to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeLoanInvestmentPlans($request, $loanId)
    {
        $stateid = getBranchState(Auth::user()->username);
        foreach ($request['investmentplanloanid'] as $key => $value)
        {
            if ($request['ipl_amount'][$key])
            {
                $data = [
                'member_loan_id' => $loanId,
                'plan_id' => $request['investmentplanloanid'][$key],
                'amount' => $request['ipl_amount'][$key],
                'created_at' => checkMonthAvailability(date('d') , date('m') , date('Y') , $stateid) ,
                ];
                $res = Loaninvestmentmembers::create($data);
            }
        }
        return $res;
    }
    /**
     * upload loan proof documents to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function uploadStoreImage($file, $loanId, $folder, $prooffolder)
    {
        $stateid = getBranchState(Auth::user()->username);
        $mainFolder = storage_path() . '/images/loan/document/' . $loanId;
        File::makeDirectory($mainFolder, $mode = 0777, true, true);
        $loanTypeFolder = $mainFolder . '/' . $folder;
        File::makeDirectory($loanTypeFolder, $mode = 0777, true, true);
        $loanTypeProffFolder = $loanTypeFolder . '/' . $prooffolder . '/';
        //File::makeDirectory($loanTypeProffFolder, $mode = 0777, true, true);
        $uploadFile = $file->getClientOriginalName();
        $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
        $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($loanTypeProffFolder, $fname);
        $data = [
        'file_name' => $fname,
        'file_path' => $loanTypeProffFolder,
        'file_extension' => $file->getClientOriginalExtension() ,
        'created_at' => checkMonthAvailability(date('d') , date('m') , date('Y') , $stateid) ,
        ];
        $res = Files::create($data);
        return $res->id;
    }
    /**
     * upload more documents to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function uploadGuarantorMoreDoc($moredoctitles, $moredocfiles, $loanId, $folder, $moredocfolder)
    {
        $stateid = getBranchState(Auth::user()->username);
        $mainFolder = storage_path() . '/images/loan/document/' . $loanId;
        File::makeDirectory($mainFolder, $mode = 0777, true, true);
        $loanTypeFolder = $mainFolder . '/' . $folder;
        File::makeDirectory($loanTypeFolder, $mode = 0777, true, true);
        $loanTypeProffFolder = $loanTypeFolder . '/' . $moredocfolder . '/';
        //File::makeDirectory($loanTypeProffFolder, $mode = 0777, true, true);
        foreach ($moredoctitles as $key => $value)
        {
            $file = $moredocfiles[$key];
            $uploadFile = $file->getClientOriginalName();
            $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
            $fname = $filename . '_' . time() . $key . '.' . $file->getClientOriginalExtension();
            $file->move($loanTypeProffFolder, $fname);
            $data = [
            'file_name' => $fname,
            'file_path' => $loanTypeProffFolder,
            'file_extension' => $file->getClientOriginalExtension() ,
            'created_at' => checkMonthAvailability(date('d') , date('m') , date('Y') , $stateid) ,
            ];
            $res = Files::create($data);
            $fId = $res->id;
            $loanOtherData = [
            'member_loan_id' => $loanId,
            'title' => $value,
            'file_id' => $fId,
            'created_at' => checkMonthAvailability(date('d') , date('m') , date('Y') , $stateid) ,
            ];
            $res = Loanotherdocs::create($loanOtherData);
        }
        return $res;
    }
    /**
     * update upload loan proof documents to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateUploadStoreImage($file, $loanId, $folder, $prooffolder, $hiddenFileId)
    {
        $stateid = getBranchState(Auth::user()->username);
        $mainFolder = storage_path() . '/images/loan/document/' . $loanId;
        File::makeDirectory($mainFolder, $mode = 0777, true, true);
        $loanTypeFolder = $mainFolder . '/' . $folder;
        File::makeDirectory($loanTypeFolder, $mode = 0777, true, true);
        $loanTypeProffFolder = $loanTypeFolder . '/' . $prooffolder . '/';
        //File::makeDirectory($loanTypeProffFolder, $mode = 0777, true, true);
        $proffFolder = glob($loanTypeFolder . '/' . $prooffolder . '/*');
        foreach ($proffFolder as $fileRes)
        {
            if (is_file($fileRes))
            unlink($fileRes);
        }
        $uploadFile = $file->getClientOriginalName();
        $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
        $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($loanTypeProffFolder, $fname);
        if ($hiddenFileId == '')
        {
            $data = [
            'file_name' => $fname,
            'file_path' => $loanTypeProffFolder,
            'file_extension' => $file->getClientOriginalExtension() ,
            'created_at' => checkMonthAvailability(date('d') , date('m') , date('Y') , $stateid) ,
            ];
            $res = Files::create($data);
            $filesId = $res->id;
        }
        else
        {
            $data = [
            'file_name' => $fname,
            'file_path' => $loanTypeProffFolder,
            'file_extension' => $file->getClientOriginalExtension() ,
            'created_at' => checkMonthAvailability(date('d') , date('m') , date('Y') , $stateid) ,
            ];
            $fileRes = Files::find($hiddenFileId);
            $fileRes->update($data);
            $filesId = $hiddenFileId;
        }
        return $filesId;
    }
    /**
     * Send a loan approval request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function SendApprovalRequest($id)
    {
        if (!in_array('Group Loan Send Approval Request', auth()->user()
            ->getPermissionNames()
            ->toArray()))
        {
            return redirect()
                ->route('branch.dashboard');
        }
        $mLoan = Memberloans::find($id);
        $mLoanData['status'] = 0;
        $mLoan->update($mLoanData);
        return back()->with('success', 'Send approval request Successfully!');
    }
    /**
     * Deposite loan EMI.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    // public function depositeLoanEmi(Request $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $entryTime = date("H:i:s");
    //         Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date']))));
    //         $branchId = branchName()->id;
    //         //$branchId = $request['branch'];
    //         $loanId = $request['loan_id'];
    //         $ssbAccountDetails = SavingAccount::with('ssbMember')->where('id',$request['ssb_id'])->first();
    //         if($request['loan_emi_payment_mode'] == 0){
    //             if($ssbAccountDetails->balance < $request['deposite_amount']){
    //                 return back()->with('error', 'Insufficient balance in ssb account!');
    //             }
    //         }
    //         $mLoan = Memberloans::with('loanMember')->where('id',$request['loan_id'])->first();
    //         if($mLoan->emi_option == 1){
    //             $roi = $mLoan->due_amount*$mLoan->ROI/1200;
    //         }elseif($mLoan->emi_option == 2){
    //             $roi = $mLoan->due_amount*$mLoan->ROI/5200;
    //         }elseif($mLoan->emi_option == 3){
    //             $roi = $mLoan->due_amount*$mLoan->ROI/36500;
    //         }
    //         $principal_amount = $request['deposite_amount']-$roi;
    //         $amountArraySsb=array('1'=>$request['deposite_amount']);
    //         $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
    //         $dueAmount = $mLoan->due_amount-round($principal_amount);
    //         $mlResult = Memberloans::find($request['loan_id']);
    //         $lData['credit_amount'] = $mLoan->credit_amount+round($principal_amount);
    //         $lData['due_amount'] = $dueAmount;
    //         if($dueAmount == 0){
    //             //$lData['status'] = 3;
    //             //$lData['clear_date'] = date("Y-m-d", strtotime(convertDate($request['application_date'])));
    //         }
    //         $lData['received_emi_amount'] = $mLoan->received_emi_amount+$request['deposite_amount'];
    //         $mlResult->update($lData);
    //         if($request['loan_emi_payment_mode'] == 0){
    //             $cheque_dd_no=NULL;
    //             $online_payment_id=NULL;
    //             $online_payment_by=NULL;
    //             $bank_name=NULL;
    //             $cheque_date=NULL;
    //             $account_number=NULL;
    //             $paymentMode=4;
    //             $ssbpaymentMode=3;
    //             $paymentDate = date("Y-m-d", strtotime(convertDate($request['application_date'])));
    //             $record1=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','<',date("Y-m-d", strtotime(convertDate($request['application_date']))))->orderby('id','desc')->first();
    //             $ssb['saving_account_id']=$ssbAccountDetails->id;
    //             $ssb['account_no']=$ssbAccountDetails->account_no;
    //             $ssb['opening_balance']=$record1->opening_balance-$request['deposite_amount'];
    //             $ssb['branch_id']=$branchId;
    //             $ssb['type']=9;
    //             $ssb['deposit']=0;
    //             $ssb['withdrawal']=$request['deposite_amount'];
    //             $ssb['description']='Loan EMI Payment';
    //             $ssb['currency_code']='INR';
    //             $ssb['payment_type']='DR';
    //             $ssb['payment_mode']=$ssbpaymentMode;
    //             $ssb['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //             $ssbAccountTran = SavingAccountTranscation::create($ssb);
    //             $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;
    //             // update saving account current balance
    //             $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
    //             $ssbBalance->balance=$request['ssb_account']-$request['deposite_amount'];
    //             $ssbBalance->save();
    //             $record2=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','>',date("Y-m-d", strtotime(convertDate($request['application_date']))))->get();
    //             foreach ($record2 as $key => $value) {
    //                 $nsResult = SavingAccountTranscation::find($value->id);
    //                 $nsResult['opening_balance']=$value->opening_balance-$request['deposite_amount'];
    //                 $nsResult['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //                 $nsResult->update($nsResult);
    //             }
    //             $data['saving_account_transaction_id']=$ssb_transaction_id;
    //             $data['loan_id']=$request['loan_id'];
    //             $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //             $satRef = TransactionReferences::create($data);
    //             $satRefId = $satRef->id;
    //             $updateSsbDayBook = $this->updateSsbDayBookAmount($request['deposite_amount'],$request['ssb_account_number'],date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date']))));
    //             $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,1,$ssbAccountDetails->id,$ssbAccountDetails->member_id,$branchId,getBranchCode($branchId)->branch_code,$amountArraySsb,$paymentMode,$request['loan_associate_name'],$request['associate_member_id'],$ssbAccountDetails->account_no,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'DR');
    //             $totalbalance = $request['ssb_account']-$request['deposite_amount'];
    //             $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->member_investments_id,0,$ssbAccountDetails->member_id,$totalbalance,0,$request['deposite_amount'],'Withdrawal from SSB',$request['account_number'],$branchId,getBranchCode($branchId)->branch_code,$amountArraySsb,$paymentMode,$request['loan_associate_name'],$request['associate_member_id'],$ssbAccountDetails->account_no,$cheque_dd_no,NULL,NULL,$paymentDate,$online_payment_by,$online_payment_by,$ssbAccountDetails->id,'DR');
    //         }elseif($request['loan_emi_payment_mode'] == 1){
    //             if($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != ''){
    //                 $cheque_dd_no=$request['customer_cheque'];
    //                 $paymentMode=1;
    //                 $ssbpaymentMode=5;
    //                 $online_payment_id=NULL;
    //                 $online_payment_by=NULL;
    //                 $satRefId = NULL;
    //                 $bank_name=NULL;
    //                 $cheque_date=NULL;
    //                 $account_number=NULL;
    //                 $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //             }elseif($request['bank_transfer_mode'] == 1){
    //                 $cheque_dd_no=NULL;
    //                 $paymentMode=3;
    //                 $ssbpaymentMode=5;
    //                 $online_payment_id=$request['utr_transaction_number'];
    //                 $online_payment_by=NULL;
    //                 $satRefId = NULL;
    //                 $bank_name=NULL;
    //                 $cheque_date=NULL;
    //                 $account_number=NULL;
    //                 $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //             }
    //             $ssbAccountTran = '';
    //             $ssb_account_id_from = '';
    //         }elseif($request['loan_emi_payment_mode'] == 2){
    //             $cheque_dd_no=NULL;
    //             $paymentMode=0;
    //             $ssbpaymentMode=0;
    //             $online_payment_id=NULL;
    //             $online_payment_by=NULL;
    //             $satRefId = NULL;
    //             $bank_name=NULL;
    //             $cheque_date=NULL;
    //             $account_number=NULL;
    //             $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //             $ssbAccountTran = '';
    //             $ssb_account_id_from = '';
    //         }elseif($request['loan_emi_payment_mode'] == 3){
    //             $cheque_dd_no=$request['cheque_number'];
    //             $cheque_date=$request['cheque_date'];
    //             $bank_name=$request['bank_name'];
    //             $account_number=$request['account_number'];
    //             $paymentMode=1;
    //             $ssbpaymentMode=1;
    //             $online_payment_id=NULL;
    //             $online_payment_by=NULL;
    //             $satRefId = NULL;
    //             $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //             $ssbAccountTran = '';
    //             $ssb_account_id_from = '';
    //         }
    //         $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,5,$request['loan_id'],$mLoan->applicant_id,$branchId,getBranchCode($branchId)->branch_code,$amountArraySsb,$paymentMode,$request['loan_associate_name'],$request['associate_member_id'],$mLoan->account_number,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'CR');
    //         $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,5,$request['loan_id'],0,$mLoan->applicant_id,$dueAmount,$request['deposite_amount'],0,'Loan EMI deposit',$mLoan->account_number,$branchId,getBranchCode($branchId)->branch_code,$amountArraySsb,$paymentMode,$request['loan_associate_name'],$request['associate_member_id'],$mLoan->account_number,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_by,$online_payment_by,$ssbAccountDetails->id,'CR');
    //         if($request['loan_emi_payment_mode'] == 3){
    //             $checkData['type']=4;
    //             $checkData['branch_id']=$branchId;
    //             // $checkData['loan_id']=$request['loan_id'];
    //             $checkData['day_book_id']=$createDayBook;
    //             $checkData['cheque_id']=$cheque_dd_no;
    //             $checkData['status']=1;
    //             $checkData['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //             $ssbAccountTran = ReceivedChequePayment::create($checkData);
    //             $dataRC['status']=3;
    //             $receivedcheque = ReceivedCheque::find($cheque_dd_no);
    //             $receivedcheque->update($dataRC);
    //         }
    //         /*********** Head Implement ************/
    //         if($request['loan_emi_payment_mode'] == 0){
    //             $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    //             $v_no = "";
    //             for ($i = 0; $i < 10; $i++) {
    //                 $v_no .= $chars[mt_rand(0, strlen($chars)-1)];
    //             }
    //             $roidayBookRef = CommanTransactionsController::createBranchDayBookReference($roi+$principal_amount,date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date']))));
    //             $roibranchDayBook = CommanTransactionsController::branchDayBookNew($roidayBookRef,$branchId,5,52,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi+$principal_amount,$roi+$principal_amount,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','SSB A/C Cr '.($roi+$principal_amount).'','SSB A/C Cr '.($roi+$principal_amount).'','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$ssbAccountDetails->id,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$request['application_date'],date("H:i:s"),2,Auth::user()->id,$is_contra=NULL,$contra_id=NULL,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_id_from,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //             /*$roibranchDayBook = $this->createBranchDayBook($roidayBookRef,$branchId,5,52,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi+$principal_amount,$roi+$principal_amount,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','SSB A/C Cr '.($roi+$principal_amount).'','SSB A/C Cr '.($roi+$principal_amount).'','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$is_contra=NULL,$contra_id=NULL,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,31,5,523,$loanId,$ssb_transaction_id,$request['associate_member_id'],NULL,$mLoan->branch_id,$ssbAccountDetails->branch_id,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],NULL,NULL,$ssb_transaction_id,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,56,5,52,$loanId,$ssb_transaction_id,$request['associate_member_id'],NULL,$mLoan->branch_id,$ssbAccountDetails->branch_id,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],NULL,NULL,$ssb_transaction_id,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //             /*$roiallTransaction = $this->createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,52,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);
    //             $roiallTransaction = $this->createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,52,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
    //             if($mLoan->loan_type == '1'){
    //                 $loan_head_id = 64;
    //             }elseif($mLoan->loan_type == '2'){
    //                 $loan_head_id = 65;
    //             }elseif($mLoan->loan_type == '3'){
    //                 $loan_head_id = 66;
    //             }elseif($mLoan->loan_type == '4'){
    //                 $loan_head_id = 67;
    //             }
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,$loan_head_id,5,52,$loanId,$ssb_transaction_id,$request['associate_member_id'],NULL,$mLoan->branch_id,$ssbAccountDetails->branch_id,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],NULL,NULL,$ssb_transaction_id,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //             /*$principalallTransaction = $this->createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,52,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,56,5,52,$loanId,$ssb_transaction_id,$request['associate_member_id'],NULL,$mLoan->branch_id,$ssbAccountDetails->branch_id,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],NULL,NULL,$ssb_transaction_id,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //             /*$principalallTransaction = $this->createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,52,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
    //             $principalmemberTransaction = CommanTransactionsController::memberTransactionNew($roidayBookRef,5,52,$loanId,$ssb_transaction_id,$request['associate_member_id'],$mLoan->applicant_id,$branchId,$bank_id=NULL,$account_id=NULL,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$ssbAccountDetails->id,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$request['application_date'],date("H:i:s"),2,Auth::user()->id,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_id_from,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //             /*$principalmemberTransaction = $this->createMemberTransaction($roidayBookRef,5,52,$loanId,$ssb_transaction_id,$request['associate_member_id'],$mLoan->applicant_id,$branchId,$bank_id=NULL,$account_id=NULL,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
    //         }elseif($request['loan_emi_payment_mode'] == 2){
    //             $roidayBookRef = CommanTransactionsController::createBranchDayBookReference($roi+$principal_amount,date("Y-m-d H:i:s", strtotime(convertDate($request['application_date']))));
    //             $roibranchDayBook = CommanTransactionsController::branchDayBookNew($roidayBookRef,$branchId,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$roi+$principal_amount,$roi+$principal_amount,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','Cash A/C Dr '.($roi+$principal_amount).'','To '.$mLoan->account_number.' A/C Cr '.($roi+$principal_amount).'','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$is_contra=NULL,$contra_id=NULL,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,31,5,523,$loanId,$createDayBook,$request['associate_member_id'],NULL,$branchId,NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //             /*$roiallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,28,5,52,$loanId,$createDayBook,$request['associate_member_id'],NULL,$branchId,NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //             /*$roiallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
    //             if($mLoan->loan_type == '1'){
    //                 $loan_head_id = 64;
    //             }elseif($mLoan->loan_type == '2'){
    //                 $loan_head_id = 65;
    //             }elseif($mLoan->loan_type == '3'){
    //                 $loan_head_id = 66;
    //             }elseif($mLoan->loan_type == '4'){
    //                 $loan_head_id = 67;
    //             }
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,$loan_head_id,5,52,$loanId,$createDayBook,$request['associate_member_id'],NULL,$branchId,NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //             /*$principalallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,28,5,52,$loanId,$createDayBook,$request['associate_member_id'],NULL,$branchId,NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //             /*$principalallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
    //             $principalmemberTransaction = CommanTransactionsController::memberTransactionNew($roidayBookRef,5,52,$loanId,$createDayBook,$request['associate_member_id'],$mLoan->applicant_id,$branchId,$bank_id=NULL,$account_id=NULL,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //             $createRoiBranchCash = $this->updateBranchCashFromBackDate($roi+$principal_amount,$branchId,date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date']))));
    //         }elseif($request['loan_emi_payment_mode'] == 1){
    //             if($mLoan->loan_type == '1'){
    //                 $loan_head_id = 64;
    //             }elseif($mLoan->loan_type == '2'){
    //                 $loan_head_id = 65;
    //             }elseif($mLoan->loan_type == '3'){
    //                 $loan_head_id = 66;
    //             }elseif($mLoan->loan_type == '4'){
    //                 $loan_head_id = 67;
    //             }
    //             if($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != ''){
    //                 $payment_type = 1;
    //                 $cheque_type = 1;
    //                 $amount_from_id =$request['associate_member_id'];
    //                 $amount_from_name = getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name;
    //                 $cheque_no = $request['customer_cheque'];
    //                 $cheque_date = NULL;
    //                 $cheque_bank_from = $request['customer_bank_name'];
    //                 $cheque_bank_ac_from = $request['customer_bank_account_number'];
    //                 $cheque_bank_ifsc_from = $request['customer_ifsc_code'];
    //                 $cheque_bank_branch_from=NULL;
    //                 $cheque_bank_to=$request['company_bank'];
    //                 $cheque_bank_ac_to=$request['bank_account_number'];
    //                 $v_no=NULL;
    //                 $v_date=NULL;
    //                 $ssb_account_id_from=NULL;
    //                 $transction_no = NULL;
    //                 $transction_bank_from = NULL;
    //                 $transction_bank_ac_from = NULL;
    //                 $transction_bank_ifsc_from = NULL;
    //                 $transction_bank_branch_from = NULL;
    //                 $transction_bank_to = $request['company_bank'];
    //                 $transction_bank_ac_to = $request['bank_account_number'];
    //             }elseif($request['bank_transfer_mode'] == 1){
    //                 $payment_type = 2;
    //                 $cheque_type = NULL;
    //                 $amount_from_id =$request['associate_member_id'];
    //                 $amount_from_name = getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name;
    //                 $cheque_no = NULL;
    //                 $cheque_date = NULL;
    //                 $cheque_bank_from = NULL;
    //                 $cheque_bank_ac_from = NULL;
    //                 $cheque_bank_ifsc_from = NULL;
    //                 $cheque_bank_branch_from = NULL;
    //                 $cheque_bank_to = NULL;
    //                 $cheque_bank_ac_to = NULL;
    //                 $transction_no = $request['utr_transaction_number'];
    //                 $v_no=NULL;
    //                 $v_date=NULL;
    //                 $ssb_account_id_from=NULL;
    //                 $transction_bank_from = $request['customer_bank_name'];
    //                 $transction_bank_ac_from = $request['customer_bank_account_number'];
    //                 $transction_bank_ifsc_from = $request['customer_ifsc_code'];
    //                 $transction_bank_branch_from = $request['customer_branch_name'];
    //                 $transction_bank_to = $request['company_bank'];
    //                 $transction_bank_ac_to = $request['bank_account_number'];
    //             }
    //             $roidayBookRef = CommanTransactionsController::createBranchDayBookReference($roi+$principal_amount,date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date']))));
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,31,5,523,$loanId,$createDayBook,$request['associate_member_id'],NULL,NULL,NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,NULL,NULL,NULL,$cheque_type,NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,NULL,NULL,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_from,$branchId,$transction_bank_ac_to,getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,NULL,NULL,$transction_bank_to,$transction_bank_ac_to,getSamraddhBank($request['company_bank'])->bank_name,$transction_bank_ac_to,NULL,getSamraddhBankAccount($request['bank_account_number'])->ifsc_code,NULL,1,Auth::user()->id);
    //             /*$roiallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,5,52,$loanId,$createDayBook,$request['associate_member_id'],NULL,NULL,NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,NULL,NULL,NULL,$cheque_type,NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,NULL,NULL,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_from,$branchId,$transction_bank_ac_to,getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,NULL,NULL,$transction_bank_to,$transction_bank_ac_to,getSamraddhBank($request['company_bank'])->bank_name,$transction_bank_ac_to,NULL,getSamraddhBankAccount($request['bank_account_number'])->ifsc_code,NULL,1,Auth::user()->id);
    //             /*$allTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,$head5=NULL,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,$loan_head_id,5,52,$loanId,$createDayBook,$request['associate_member_id'],NULL,NULL,NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,NULL,NULL,NULL,$cheque_type,NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,NULL,NULL,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_from,$branchId,$transction_bank_ac_to,getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,NULL,NULL,$transction_bank_to,$transction_bank_ac_to,getSamraddhBank($request['company_bank'])->bank_name,$transction_bank_ac_to,NULL,getSamraddhBankAccount($request['bank_account_number'])->ifsc_code,NULL,1,Auth::user()->id);
    //             /*$principalallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,5,52,$loanId,$createDayBook,$request['associate_member_id'],NULL,NULL,NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,NULL,NULL,NULL,$cheque_type,NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,NULL,NULL,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_from,$branchId,$transction_bank_ac_to,getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,NULL,NULL,$transction_bank_to,$transction_bank_ac_to,getSamraddhBank($request['company_bank'])->bank_name,$transction_bank_ac_to,NULL,getSamraddhBankAccount($request['bank_account_number'])->ifsc_code,NULL,1,Auth::user()->id);
    //             /*$allTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,$head5=NULL,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
    //             $roibranchDayBook = CommanTransactionsController::branchDayBookNew($roidayBookRef,$branchId,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$roi+$principal_amount,$roi+$principal_amount,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','Online A/C Dr '.($roi+$principal_amount).'','To '.$mLoan->account_number.' A/C Cr '.($roi+$principal_amount).'','CR',$payment_type,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$is_contra=NULL,$contra_id=NULL,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //             $principalmemberTransaction = CommanTransactionsController::memberTransactionNew($roidayBookRef,5,52,$loanId,$createDayBook,$request['associate_member_id'],$mLoan->applicant_id,$branchId,$bank_id=NULL,$account_id=NULL,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','DR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //             $samraddhBankDaybook = CommanTransactionsController::samraddhBankDaybookNew($roidayBookRef,$bank_id=NULL,$account_id=NULL,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$roi+$principal_amount,$roi+$principal_amount,$roi+$principal_amount,'EMI collection','Online A/C Cr. '.($roi+$principal_amount).'','Online A/C Cr. '.($roi+$principal_amount).'','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,getSamraddhBank($request['company_bank'])->bank_name,$transction_bank_ac_to,NULL,getSamraddhBankAccount($request['bank_account_number'])->ifsc_code,$request['application_date'],$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL);
    //             $createPrincipleBankClosing = CommanTransactionsController::updateBackDateloanBankBalance($roi+$principal_amount,$request['company_bank'],getSamraddhBankAccount($request['bank_account_number'])->id,date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date']))),0,0);
    //         }
    //         /*********** Head Implement ************/
    //         /*---------- commission script  start  ---------*/
    //         $daybookId=$createDayBook;
    //         $total_amount=$request['deposite_amount'];
    //         $percentage=2;
    //         $month=NULL;
    //         $type_id=$request['loan_id'];
    //         $type=4;
    //         $associate_id=$request['associate_member_id'];
    //         $branch_id=$branchId;
    //         $commission_type=0;
    //         $associateDetail=Member::where('id',$associate_id)->first();
    //         $carder=$associateDetail->current_carder_id;
    //         $associate_exist=0;
    //         $percentInDecimal = $percentage / 100;
    //         $commission_amount = round($percentInDecimal * $total_amount,4);
    //         $associateCommission['member_id'] = $associate_id;
    //         $associateCommission['branch_id'] = $branch_id;
    //         $associateCommission['type'] = $type;
    //         $associateCommission['type_id'] = $type_id;
    //         $associateCommission['day_book_id'] = $daybookId;
    //         $associateCommission['total_amount'] = $total_amount;
    //         $associateCommission['month'] = $month;
    //         $associateCommission['commission_amount'] = $commission_amount;
    //         $associateCommission['percentage'] = $percentage;
    //         $associateCommission['commission_type'] = $commission_type;
    //         $date =\App\Models\Daybook::where('id',$daybookId)->first();
    //         $associateCommission['created_at'] = $date->created_at;
    //         $associateCommission['pay_type'] = 4;
    //         $associateCommission['carder_id'] = $carder;
    //         $associateCommission['associate_exist'] = $associate_exist;
    //         $associateCommissionInsert = \App\Models\CommissionEntryLoan::create($associateCommission);
    //         // Collection start
    //         /*$collection_percentage=5;
    //         $collection_type=6;
    //         $Collection_associate_id=$mLoan->associate_member_id;
    //         $collection_associateDetail=Member::where('id',$Collection_associate_id)->first();
    //         $Collection_carder=$collection_associateDetail->current_carder_id;
    //         $collection_percentInDecimal = $collection_percentage / 100;
    //         $collection_commission_amount = round($collection_percentInDecimal * $total_amount,4);
    //         $coll_associateCommission['member_id'] = $Collection_associate_id;
    //         $coll_associateCommission['branch_id'] = $branch_id;
    //         $coll_associateCommission['type'] = $collection_type;
    //         $coll_associateCommission['type_id'] = $type_id;
    //         $coll_associateCommission['day_book_id'] = $daybookId;
    //         $coll_associateCommission['total_amount'] = $total_amount;
    //         $coll_associateCommission['month'] = $month;
    //         $coll_associateCommission['commission_amount'] = $collection_commission_amount;
    //         $coll_associateCommission['percentage'] = $collection_percentage;
    //         $coll_associateCommission['commission_type'] = $commission_type;
    //         $coll_date =\App\Models\Daybook::where('id',$daybookId)->first();
    //         $coll_associateCommission['created_at'] = $coll_date->created_at;
    //         $coll_associateCommission['pay_type'] = 4;
    //         $coll_associateCommission['carder_id'] = $Collection_carder;
    //         $coll_associateCommission['associate_exist'] = $associate_exist;
    //         $coll_associateCommissionInsert = \App\Models\CommissionEntryLoan::create($coll_associateCommission);*/
    //         /*---------- commission script  end  ---------*/
    //         $createLoanDayBook = CommanTransactionsController::createLoanDayBook($daybookId,$mLoan->loan_type,0,$mLoan->id,$lId=NULL,$mLoan->account_number,$mLoan->applicant_id,$roi,$principal_amount,$dueAmount,$request['deposite_amount'],'Loan EMI deposit',$branchId,getBranchCode($branchId)->branch_code,'CR','INR',$paymentMode,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,1,1,$cheque_date,$account_number,NULL,$request['loan_associate_name'],$request['associate_member_id'],$branchId);
    //         if($request['penalty_amount'] != '' && $request['penalty_amount'] > 0){
    //             $amountArray=array('1'=>$request['penalty_amount']);
    //             if($request['loan_emi_payment_mode'] == 0){
    //                 $cheque_dd_no=NULL;
    //                 $online_payment_id=NULL;
    //                 $online_payment_by=NULL;
    //                 $bank_name=NULL;
    //                 $cheque_date=NULL;
    //                 $account_number=NULL;
    //                 $paymentMode=4;
    //                 $ssbpaymentMode=3;
    //                 $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //                 $ssb['saving_account_id']=$ssbAccountDetails->id;
    //                 $ssb['account_no']=$ssbAccountDetails->account_no;
    //                 $ssb['opening_balance']=$request['ssb_account']-$request['penalty_amount'];
    //                 $ssb['deposit']=0;
    //                 $ssb['withdrawal']=$request['penalty_amount'];
    //                 $ssb['description']='Loan EMI Penalty';
    //                 $ssb['currency_code']='INR';
    //                 $ssb['payment_type']='DR';
    //                 $ssb['payment_mode']=$ssbpaymentMode;
    //                 $ssb['created_at'] = $request['created_at'];
    //                 $ssbAccountTran = SavingAccountTranscation::create($ssb);
    //                 $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;
    //                 // update saving account current balance
    //                 $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
    //                 $ssbBalance->balance=$request['ssb_account']-$request['penalty_amount'];
    //                 $ssbBalance->save();
    //                 $data['saving_account_transaction_id']=$ssb_transaction_id;
    //                 $data['loan_id']=$request['loan_id'];
    //                 $data['created_at']=$request['created_at'];
    //                 $satRef = TransactionReferences::create($data);
    //                 $satRefId = $satRef->id;
    //                 $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,1,$ssbAccountDetails->id,$ssbAccountDetails->member_id,$branchId,getBranchCode($branchId)->branch_code,$amountArray,$paymentMode,$request['loan_associate_name'],$request['associate_member_id'],$ssbAccountDetails->account_no,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'DR');
    //                 $totalbalance = $request['ssb_account']-$request['penalty_amount'];
    //                 $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->member_investments_id,0,$ssbAccountDetails->member_id,$totalbalance,0,$request['penalty_amount'],'Withdrawal from SSB',$request['account_number'],$branchId,getBranchCode($branchId)->branch_code,$amountArray,$paymentMode,$request['loan_associate_name'],$request['associate_member_id'],$ssbAccountDetails->account_no,$cheque_dd_no,NULL,NULL,$paymentDate,$online_payment_by,$online_payment_by,$ssbAccountDetails->id,'DR');
    //             }elseif($request['loan_emi_payment_mode'] == 1){
    //                 if($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != ''){
    //                     $paymentMode = 1;
    //                     $cheque_dd_no = $request['customer_cheque'];
    //                     $ssbpaymentMode=5;
    //                     $online_payment_id = NULL;
    //                     $online_payment_by=NULL;
    //                     $satRefId = NULL;
    //                     $bank_name=NULL;
    //                     $cheque_date=NULL;
    //                     $account_number=NULL;
    //                     $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));;
    //                 }elseif($request['bank_transfer_mode'] == 1){
    //                     $paymentMode = 2;
    //                     $cheque_dd_no = NULL;
    //                     $ssbpaymentMode=5;
    //                     $online_payment_id = $request['utr_transaction_number'];
    //                     $online_payment_by=NULL;
    //                     $satRefId = NULL;
    //                     $bank_name=NULL;
    //                     $cheque_date=NULL;
    //                     $account_number=NULL;
    //                     $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));;
    //                 }
    //                 $ssb_transaction_id = '';
    //                 $ssb_account_id_from = '';
    //             }elseif($request['loan_emi_payment_mode'] == 2){
    //                 $cheque_dd_no=NULL;
    //                 $paymentMode=0;
    //                 $ssbpaymentMode=0;
    //                 $online_payment_id=NULL;
    //                 $online_payment_by=NULL;
    //                 $satRefId = NULL;
    //                 $bank_name=NULL;
    //                 $cheque_date=NULL;
    //                 $account_number=NULL;
    //                 $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //                 $ssb_transaction_id = '';
    //                 $ssb_account_id_from = '';
    //             }
    //             $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,11,$request['loan_id'],$mLoan->applicant_id,$branchId,getBranchCode($branchId)->branch_code,$amountArray,$paymentMode,$request['loan_associate_name'],$request['associate_member_id'],$mLoan->account_number,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'CR');
    //             $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,11,$request['loan_id'],0,$mLoan->applicant_id,$dueAmount,$request['penalty_amount'],0,'Loan EMI penalty',$mLoan->account_number,$branchId,getBranchCode($branchId)->branch_code,$amountArray,$paymentMode,$request['loan_associate_name'],$request['associate_member_id'],$mLoan->account_number,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'CR');
    //             if($request['loan_emi_payment_mode'] == 3){
    //                 $checkData['type']=4;
    //                 $checkData['branch_id']=$branchId;
    //                 // $checkData['loan_id']=$request['loan_id'];
    //                 $checkData['day_book_id']=$createDayBook;
    //                 $checkData['cheque_id']=$cheque_dd_no;
    //                 $checkData['status']=1;
    //                 $checkData['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //                 $ssbAccountTran = ReceivedChequePayment::create($checkData);
    //                 $dataRC['status']=3;
    //                 $receivedcheque = ReceivedCheque::find($cheque_dd_no);
    //                 $receivedcheque->update($dataRC);
    //             }
    //             /************* Head Implement ****************/
    //             if($request['loan_emi_payment_mode'] == 0){
    //                 $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    //                 $v_no = "";
    //                 for ($i = 0; $i < 10; $i++) {
    //                     $v_no .= $chars[mt_rand(0, strlen($chars)-1)];
    //                 }
    //                 $penaltyDayBookRef = CommanTransactionsController::createBranchDayBookReference($request['penalty_amount'],$request['application_date']);
    //                 $roibranchDayBook = CommanTransactionsController::branchDayBookNew($penaltyDayBookRef,$branchId,5,53,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','SSB A/C Cr '.$request['penalty_amount'].'','SSB A/C Cr '.$request['penalty_amount'].'','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$is_contra=NULL,$contra_id=NULL,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_id_from,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //                 $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef,$branchId,NULL,NULL,33,5,53,$loanId,$ssb_transaction_id,$request['associate_member_id'],NULL,$mLoan->branch_id,$ssbAccountDetails->branch_id,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],NULL,NULL,$ssb_transaction_id,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //                 /*$roiallTransaction = $this->createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,53,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
    //                 $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef,$branchId,NULL,NULL,56,5,53,$loanId,$ssb_transaction_id,$request['associate_member_id'],NULL,$mLoan->branch_id,$ssbAccountDetails->branch_id,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],NULL,NULL,$ssb_transaction_id,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //                 /*$roiallTransaction = $this->createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,53,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
    //                 $roimemberTransaction = CommanTransactionsController::memberTransactionNew($penaltyDayBookRef,5,53,$loanId,$ssb_transaction_id,$request['associate_member_id'],$mLoan->applicant_id,$branchId,$bank_id=NULL,$account_id=NULL,$request['penalty_amount'],'Loan Panelty Charge','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$request['application_date'],date("H:i:s"),2,Auth::user()->id,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_id_from,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //                 /*$roimemberTransaction = $this->createMemberTransaction($penaltyDayBookRef,5,53,$loanId,$ssb_transaction_id,$request['associate_member_id'],$mLoan->applicant_id,$branchId,$bank_id=NULL,$account_id=NULL,$request['penalty_amount'],'Loan Panelty Charge','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id); */
    //             }elseif($request['loan_emi_payment_mode'] == 2){
    //                 $penaltyDayBookRef = CommanTransactionsController::createBranchDayBookReference($request['penalty_amount'],$request['application_date']);
    //                 $roibranchDayBook = CommanTransactionsController::branchDayBookNew($penaltyDayBookRef,$branchId,5,53,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','Cash A/C Dr '.$request['penalty_amount'].'','To '.$mLoan->account_number.' A/C Cr '.$request['penalty_amount'].'','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$is_contra=NULL,$contra_id=NULL,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //                 $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef,$branchId,NULL,NULL,33,5,53,$loanId,$createDayBook,$request['associate_member_id'],NULL,$branchId,NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //                 /*$roiallTransaction = CommanTransactionsController::createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,53,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
    //                 $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef,$branchId,NULL,NULL,28,5,53,$loanId,$createDayBook,$request['associate_member_id'],NULL,$branchId,NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','DR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //                 /*$roiallTransaction = CommanTransactionsController::createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,53,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
    //                 $roimemberTransaction = CommanTransactionsController::memberTransactionNew($penaltyDayBookRef,5,53,$loanId,$createDayBook,$request['associate_member_id'],$mLoan->applicant_id,$branchId,$bank_id=NULL,$account_id=NULL,$request['penalty_amount'],'Loan Panelty Charge','DR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //                 $createRoiBranchCash = $this->updateBranchCashFromBackDate($request['penalty_amount'],$branchId,date("Y-m-d  ".$entryTime."", strtotime(convertDate($request['application_date']))));
    //             }elseif($request['loan_emi_payment_mode'] == 1){
    //                 if($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != ''){
    //                     $payment_type = 1;
    //                     $cheque_type = 1;
    //                     $amount_from_id =$request['associate_member_id'];
    //                     $amount_from_name = getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name;
    //                     $cheque_no = $request['customer_cheque'];
    //                     $cheque_date = NULL;
    //                     $cheque_bank_from = $request['customer_bank_name'];
    //                     $cheque_bank_ac_from = $request['customer_bank_account_number'];
    //                     $cheque_bank_ifsc_from = $request['customer_ifsc_code'];
    //                     $cheque_bank_branch_from=NULL;
    //                     $cheque_bank_to=$request['company_bank'];
    //                     $cheque_bank_ac_to=$request['bank_account_number'];
    //                     $v_no=NULL;
    //                     $v_date=NULL;
    //                     $ssb_account_id_from=NULL;
    //                     $transction_no = NULL;
    //                     $transction_bank_from = NULL;
    //                     $transction_bank_ac_from = NULL;
    //                     $transction_bank_ifsc_from = NULL;
    //                     $transction_bank_branch_from = NULL;
    //                     $transction_bank_to = NULL;
    //                     $transction_bank_ac_to = NULL;
    //                 }elseif($request['bank_transfer_mode'] == 1){
    //                     $payment_type = 2;
    //                     $cheque_type = NULL;
    //                     $amount_from_id =$request['associate_member_id'];
    //                     $amount_from_name = getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name;
    //                     $cheque_no = NULL;
    //                     $cheque_date = NULL;
    //                     $cheque_bank_from = NULL;
    //                     $cheque_bank_ac_from = NULL;
    //                     $cheque_bank_ifsc_from = NULL;
    //                     $cheque_bank_branch_from = NULL;
    //                     $cheque_bank_to = NULL;
    //                     $cheque_bank_ac_to = NULL;
    //                     $transction_no = $request['utr_transaction_number'];
    //                     $v_no=NULL;
    //                     $v_date=NULL;
    //                     $ssb_account_id_from=NULL;
    //                     $transction_bank_from = $request['customer_bank_name'];
    //                     $transction_bank_ac_from = $request['customer_bank_account_number'];
    //                     $transction_bank_ifsc_from = $request['customer_ifsc_code'];
    //                     $transction_bank_branch_from = $request['customer_branch_name'];
    //                     $transction_bank_to = $request['company_bank'];
    //                     $transction_bank_ac_to = $request['bank_account_number'];
    //                 }
    //                 $penaltyDayBookRef = CommanTransactionsController::createBranchDayBookReference($request['penalty_amount'],date("Y-m-d  ".$entryTime."", strtotime(convertDate($request['application_date']))));
    //                 $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef,$branchId,NULL,NULL,33,5,53,$loanId,$createDayBook,$request['associate_member_id'],NULL,NULL,NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,NULL,NULL,NULL,$cheque_type,NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,NULL,NULL,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_from,$branchId,$transction_bank_ac_to,getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,NULL,NULL,$transction_bank_to,$transction_bank_ac_to,getSamraddhBank($request['company_bank'])->bank_name,$transction_bank_ac_to,NULL,getSamraddhBankAccount($request['bank_account_number'])->ifsc_code,NULL,1,Auth::user()->id);
    //                 /*$roiallTransaction = CommanTransactionsController::createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,53,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
    //                 $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef,$branchId,NULL,NULL,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,5,53,$loanId,$createDayBook,$request['associate_member_id'],NULL,NULL,NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','DR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,NULL,NULL,NULL,$cheque_type,NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,NULL,NULL,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_from,$branchId,$transction_bank_ac_to,getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,NULL,NULL,$transction_bank_to,$transction_bank_ac_to,getSamraddhBank($request['company_bank'])->bank_name,$transction_bank_ac_to,NULL,getSamraddhBankAccount($request['bank_account_number'])->ifsc_code,NULL,1,Auth::user()->id);
    //                 /*$allTransaction = CommanTransactionsController::createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,$head5=NULL,5,53,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
    //                 $roibranchDayBook = CommanTransactionsController::branchDayBookNew($penaltyDayBookRef,$branchId,5,53,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','Cash A/C Dr '.$request['penalty_amount'].'','To '.$mLoan->account_number.' A/C Cr '.$request['penalty_amount'].'','CR',$payment_type,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$is_contra=NULL,$contra_id=NULL,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //                 $principalmemberTransaction = CommanTransactionsController::memberTransactionNew($penaltyDayBookRef,5,53,$loanId,$createDayBook,$request['associate_member_id'],$mLoan->applicant_id,$branchId,$bank_id=NULL,$account_id=NULL,$request['penalty_amount'],'Loan Panelty Charge','DR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$request['application_date'],$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //                 $samraddhBankDaybook = CommanTransactionsController::samraddhBankDaybookNew($penaltyDayBookRef,$bank_id=NULL,$account_id=NULL,5,53,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','Cash A/C Cr. '.$request['penalty_amount'].'','Cash A/C Cr. '.$request['penalty_amount'].'','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$request['application_date'],$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$jv_unique_id,$cheque_type,$cheque_id,$ssb_account_tran_id_to,$ssb_account_tran_id_from);
    //                 $createPrincipleBankClosing = CommanTransactionsController::updateBackDateloanBankBalance($request['penalty_amount'],$request['company_bank'],getSamraddhBankAccount($request['bank_account_number'])->id,date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date']))),0,0);
    //             }
    //             /************* Head Implement ****************/
    //             /*---------- penalty commission script  start  ---------*/
    //             /*$daybookId=$createDayBook;
    //             $total_amount=$request['penalty_amount'];
    //             $month=NULL;
    //             $type_id=$request['loan_id'];
    //             $branch_id=$branchId;
    //             $associate_exist=0;
    //             $commission_type=0;
    //             // Collection start
    //             $collection_percentage=5;
    //             $collection_type=6;
    //             $Collection_associate_id=$mLoan->associate_member_id;
    //             $collection_associateDetail=Member::where('id',$Collection_associate_id)->first();
    //             $Collection_carder=$collection_associateDetail->current_carder_id;
    //             $collection_percentInDecimal = $collection_percentage / 100;
    //             $collection_commission_amount = round($collection_percentInDecimal * $total_amount,4);
    //             $coll_associateCommission['member_id'] = $Collection_associate_id;
    //             $coll_associateCommission['branch_id'] = $branch_id;
    //             $coll_associateCommission['type'] = $collection_type;
    //             $coll_associateCommission['type_id'] = $type_id;
    //             $coll_associateCommission['day_book_id'] = $daybookId;
    //             $coll_associateCommission['total_amount'] = $total_amount;
    //             $coll_associateCommission['month'] = $month;
    //             $coll_associateCommission['commission_amount'] = $collection_commission_amount;
    //             $coll_associateCommission['percentage'] = $collection_percentage;
    //             $coll_associateCommission['commission_type'] = $commission_type;
    //             $coll_date =\App\Models\Daybook::where('id',$daybookId)->first();
    //             $coll_associateCommission['created_at'] = $coll_date->created_at;
    //             $coll_associateCommission['pay_type'] = 4;
    //             $coll_associateCommission['carder_id'] = $Collection_carder;
    //             $coll_associateCommission['associate_exist'] = $associate_exist;
    //             $coll_associateCommissionInsert = \App\Models\CommissionEntryLoan::create($coll_associateCommission);*/
    //             /*---------- penalty commission script  end  ---------*/
    //             if($request['loan_emi_payment_mode'] == 0){
    //                 $paymentMode = 4;
    //                 $cheque_dd_no = NULL;
    //                 $ssbpaymentMode=5;
    //                 $online_payment_id = NULL;
    //                 $online_payment_by=NULL;
    //                 $satRefId = NULL;
    //                 $bank_name=NULL;
    //                 $cheque_date=NULL;
    //                 $account_number=NULL;
    //                 $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));;
    //             }elseif($request['loan_emi_payment_mode'] == 1){
    //                 if($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != ''){
    //                     $paymentMode = 1;
    //                     $cheque_dd_no = $request['customer_cheque'];
    //                     $ssbpaymentMode=5;
    //                     $online_payment_id = NULL;
    //                     $online_payment_by=NULL;
    //                     $satRefId = NULL;
    //                     $bank_name=NULL;
    //                     $cheque_date=NULL;
    //                     $account_number=NULL;
    //                     $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));;
    //                 }elseif($request['bank_transfer_mode'] == 1){
    //                     $paymentMode = 2;
    //                     $cheque_dd_no = NULL;
    //                     $ssbpaymentMode=5;
    //                     $online_payment_id = $request['utr_transaction_number'];
    //                     $online_payment_by=NULL;
    //                     $satRefId = NULL;
    //                     $bank_name=NULL;
    //                     $cheque_date=NULL;
    //                     $account_number=NULL;
    //                     $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));;
    //                 }
    //             }elseif($request['loan_emi_payment_mode'] == 2){
    //                 $cheque_dd_no=NULL;
    //                 $paymentMode=0;
    //                 $ssbpaymentMode=0;
    //                 $online_payment_id=NULL;
    //                 $online_payment_by=NULL;
    //                 $satRefId = NULL;
    //                 $bank_name=NULL;
    //                 $cheque_date=NULL;
    //                 $account_number=NULL;
    //                 $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));;
    //             }
    //             $createLoanDayBook = CommanTransactionsController::createLoanDayBook($createDayBook,$mLoan->loan_type,1,$mLoan->id,$lId=NULL,$mLoan->account_number,$mLoan->applicant_id,$request['penalty_amount'],$request['penalty_amount'],$opening_balance=NULL,$request['penalty_amount'],'Loan EMI penalty',$branchId,getBranchCode($branchId)->branch_code,'CR','INR',$paymentMode,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,1,1,$cheque_date,$account_number,NULL,$request['loan_associate_name'],$request['associate_member_id'],$branchId);
    //         }
    //     DB::commit();
    //     } catch (\Exception $ex) {
    //         DB::rollback();
    //         return back()->with('alert', $ex->getMessage());
    //     }
    //     return back()->with('success', 'Loan EMI Successfully submitted!');
    // }
    public function depositeLoanEmi(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $entryTime = date("H:i:s");
            if($request['penalty_amount']>0)
            {
                $penalty = $request['penalty_amount'];
            }
            else{
                $penalty = 0;
            }
            Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))));
            $branchId = branchName()->id;
            //$branchId = $branchId;
            $loanId = $request['loan_id'];
            if ($request['loan_emi_payment_mode'] == 0)
            {
                $request
                    ->request
                    ->add(['code' => $request['loan_associate_code']]);
                $request
                    ->request
                    ->add(['applicationDate' => $request['application_date']]);
                // $result = $this->getCollectorAssociate($request)->getData();
                // $ssbAccountDetails->balance = $result->ssbAmount;
                if(!empty( $request['ssb_id']) && $request['ssb_id']!= 0)
                {
                    $ssbAccountDetails = SavingAccount::with('ssbMember')->where('id', $request['ssb_id'])->first();
                }
                if ($ssbAccountDetails->balance < $request['deposite_amount'])
                {
                    return back()->with('alert', 'Insufficient balance in ssb account!');
                }
            }
            $mLoan = Memberloans::with('loanMember')->where('id', $request['loan_id'])->first();
            $stateid = getBranchState($mLoan['loanBranch']->name);
            // $globaldate = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
            $globaldate = date('Y-m-d',strtotime(convertDate($request['application_date'])));
            $getHeadSetting = \App\Models\HeadSetting::where('head_id',33)->first();
            $getGstSetting = \App\Models\GstSetting::where('state_id',$mLoan['loanBranch']->state_id)->where('applicable_date','<=',$globaldate)->exists();
            $getGstSettingno = \App\Models\GstSetting::select('id','gst_no')->where('state_id',$mLoan['loanBranch']->state_id)->where('applicable_date','<=',$globaldate)->first();
            $gstAmount = 0;
            if($request['penalty_amount']>0 && $getGstSetting )
            {
                if($mLoan['loanBranch']->state_id == $stateid)
                {
                    $gstAmount =  (($request['penalty_amount']*$getHeadSetting->gst_percentage)/100)/2;
                    $cgstHead = 171;
                    $sgstHead = 172;
                    $IntraState = true;
                    $gstAmount = ceil($gstAmount);
                }
                else{
                    $gstAmount =  ($request['penalty_amount']*$getHeadSetting->gst_percentage)/100;
                    $cgstHead = 170;
                    $IntraState = false;
                    $gstAmount = ceil($gstAmount);
                }
                $penalty = $request['penalty_amount'];
            }
            else{
                $penalty = 0;
            }
            $LoanCreatedDate = date('Y-m-d',strtotime($mLoan->approve_date));
            $LoanCreatedYear = date('Y',strtotime($mLoan->approve_date));
            $LoanCreatedMonth = date('m',strtotime($mLoan->approve_date));
            $LoanCreateDate = date('d',strtotime($mLoan->approve_date));
            $currentDate = date('Y-m-d');
            $CurrentDate= date('d');
            $CurrentDateYear = date('Y');
            $CurrentDateMonth = date('m');
            $applicationDate = date('Y-m-d',strtotime(convertDate($request['application_date'])));
            $applicationCurrentDate= date('d',strtotime(convertDate($request['application_date'])));
            $applicationCurrentDateYear =date('Y',strtotime(convertDate($request['application_date'])));
            $applicationCurrentDateMonth =date('m',strtotime(convertDate($request['application_date'])));
            if ($mLoan->emi_option == 1){ //Month
                $daysDiff =(($CurrentDateYear - $LoanCreatedYear) * 12) + ($CurrentDateMonth - $LoanCreatedMonth);
                $daysDiff2 =today()->diffInDays($LoanCreatedDate);
                $nextEmiDates = $this->nextEmiDates($daysDiff,$LoanCreatedDate);
                $nextEmiDates2 = $this->nextEmiDatesDays($daysDiff2,$LoanCreatedDate);
                $nextEmiDates3 = $this->nextEmiDates($mLoan->emi_period,$LoanCreatedDate);
            }
            if ($mLoan->emi_option == 2){ //Week
                $daysDiff =today()->diffInDays($LoanCreatedDate);
                $daysDiff = $daysDiff/7;
                $nextEmiDates2 = $this->nextEmiDatesDays($daysDiff,$LoanCreatedDate);
                $nextEmiDates = $this->nextEmiDatesWeek($daysDiff,$LoanCreatedDate);
            }
            if ($mLoan->emi_option == 3){  //Days
                $daysDiff =today()->diffInDays($LoanCreatedDate);
                $nextEmiDates = $this->nextEmiDatesDays($daysDiff,$LoanCreatedDate);
            }
            // $currentEmiDate = $nextEmiDates[$CurrentDate.'_'.$CurrentDateMonth.'_'.$CurrentDateYear];
            $totalDayInterest =0;
            $totalDailyInterest = 0;
            $newApplicationDate = explode('-',$applicationDate);
            $StartnewDAte = $applicationCurrentDateYear.'-'. $applicationCurrentDateMonth.'-01';
            $EndnewDAte = $applicationCurrentDateYear.'-'. $applicationCurrentDateMonth.'-31';
            $dailyoutstandingAmount = 0;
            $lastOutstanding = LoanEmisNew::where('loan_id',$mLoan->id)->where('loan_type',$mLoan->loan_type)->orderBy('id','desc')->first();
            $lastOutstandingDate = LoanEmisNew::where('loan_id',$mLoan->id)->where('loan_type',$mLoan->loan_type)->pluck('emi_date')->toArray();
                // $eniDateOutstandingArray = array_values($lastOutstandingDate);
            $newDate = array();
            //$checkDate = array_intersect($nextEmiDates,$lastOutstandingDate);
            $deposit = $request['deposite_amount'];
        if(isset($lastOutstanding->out_standing_amount) )
        {
            $checkDateMonth = date('m',strtotime($lastOutstanding->emi_date));
            $checkDateYear = date('Y',strtotime($lastOutstanding->emi_date));
            $newstartDate = $checkDateYear.'-'.  $checkDateMonth.'-01';
            $newEndDate = $checkDateYear.'-'.  $checkDateMonth.'-31';
            $gapDayes = Carbon::parse($lastOutstanding->emi_date)->diff(Carbon::parse($applicationDate))->format('%a');
            $lastOutstanding2 = LoanEmisNew::where('loan_id',$mLoan->id)->where('loan_type',$mLoan->loan_type)->whereBetween('emi_date',[$newstartDate,$newEndDate])->sum('out_standing_amount');
            $roi = ((($mLoan->ROI) / 365) * $lastOutstanding->out_standing_amount) / 100;
            if($mLoan->emi_option == 1 || $mLoan->emi_option == 2)
            {
                $roi = $roi * $gapDayes;
                $principal_amount =$deposit - $roi;
                if(!array_key_exists($applicationCurrentDate.'_'.$applicationCurrentDateMonth.'_'.$applicationCurrentDateYear,$nextEmiDates)){
                    $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit );
                }else{
                    $preDate =current($nextEmiDates);
                    $oldDate = $nextEmiDates[$applicationCurrentDate.'_'.$applicationCurrentDateMonth.'_'.$applicationCurrentDateYear];
                    if($mLoan->emi_option == 1)
                    {
                        $previousDate = Carbon::parse($oldDate)->subMonth(1);
                    }
                    if($mLoan->emi_option == 2)
                    {
                        $previousDate = Carbon::parse($oldDate)->subDays(7);
                    }
                    $pDate = date('Y-m-d',strtotime("+1 day",strtotime($previousDate)))  ;
                    if($preDate == $applicationDate)
                    {
                        $aqmount = LoanEmisNew::where('loan_id',$mLoan->id)->whereBetween('emi_date',[$LoanCreatedDate,$applicationDate])->sum('roi_amount');
                    }
                    else{
                        $aqmount = LoanEmisNew::where('loan_id',$mLoan->id)->whereBetween('emi_date',[$pDate,$applicationDate])->sum('roi_amount');
                    }
                    if($aqmount > 0)
                    {
                        $outstandingAmount = ($lastOutstanding->out_standing_amount -$deposit + $roi   + $aqmount);
                    }else{
                        $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi );
                    }
                }
               $dailyoutstandingAmount = $outstandingAmount + $roi;
            }
            else{
                $principal_amount =$deposit - $roi;
                $outstandingAmount = ($lastOutstanding->out_standing_amount - $principal_amount );
            }
            $deposit =  $request['deposite_amount'];
        }
        else{
            $roi = ((($mLoan->ROI) / 365) * $mLoan->amount) / 100;
            $gapDayes = Carbon::parse($mLoan->approve_date)->diff(Carbon::parse($applicationDate))->format('%a');
            if($mLoan->emi_option == 1 || $mLoan->emi_option == 2) //Monthly
            {
                $roi = $roi * $gapDayes;
                $principal_amount =$deposit - $roi;
                if(!array_key_exists($applicationCurrentDate.'_'.$applicationCurrentDateMonth.'_'.$applicationCurrentDateYear,$nextEmiDates)){
                    $outstandingAmount = ($mLoan->amount - $deposit );
                }else{
                    $outstandingAmount = ($mLoan->amount -$deposit + $roi );
                }
               $dailyoutstandingAmount = $outstandingAmount + $roi;
            }else{
            $principal_amount = $deposit- $roi;
            $outstandingAmount = ($mLoan->amount - $principal_amount );
            }
            $deposit =  $request['deposite_amount'];
            $dailyoutstandingAmount = $mLoan->amount + $roi;
        }
            $amountArraySsb = array(
                '1' => $request['deposite_amount']
            );
            if(isset(($ssbAccountDetails['ssbMember'])))
            {
                $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name . ' ' . $ssbAccountDetails['ssbMember']->last_name;
            }
            else{
                $amount_deposit_by_name = NULL;
            }
            $dueAmount = $mLoan->due_amount - round($principal_amount);
            $mlResult = Memberloans::find($request['loan_id']);
            $lData['credit_amount'] = $mLoan->credit_amount + round($principal_amount);
            $lData['due_amount'] = $dueAmount;
            if ($dueAmount == 0)
            {
                //$lData['status'] = 3;
                //$lData['clear_date'] = date("Y-m-d", strtotime(convertDate($request['application_date'])));
            }
            $lData['received_emi_amount'] = $mLoan->received_emi_amount + $request['deposite_amount'];
            $mlResult->update($lData);
            if ($request['loan_emi_payment_mode'] == 0)
            { //ssb Account emi Payment
                $cheque_dd_no = NULL;
                $online_payment_id = NULL;
                $online_payment_by = NULL;
                $bank_name = NULL;
                $cheque_date = NULL;
                $account_number = NULL;
                $paymentMode = 4;
                $ssbpaymentMode = 3;
                $paymentDate = date("Y-m-d", strtotime(convertDate($request['application_date'])));
                // $record1 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                //     ->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($request['application_date']))))->orderby('id', 'desc')
                //     ->first();
                $transDate = date("Y-m-d ".$entryTime."",strtotime(convertDate($request['application_date'])));
                $record1 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                    ->whereDate('created_at', '<=',  $transDate)->orderby('id', 'desc')
                    ->first();
                $ssb['saving_account_id'] = $ssbAccountDetails->id;
                $ssb['account_no'] = $ssbAccountDetails->account_no;
                $ssb['opening_balance'] = $record1->opening_balance - $request['deposite_amount'];
                $ssb['branch_id'] = $branchId;
                $ssb['type'] = 9;
                $ssb['deposit'] = 0;
                $ssb['withdrawal'] = $request['deposite_amount'];
                $ssb['description'] = 'Loan EMI Payment'.$mLoan->account_number;
                $ssb['currency_code'] = 'INR';
                $ssb['payment_type'] = 'DR';
                $ssb['payment_mode'] = $ssbpaymentMode;
                $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $ssbAccountTran = SavingAccountTranscation::create($ssb);
                $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;
                // update saving account current balance
                $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                $ssbBalance->balance = $request['ssb_account'] - $request['deposite_amount'];
                $ssbBalance->save();
                $record2 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                    ->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($request['application_date']))))->get();
                foreach ($record2 as $key => $value)
                {
                    $nsResult = SavingAccountTranscation::find($value->id);
                    $nsResult['opening_balance'] = $value->opening_balance - $request['deposite_amount'];
                    $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                    $nsResult->update($nsResult);
                }
                $data['saving_account_transaction_id'] = $ssb_transaction_id;
                $data['loan_id'] = $request['loan_id'];
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $satRef = TransactionReferences::create($data);
                $satRefId = $satRef->id;
                $updateSsbDayBook = $this->updateSsbDayBookAmount($request['deposite_amount'], $request['ssb_account_number'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))));
                $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->member_id, $branchId, getBranchCode($branchId)->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $ssbAccountDetails->account_no, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, $ssbAccountDetails->id, 'DR');
                $totalbalance = $request['ssb_account'] - $request['deposite_amount'];
                $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran, $satRefId, 1, $ssbAccountDetails->member_investments_id, 0, $ssbAccountDetails->member_id, $totalbalance, 0, $request['deposite_amount'], 'Withdrawal from SSB', $request['account_number'], $branchId, getBranchCode($branchId)->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $ssbAccountDetails->account_no, $cheque_dd_no, NULL, NULL, $paymentDate, $online_payment_by, $online_payment_by, $ssbAccountDetails->id, 'DR');
            }
            elseif ($request['loan_emi_payment_mode'] == 1)
            { //bank emi payment
                if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '')
                { // emi payment by cheque
                    $cheque_dd_no = $request['customer_cheque'];
                    $paymentMode = 1;
                    $ssbpaymentMode = 5;
                    $online_payment_id = NULL;
                    $online_payment_by = NULL;
                    $satRefId = NULL;
                    $bank_name = NULL;
                    $cheque_date = NULL;
                    $account_number = NULL;
                    $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                }
                elseif ($request['bank_transfer_mode'] == 1)
                { // emi payment by online
                    $cheque_dd_no = NULL;
                    $paymentMode = 3;
                    $ssbpaymentMode = 5;
                    $online_payment_id = $request['utr_transaction_number'];
                    $online_payment_by = NULL;
                    $satRefId = NULL;
                    $bank_name = NULL;
                    $cheque_date = NULL;
                    $account_number = NULL;
                    $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                }
                $ssbAccountTran = '';
                $ssb_account_id_from = '';
            }
            elseif ($request['loan_emi_payment_mode'] == 2)
            { // emi payment by cash
                $cheque_dd_no = NULL;
                $paymentMode = 0;
                $ssbpaymentMode = 0;
                $online_payment_id = NULL;
                $online_payment_by = NULL;
                $satRefId = NULL;
                $bank_name = NULL;
                $cheque_date = NULL;
                $account_number = NULL;
                $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $ssbAccountTran = '';
                $ssb_account_id_from = '';
            }
            elseif ($request['loan_emi_payment_mode'] == 3)
            {
                $cheque_dd_no = $request['cheque_number'];
                $cheque_date = $request['cheque_date'];
                $bank_name = $request['bank_name'];
                $account_number = $request['account_number'];
                $paymentMode = 1;
                $ssbpaymentMode = 1;
                $online_payment_id = NULL;
                $online_payment_by = NULL;
                $satRefId = NULL;
                $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $ssbAccountTran = '';
                $ssb_account_id_from = '';
            }
            $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 5, $request['loan_id'], $mLoan->applicant_id, $branchId, getBranchCode($branchId)->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $mLoan->account_number, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, NULL, 'CR');
            $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran, $satRefId, 5, $request['loan_id'], 0, $mLoan->applicant_id, $dueAmount, $request['deposite_amount'], 0, 'Loan EMI deposit', $mLoan->account_number, $branchId, getBranchCode($branchId)->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $mLoan->account_number, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_by, $online_payment_by, NULL, 'CR');
            if ($request['loan_emi_payment_mode'] == 3)
            {
                $checkData['type'] = 4;
                $checkData['branch_id'] = $branchId;
                // $checkData['loan_id']=$request['loan_id'];
                $checkData['day_book_id'] = $createDayBook;
                $checkData['cheque_id'] = $cheque_dd_no;
                $checkData['status'] = 1;
                $checkData['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $ssbAccountTran = ReceivedChequePayment::create($checkData);
                $dataRC['status'] = 3;
                $receivedcheque = ReceivedCheque::find($cheque_dd_no);
                $receivedcheque->update($dataRC);
            }
            /*********** Head Implement start************/
            if ($request['loan_emi_payment_mode'] == 0)
            { //ssb account emi payment
                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $v_no = "";
                for ($i = 0;$i < 10;$i++)
                {
                    $v_no .= $chars[mt_rand(0, strlen($chars) - 1) ];
                }
                $roidayBookRef = CommanTransactionsController::createBranchDayBookReference($roi + $principal_amount, date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))));
                $roibranchDayBook = CommanTransactionsController::branchDayBookNew($roidayBookRef, $branchId, 5, 52, $loanId, $ssb_transaction_id, $request['associate_member_id'], $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $roi + $principal_amount, $roi + $principal_amount, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'SSB A/C Cr ' . ($roi + $principal_amount) . '', 'SSB A/C Cr ' . ($roi + $principal_amount) . '', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $ssbAccountDetails->id, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], date("H:i:s") , 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                /*$roibranchDayBook = $this->createBranchDayBook($roidayBookRef,$branchId,5,52,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi+$principal_amount,$roi+$principal_amount,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','SSB A/C Cr '.($roi+$principal_amount).'','SSB A/C Cr '.($roi+$principal_amount).'','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$is_contra=NULL,$contra_id=NULL,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 31, 5, 523, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 56, 5, 52, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                /*$roiallTransaction = $this->createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,52,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);
                $roiallTransaction = $this->createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,52,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
                if ($mLoan->loan_type == '1')
                {
                    $loan_head_id = 64;
                }
                elseif ($mLoan->loan_type == '2')
                {
                    $loan_head_id = 65;
                }
                elseif ($mLoan->loan_type == '3')
                {
                    $loan_head_id = 66;
                }
                elseif ($mLoan->loan_type == '4')
                {
                    $loan_head_id = 67;
                }
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, $loan_head_id, 5, 52, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                /*$principalallTransaction = $this->createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,52,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 56, 5, 52, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                /*$principalallTransaction = $this->createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,52,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
                $principalmemberTransaction = CommanTransactionsController::memberTransactionNew($roidayBookRef, 5, 52, $loanId, $ssb_transaction_id, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $ssbAccountDetails->id, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], date("H:i:s") , 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                /*$principalmemberTransaction = $this->createMemberTransaction($roidayBookRef,5,52,$loanId,$ssb_transaction_id,$request['associate_member_id'],$mLoan->applicant_id,$branchId,$bank_id=NULL,$account_id=NULL,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
            }
            elseif ($request['loan_emi_payment_mode'] == 2)
            { //cash emi payment
                $roidayBookRef = CommanTransactionsController::createBranchDayBookReference($roi + $principal_amount, date("Y-m-d H:i:s", strtotime(convertDate($request['application_date']))));
                $roibranchDayBook = CommanTransactionsController::branchDayBookNew($roidayBookRef, $branchId, 5, 52, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $roi + $principal_amount, $roi + $principal_amount, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'Cash A/C Dr ' . ($roi + $principal_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($roi + $principal_amount) . '', 'CR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 31, 5, 523, $loanId, $createDayBook, $request['associate_member_id'], NULL, $branchId, NULL, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'CR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                /*$roiallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 28, 5, 52, $loanId, $createDayBook, $request['associate_member_id'], NULL, $branchId, NULL, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'DR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                /*$roiallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
                if ($mLoan->loan_type == '1')
                {
                    $loan_head_id = 64;
                }
                elseif ($mLoan->loan_type == '2')
                {
                    $loan_head_id = 65;
                }
                elseif ($mLoan->loan_type == '3')
                {
                    $loan_head_id = 66;
                }
                elseif ($mLoan->loan_type == '4')
                {
                    $loan_head_id = 67;
                }
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, $loan_head_id, 5, 52, $loanId, $createDayBook, $request['associate_member_id'], NULL, $branchId, NULL, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'CR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                /*$principalallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 28, 5, 52, $loanId, $createDayBook, $request['associate_member_id'], NULL, $branchId, NULL, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'DR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                /*$principalallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
                $principalmemberTransaction = CommanTransactionsController::memberTransactionNew($roidayBookRef, 5, 52, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'DR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                $createRoiBranchCash = $this->updateBranchCashFromBackDate($roi + $principal_amount, $branchId, date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))));
            }
            elseif ($request['loan_emi_payment_mode'] == 1)
            { //bank payment
                if ($mLoan->loan_type == '1')
                {
                    $loan_head_id = 64;
                }
                elseif ($mLoan->loan_type == '2')
                {
                    $loan_head_id = 65;
                }
                elseif ($mLoan->loan_type == '3')
                {
                    $loan_head_id = 66;
                }
                elseif ($mLoan->loan_type == '4')
                {
                    $loan_head_id = 67;
                }
                if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '')
                { //cheque payment
                    $payment_type = 1;
                    $cheque_type = 1;
                    $amount_from_id = $request['associate_member_id'];
                    $amount_from_name = getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name;
                    $cheque_no = $request['customer_cheque'];
                    $cheque_date = NULL;
                    $cheque_bank_from = $request['customer_bank_name'];
                    $cheque_bank_ac_from = $request['customer_bank_account_number'];
                    $cheque_bank_ifsc_from = $request['customer_ifsc_code'];
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = $request['company_bank'];
                    $cheque_bank_ac_to = $request['bank_account_number'];
                    $v_no = NULL;
                    $v_date = NULL;
                    $ssb_account_id_from = NULL;
                    $transction_no = NULL;
                    $transction_bank_from = NULL;
                    $transction_bank_ac_from = NULL;
                    $transction_bank_ifsc_from = NULL;
                    $transction_bank_branch_from = NULL;
                    $company_name = $request->cheque_company_bank;;
                    $bankId = getSamraddhBankAccount($request['company_bank_account_number'])->id;
                    $head_id = getSamraddhBankAccount($request['company_bank_account_number'])->account_head_id;
                    $cId = \App\Models\SamraddhBank::where('account_head_id', $head_id)->first();
                    $company_bankId = $cId->id;
                    $transction_bank_ac_to = $bankId;
                    $transction_bank_to = $company_bankId;
                    $ifsc = getSamraddhBankAccount($request['company_bank_account_number'])->ifsc_code;
                }
                elseif ($request['bank_transfer_mode'] == 1)
                { //bank online payment
                    $payment_type = 2;
                    $cheque_type = NULL;
                    $amount_from_id = $request['associate_member_id'];
                    $amount_from_name = getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name;
                    $cheque_no = NULL;
                    $cheque_date = NULL;
                    $cheque_bank_from = NULL;
                    $cheque_bank_ac_from = NULL;
                    $cheque_bank_ifsc_from = NULL;
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = NULL;
                    $cheque_bank_ac_to = NULL;
                    $transction_no = $request['utr_transaction_number'];
                    $v_no = NULL;
                    $v_date = NULL;
                    $ssb_account_id_from = NULL;
                    $transction_bank_from = $request['customer_bank_name'];
                    $transction_bank_ac_from = $request['customer_bank_account_number'];
                    $transction_bank_ifsc_from = $request['customer_ifsc_code'];
                    $transction_bank_branch_from = $request['customer_branch_name'];
                    $transction_bank_to = $request['company_bank'];
                    $transction_bank_ac_to = $request['bank_account_number'];
                    $company_name = getSamraddhBank($request['company_bank'])->bank_name;
                    $ifsc = getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code;
                    $bankId = getSamraddhBankAccount($request['bank_account_number'])->id;
                    $head_id = getSamraddhBankAccount($request['bank_account_number'])->account_head_id;
                    $company_bankId = getSamraddhBank($request['company_bank'])->id;
                }
                $roidayBookRef = CommanTransactionsController::createBranchDayBookReference($roi + $principal_amount, date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))));
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 31, 5, 523, $loanId, $createDayBook, $request['associate_member_id'], NULL, NULL, NULL, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'CR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, NULL, NULL, NULL, $cheque_type, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, NULL, NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_from, $branchId, $transction_bank_ac_to, $ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, NULL, NULL, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, NULL, 1, Auth::user()->id);;
                /*$roiallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, $head_id, 5, 52, $loanId, $createDayBook, $request['associate_member_id'], NULL, NULL, NULL, $request['penalty_amount'] + $roi, $request['penalty_amount'] + $roi + $principal_amount, $request['penalty_amount'] + $roi, '' . $mLoan->account_number . ' EMI collection', 'Dr', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, NULL, NULL, NULL, $cheque_type, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, NULL, NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_from, $branchId, $transction_bank_ac_to, $ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, NULL, NULL, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, NULL, 1, Auth::user()->id);
                /*$allTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,$head5=NULL,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, $loan_head_id, 5, 52, $loanId, $createDayBook, $request['associate_member_id'], NULL, NULL, NULL, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'CR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, NULL, NULL, NULL, $cheque_type, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, NULL, NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_from, $branchId, $transction_bank_ac_to, $ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, NULL, NULL, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, NULL, 1, Auth::user()->id);
                /*$principalallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
                /*  $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,5,52,$loanId,$createDayBook,$request['associate_member_id'],NULL,NULL,NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,NULL,NULL,NULL,$cheque_type,NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,NULL,NULL,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_from,$branchId,$transction_bank_ac_to,getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,NULL,NULL,$transction_bank_to,$transction_bank_ac_to,getSamraddhBank($request['company_bank'])->bank_name,$transction_bank_ac_to,NULL,getSamraddhBankAccount($request['bank_account_number'])->ifsc_code,NULL,1,Auth::user()->id);*/
                /*$allTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,$head5=NULL,5,52,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
                $roibranchDayBook = CommanTransactionsController::branchDayBookNew($roidayBookRef, $branchId, 5, 52, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $roi + $principal_amount, $roi + $principal_amount, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'Online A/C Dr ' . ($roi + $principal_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($roi + $principal_amount) . '', 'CR', $payment_type, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no, $cheque_date, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                $principalmemberTransaction = CommanTransactionsController::memberTransactionNew($roidayBookRef, 5, 52, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'DR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date = NULL, $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                $samraddhBankDaybook = CommanTransactionsController::samraddhBankDaybookNew($roidayBookRef, $bank_id = $company_bankId, $account_id = $company_bankId, 5, 52, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $roi + $principal_amount, $roi + $principal_amount, $roi + $principal_amount, 'EMI collection', 'Online A/C Cr. ' . ($roi + $principal_amount) . '', 'Online A/C Cr. ' . ($roi + $principal_amount) . '', 'CR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, $request['application_date'], $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
                $createPrincipleBankClosing = CommanTransactionsController::updateBackDateloanBankBalance($roi + $principal_amount, $company_bankId, $bankId, date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))) , 0, 0);
                $dataRC['status'] = 3;
                $receivedcheque = \App\Models\ReceivedCheque::find($request['customer_cheque']);
                $receivedcheque->update($dataRC);
            }
            /*********** Head Implement end ************/
            /*---------- commission script  start  ---------*/
            $daybookId = $createDayBook;
            $loan_associate_code = $request->loan_associate_code;
            $total_amount = $request['deposite_amount'];
            $percentage = 2;
            $month = NULL;
            $type_id = $request['loan_id'];
            $type = 4;
            $associate_id = $request['associate_member_id'];
            $branch_id = $branchId;
            $commission_type = 0;
            $associateDetail = Member::where('id', $associate_id)->first();
            $carder = $associateDetail->current_carder_id;
            $associate_exist = 0;
            $percentInDecimal = $percentage / 100;
            $commission_amount = round($percentInDecimal * $total_amount, 4);
            $associateCommission['member_id'] = $associate_id;
            $associateCommission['branch_id'] = $branch_id;
            $associateCommission['type'] = $type;
            $associateCommission['type_id'] = $type_id;
            $associateCommission['day_book_id'] = $daybookId;
            $associateCommission['total_amount'] = $total_amount;
            $associateCommission['month'] = $month;
            $associateCommission['commission_amount'] = $commission_amount;
            $associateCommission['percentage'] = $percentage;
            $associateCommission['commission_type'] = $commission_type;
            $date = \App\Models\Daybook::where('id', $daybookId)->first();
            $associateCommission['created_at'] = $date->created_at;
            $associateCommission['pay_type'] = 4;
            $associateCommission['carder_id'] = $carder;
            $associateCommission['associate_exist'] = $associate_exist;
            if($loan_associate_code != 9999999)
            {
            $associateCommissionInsert = \App\Models\CommissionEntryLoan::create($associateCommission);
            }
            // Collection start
            /*$collection_percentage=5;
            $collection_type=6;
            $Collection_associate_id=$mLoan->associate_member_id;
            $collection_associateDetail=Member::where('id',$Collection_associate_id)->first();
            $Collection_carder=$collection_associateDetail->current_carder_id;
            $collection_percentInDecimal = $collection_percentage / 100;
            $collection_commission_amount = round($collection_percentInDecimal * $total_amount,4);
            $coll_associateCommission['member_id'] = $Collection_associate_id;
            $coll_associateCommission['branch_id'] = $branch_id;
            $coll_associateCommission['type'] = $collection_type;
            $coll_associateCommission['type_id'] = $type_id;
            $coll_associateCommission['day_book_id'] = $daybookId;
            $coll_associateCommission['total_amount'] = $total_amount;
            $coll_associateCommission['month'] = $month;
            $coll_associateCommission['commission_amount'] = $collection_commission_amount;
            $coll_associateCommission['percentage'] = $collection_percentage;
            $coll_associateCommission['commission_type'] = $commission_type;
            $coll_date =\App\Models\Daybook::where('id',$daybookId)->first();
            $coll_associateCommission['created_at'] = $coll_date->created_at;
            $coll_associateCommission['pay_type'] = 4;
            $coll_associateCommission['carder_id'] = $Collection_carder;
            $coll_associateCommission['associate_exist'] = $associate_exist;
            $coll_associateCommissionInsert = \App\Models\CommissionEntryLoan::create($coll_associateCommission);*/
            /*---------- commission script  end  ---------*/
            $createLoanDayBook = CommanTransactionsController::createLoanDayBook($roidayBookRef, $daybookId, $mLoan->loan_type, 0, $mLoan->id, $lId = NULL, $mLoan->account_number, $mLoan->applicant_id, $roi, $principal_amount, $dueAmount, $request['deposite_amount'], 'Loan EMI deposit', $branchId, getBranchCode($branchId)->branch_code, 'CR', 'INR', $paymentMode, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, 1, 1, $cheque_date, $account_number, NULL, $request['loan_associate_name'], $request['associate_member_id'], $branchId,$totalDailyInterest,$totalDayInterest,$penalty);
            $totalDepsoit = LoanDaybooks::where('account_number',$mLoan->account_number)->where('loan_sub_type',0)->where('is_deleted',0)->sum('deposit');
            $text = 'Dear Member,Received Rs.'.  $request['deposite_amount']. ' as EMI of Loan A/C '. $mLoan->account_number.' on '. date('d/m/Y',strtotime(convertDate($request['application_date']))).' Total recovery Rs. '.$totalDepsoit.' Thank You. Samraddh Bestwin Microfinance';;
            $temaplteId = 1207166308935249821;
            $contactNumber = array();
            $memberDetail = Member::find($mLoan->applicant_id);
            $contactNumber[] = $memberDetail->mobile_no;
            $sendToMember = new Sms();
            $sendToMember->sendSms( $contactNumber, $text, $temaplteId);
            if ($request['penalty_amount'] != '' && $request['penalty_amount'] > 0)
            {
                $amountArray = array(
                    '1' => $request['penalty_amount']
                );
                if ($request['loan_emi_payment_mode'] == 0)
                {
                    $cheque_dd_no = NULL;
                    $online_payment_id = NULL;
                    $online_payment_by = NULL;
                    $bank_name = NULL;
                    $cheque_date = NULL;
                    $account_number = NULL;
                    $paymentMode = 4;
                    $ssbpaymentMode = 3;
                    $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                    $ssb['saving_account_id'] = $ssbAccountDetails->id;
                    $ssb['account_no'] = $ssbAccountDetails->account_no;
                    $ssb['opening_balance'] = $request['ssb_account'] - $request['penalty_amount'];
                    $ssb['deposit'] = 0;
                    $ssb['withdrawal'] = $request['penalty_amount'];
                    $ssb['description'] = 'Loan EMI Penalty';
                    $ssb['currency_code'] = 'INR';
                    $ssb['payment_type'] = 'DR';
                    $ssb['payment_mode'] = $ssbpaymentMode;
                    $ssb['created_at'] = $request['created_at'];
                    $ssbAccountTran = SavingAccountTranscation::create($ssb);
                    $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;
                    // update saving account current balance
                    $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                    $ssbBalance->balance = $request['ssb_account'] - $request['penalty_amount'];
                    $ssbBalance->save();
                    $data['saving_account_transaction_id'] = $ssb_transaction_id;
                    $data['loan_id'] = $request['loan_id'];
                    $data['created_at'] = $request['created_at'];
                    $satRef = TransactionReferences::create($data);
                    $satRefId = $satRef->id;
                    $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->member_id, $branchId, getBranchCode($branchId)->branch_code, $amountArray, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $ssbAccountDetails->account_no, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, $ssbAccountDetails->id, 'DR');
                    $totalbalance = $request['ssb_account'] - $request['penalty_amount'];
                    $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran, $satRefId, 1, $ssbAccountDetails->member_investments_id, 0, $ssbAccountDetails->member_id, $totalbalance, 0, $request['penalty_amount'], 'Withdrawal from SSB', $request['account_number'], $branchId, getBranchCode($branchId)->branch_code, $amountArray, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $ssbAccountDetails->account_no, $cheque_dd_no, NULL, NULL, $paymentDate, $online_payment_by, $online_payment_by, $ssbAccountDetails->id, 'DR');
                }
                elseif ($request['loan_emi_payment_mode'] == 1)
                {
                    if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '')
                    {
                        $paymentMode = 1;
                        $cheque_dd_no = $request['customer_cheque'];
                        $ssbpaymentMode = 5;
                        $online_payment_id = NULL;
                        $online_payment_by = NULL;
                        $satRefId = NULL;
                        $bank_name = NULL;
                        $cheque_date = NULL;
                        $account_number = NULL;
                        $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));;
                    }
                    elseif ($request['bank_transfer_mode'] == 1)
                    {
                        $paymentMode = 3;
                        $cheque_dd_no = NULL;
                        $ssbpaymentMode = 5;
                        $online_payment_id = $request['utr_transaction_number'];
                        $online_payment_by = NULL;
                        $satRefId = NULL;
                        $bank_name = NULL;
                        $cheque_date = NULL;
                        $account_number = NULL;
                        $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));;
                    }
                    $ssb_transaction_id = '';
                    $ssb_account_id_from = '';
                }
                elseif ($request['loan_emi_payment_mode'] == 2)
                {
                    $cheque_dd_no = NULL;
                    $paymentMode = 0;
                    $ssbpaymentMode = 0;
                    $online_payment_id = NULL;
                    $online_payment_by = NULL;
                    $satRefId = NULL;
                    $bank_name = NULL;
                    $cheque_date = NULL;
                    $account_number = NULL;
                    $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                    $ssb_transaction_id = '';
                    $ssb_account_id_from = '';
                }
                $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 11, $request['loan_id'], $mLoan->applicant_id, $branchId, getBranchCode($branchId)->branch_code, $amountArray, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $mLoan->account_number, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, NULL, 'CR');
                $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran, $satRefId, 11, $request['loan_id'], 0, $mLoan->applicant_id, $dueAmount, $request['penalty_amount'], 0, 'Loan EMI penalty', $mLoan->account_number, $branchId, getBranchCode($branchId)->branch_code, $amountArray, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $mLoan->account_number, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, NULL, 'CR');
                if ($request['loan_emi_payment_mode'] == 3)
                {
                    $checkData['type'] = 4;
                    $checkData['branch_id'] = $branchId;
                    // $checkData['loan_id']=$request['loan_id'];
                    $checkData['day_book_id'] = $createDayBook;
                    $checkData['cheque_id'] = $cheque_dd_no;
                    $checkData['status'] = 1;
                    $checkData['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                    $ssbAccountTran = ReceivedChequePayment::create($checkData);
                    $dataRC['status'] = 3;
                    $receivedcheque = ReceivedCheque::find($cheque_dd_no);
                    $receivedcheque->update($dataRC);
                }
                /************* Head Implement ****************/
                if ($request['loan_emi_payment_mode'] == 0)
                {
                    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                    $v_no = "";
                    for ($i = 0;$i < 10;$i++)
                    {
                        $v_no .= $chars[mt_rand(0, strlen($chars) - 1) ];
                    }
                    $penaltyDayBookRef = CommanTransactionsController::createBranchDayBookReference($request['penalty_amount'], $request['application_date']);
                    $roibranchDayBook = CommanTransactionsController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 53, $loanId, $ssb_transaction_id, $request['associate_member_id'], $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'SSB A/C Cr ' . $request['penalty_amount'] . '', 'SSB A/C Cr ' . $request['penalty_amount'] . '', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                    $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $branchId, NULL, NULL, 33, 5, 53, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, NULL, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                    /*$roiallTransaction = $this->createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,53,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
                    $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $branchId, NULL, NULL, 56, 5, 53, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, NULL, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                    /*$roiallTransaction = $this->createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,53,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
                    $roimemberTransaction = CommanTransactionsController::memberTransactionNew($penaltyDayBookRef, 5, 53, $loanId, $ssb_transaction_id, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $request['penalty_amount'], 'Loan Panelty Charge', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], date("H:i:s") , 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                    /*$roimemberTransaction = $this->createMemberTransaction($penaltyDayBookRef,5,53,$loanId,$ssb_transaction_id,$request['associate_member_id'],$mLoan->applicant_id,$branchId,$bank_id=NULL,$account_id=NULL,$request['penalty_amount'],'Loan Panelty Charge','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id); */
                    if($gstAmount)
                    {
                        if($IntraState)
                        {
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 533, $loanId, $ssb_transaction_id, $request['associate_member_id'], $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty CGST Charge', 'SSB A/C Cr ' . $gstAmount . '', 'SSB A/C Cr ' . $gstAmount . '', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 534, $loanId, $ssb_transaction_id, $request['associate_member_id'], $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty SGST Charge', 'SSB A/C Cr ' . $gstAmount . '', 'SSB A/C Cr ' . $gstAmount . '', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $cgstHead, 5, 533, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty  CGST Charge', 'CR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $sgstHead, 5, 534, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty  SGST Charge', 'CR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 56, 5, 533, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty CGST Charge', 'DR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 56, 5, 534, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty SGST Charge', 'DR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 533, $loanId, $ssb_transaction_id, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Loan Panelty CGST Charge', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], date("H:i:s") , 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 534, $loanId, $ssb_transaction_id, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Loan Panelty SGST Charge', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], date("H:i:s") , 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                        }
                        else{
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 535, $loanId, $ssb_transaction_id, $request['associate_member_id'], $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty IGST Charge', 'SSB A/C Cr ' . $gstAmount . '', 'SSB A/C Cr ' . $gstAmount . '', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $cgstHead, 5, 535, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty  IGST Charge', 'CR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 56, 5, 535, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty IGST Charge', 'DR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 56, 5, 535, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty SGST Charge', 'DR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 535, $loanId, $ssb_transaction_id, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Loan Panelty IGST Charge', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], date("H:i:s") , 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                        }
                        $createdGstTransaction = CommanController::gstTransaction($dayBookId = $penaltyDayBookRef,$getGstSettingno->gst_no,(!isset($mLoan['loanMember']->gst_no)) ? NULL : $mLoan['loanMember']->gst_no,$request['penalty_amount'],$getHeadSetting->gst_percentage,($IntraState == false ? $gstAmount : 0 ) ,($IntraState == true ? $gstAmount : 0),($IntraState == true ? $gstAmount : 0),($IntraState == true) ? $request['penalty_amount'] + $gstAmount + $gstAmount : $request['penalty_amount'] + $gstAmount,33,$request['date'],'LP33',$mLoan['loanMember']->id,$mLoan->branch_id);
                    }
                }
                elseif ($request['loan_emi_payment_mode'] == 2)
                {
                    $penaltyDayBookRef = CommanTransactionsController::createBranchDayBookReference($request['penalty_amount'], $request['application_date']);
                    $roibranchDayBook = CommanTransactionsController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 53, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'Cash A/C Dr ' . $request['penalty_amount'] . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . $request['penalty_amount'] . '', 'CR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                    $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $branchId, NULL, NULL, 33, 5, 53, $loanId, $createDayBook, $request['associate_member_id'], NULL, $branchId, NULL, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'CR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                    /*$roiallTransaction = CommanTransactionsController::createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,53,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
                    $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $branchId, NULL, NULL, 28, 5, 53, $loanId, $createDayBook, $request['associate_member_id'], NULL, $branchId, NULL, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'DR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                    /*$roiallTransaction = CommanTransactionsController::createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,53,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
                    $roimemberTransaction = CommanTransactionsController::memberTransactionNew($penaltyDayBookRef, 5, 53, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $request['penalty_amount'], 'Loan Panelty Charge', 'DR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                    if($gstAmount > 0)
                    {
                        if($IntraState)
                        {
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 533, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty CGST Charge', 'Cash A/C Dr ' . $gstAmount . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . $gstAmount . '', 'CR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 534, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty SGST Charge', 'Cash A/C Dr ' . $gstAmount . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . $gstAmount . '', 'CR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $cgstHead, 5, 533, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty CGST Charge', 'CR', 0, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 28, 5, 534, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty CGST Charge', 'DR', 0, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $sgstHead, 5, 533, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty SGST Charge', 'CR', 0, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 28, 5, 534, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty SGST Charge', 'DR', 0, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 533, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Loan Panelty CGST Charge', 'DR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 534, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Loan Panelty SGST Charge', 'DR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                        }
                        else{
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 535, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty IGST Charge', 'Cash A/C Dr ' . $gstAmount . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . $gstAmount . '', 'CR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $cgstHead, 5, 535, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty IGST Charge', 'CR', 0, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 28, 5, 535, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty IGST Charge', 'DR', 0, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 535, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Loan Panelty IGST Charge', 'DR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                        }
                        $createdGstTransaction = CommanController::gstTransaction($dayBookId = $penaltyDayBookRef,$getGstSettingno->gst_no,(!isset($mLoan['loanMember']->gst_no)) ? NULL : $mLoan['loanMember']->gst_no,$request['penalty_amount'],$getHeadSetting->gst_percentage,($IntraState == false ? $gstAmount : 0 ) ,($IntraState == true ? $gstAmount : 0),($IntraState == true ? $gstAmount : 0),($IntraState == true) ? $request['penalty_amount'] + $gstAmount + $gstAmount : $request['penalty_amount'] + $gstAmount,33,$request['date'],'LP33',$mLoan['loanMember']->id,$request['branch']);
                    }
                    $createRoiBranchCash = $this->updateBranchCashFromBackDate($request['penalty_amount'] + $gstAmount, $branchId, date("Y-m-d  " . $entryTime . "", strtotime(convertDate($request['application_date']))));
                }
                elseif ($request['loan_emi_payment_mode'] == 1)
                {
                    if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '')
                    {
                        $payment_type = 1;
                        $cheque_type = 1;
                        $amount_from_id = $request['associate_member_id'];
                        $amount_from_name = getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name;
                        $cheque_no = $request['customer_cheque'];
                        $cheque_date = NULL;
                        $cheque_bank_from = $request['customer_bank_name'];
                        $cheque_bank_ac_from = $request['customer_bank_account_number'];
                        $cheque_bank_ifsc_from = $request['customer_ifsc_code'];
                        $cheque_bank_branch_from = NULL;
                        $cheque_bank_to = $request['company_bank'];
                        $cheque_bank_ac_to = $request['bank_account_number'];
                        $v_no = NULL;
                        $v_date = NULL;
                        $ssb_account_id_from = NULL;
                        $transction_no = NULL;
                        $transction_bank_from = NULL;
                        $transction_bank_ac_from = NULL;
                        $transction_bank_ifsc_from = NULL;
                        $transction_bank_branch_from = NULL;
                        $transction_bank_to = NULL;
                        $transction_bank_ac_to = NULL;
                    }
                    elseif ($request['bank_transfer_mode'] == 1)
                    {
                        $payment_type = 2;
                        $cheque_type = NULL;
                        $amount_from_id = $request['associate_member_id'];
                        $amount_from_name = getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name;
                        $cheque_no = NULL;
                        $cheque_date = NULL;
                        $cheque_bank_from = NULL;
                        $cheque_bank_ac_from = NULL;
                        $cheque_bank_ifsc_from = NULL;
                        $cheque_bank_branch_from = NULL;
                        $cheque_bank_to = NULL;
                        $cheque_bank_ac_to = NULL;
                        $transction_no = $request['utr_transaction_number'];
                        $v_no = NULL;
                        $v_date = NULL;
                        $ssb_account_id_from = NULL;
                        $transction_bank_from = $request['customer_bank_name'];
                        $transction_bank_ac_from = $request['customer_bank_account_number'];
                        $transction_bank_ifsc_from = $request['customer_ifsc_code'];
                        $transction_bank_branch_from = $request['customer_branch_name'];
                        $transction_bank_to = $request['company_bank'];
                        $transction_bank_ac_to = $request['bank_account_number'];
                    }
                    $penaltyDayBookRef = CommanTransactionsController::createBranchDayBookReference($request['penalty_amount'], date("Y-m-d  " . $entryTime . "", strtotime(convertDate($request['application_date']))));
                    $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $branchId, NULL, NULL, 33, 5, 53, $loanId, $createDayBook, $request['associate_member_id'], NULL, NULL, NULL, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'CR', $payment_type, 'INR', $request['company_bank'], getSamraddhBank($request['company_bank'])->bank_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, NULL, NULL, NULL, $cheque_type, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, NULL, NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_from, $branchId, $transction_bank_ac_to, getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, NULL, NULL, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($request['company_bank'])->bank_name, $transction_bank_ac_to, NULL, getSamraddhBankAccount($request['bank_account_number'])->ifsc_code, NULL, 1, Auth::user()
                        ->id);
                    /*$roiallTransaction = CommanTransactionsController::createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,53,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
                    $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $branchId, NULL, NULL, getSamraddhBankAccount($request['bank_account_number'])->account_head_id, 5, 53, $loanId, $createDayBook, $request['associate_member_id'], NULL, NULL, NULL, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'DR', $payment_type, 'INR', $request['company_bank'], getSamraddhBank($request['company_bank'])->bank_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, NULL, NULL, NULL, $cheque_type, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, NULL, NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_from, $branchId, $transction_bank_ac_to, getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, NULL, NULL, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($request['company_bank'])->bank_name, $transction_bank_ac_to, NULL, getSamraddhBankAccount($request['bank_account_number'])->ifsc_code, NULL, 1, Auth::user()
                        ->id);
                    /*$allTransaction = CommanTransactionsController::createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,$head5=NULL,5,53,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
                    $roibranchDayBook = CommanTransactionsController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 53, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'Cash A/C Dr ' . $request['penalty_amount'] . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . $request['penalty_amount'] . '', 'CR', $payment_type, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                    $principalmemberTransaction = CommanTransactionsController::memberTransactionNew($penaltyDayBookRef, 5, 53, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $request['penalty_amount'], 'Loan Panelty Charge', 'DR', $payment_type, 'INR', $request['company_bank'], getSamraddhBank($request['company_bank'])->bank_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                    $samraddhBankDaybook = CommanTransactionsController::samraddhBankDaybookNew($penaltyDayBookRef, $bank_id = getSamraddhBank($request['company_bank'])->id, $account_id = getSamraddhBank($request['company_bank'])->id, 5, 53, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'Cash A/C Cr. ' . $request['penalty_amount'] . '', 'Cash A/C Cr. ' . $request['penalty_amount'] . '', 'CR', $payment_type, 'INR', $request['company_bank'], getSamraddhBank($request['company_bank'])->bank_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL,
                    $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $jv_unique_id, $cheque_type, $cheque_id, $ssb_account_tran_id_to, $ssb_account_tran_id_from);
                    if($gstAmount > 0)
                    {
                        if($IntraState)
                        {
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 533, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty CGST Charge', 'Bank A/C Dr ' . $gstAmount . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . $gstAmount . '', 'CR', $payment_type, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 534, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty SGST Charge', 'Cash A/C Dr ' . $gstAmount . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . $gstAmount . '', 'CR', $payment_type, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $cgstHead, 5, 533, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty CGST Charge', 'CR', $payment_type, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 28, 5, 533, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty CGST Charge', 'DR', $payment_type, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $sgstHead, 5, 534, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty SGST Charge', 'CR', $payment_type, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 28, 5, 534, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty SGST Charge', 'DR', $payment_type, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 533, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Loan Panelty CGST Charge', 'DR', $payment_type, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 534, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Loan Panelty SGST Charge', 'DR', $payment_type, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($penaltyDayBookRef, $bank_id = $company_bankId, $account_id = $company_bankId, 5, 533, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty CGST Charge', 'Cash A/C Cr. ' . $gstAmount . '', 'Cash A/C Cr. ' . $gstAmount . '', 'CR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, $request['application_date'], $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
                            $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($penaltyDayBookRef, $bank_id = $company_bankId, $account_id = $company_bankId, 5, 534, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty SGST Charge', 'Cash A/C Cr. ' . $gstAmount . '', 'Cash A/C Cr. ' . $gstAmount. '', 'CR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, $request['application_date'], $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
                        }
                        else{
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 535, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty IGST Charge', 'Cash A/C Dr ' . gstAmount . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . gstAmount . '', 'CR', $payment_type, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $cgstHead, 5, 535, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty IGST Charge', 'CR', 0, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $branchId, NULL, NULL, getSamraddhBankAccount($request['bank_account_number'])->account_head_id, 5, 53, $loanId, $createDayBook, $request['associate_member_id'], NULL, NULL, NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty IGSt Charge', 'DR', $payment_type, 'INR', $request['company_bank'], getSamraddhBank($request['company_bank'])->bank_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, NULL, NULL, NULL, $cheque_type, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, NULL, NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_from, $branchId, $transction_bank_ac_to, getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, NULL, NULL, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($request['company_bank'])->bank_name, $transction_bank_ac_to, NULL, getSamraddhBankAccount($request['bank_account_number'])->ifsc_code, NULL, 1, Auth::user()->id);
                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 535, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Loan Panelty IGST Charge', 'DR', $payment_type, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($penaltyDayBookRef, $bank_id = $company_bankId, $account_id = NULL, 5, 535, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty IGST Charge', 'Cash A/C Cr. ' . $gstAmount . '', 'Cash A/C Cr. ' . $gstAmount. '', 'CR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, $request['application_date'], $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
                        }
                        $createdGstTransaction = CommanController::gstTransaction($dayBookId = $penaltyDayBookRef,$getGstSettingno->gst_no,(!isset($mLoan['loanMember']->gst_no)) ? NULL : $mLoan['loanMember']->gst_no,$request['penalty_amount'],$getHeadSetting->gst_percentage,($IntraState == false ? $gstAmount : 0 ) ,($IntraState == true ? $gstAmount : 0),($IntraState == true ? $gstAmount : 0),($IntraState == true) ? $request['penalty_amount'] + $gstAmount + $gstAmount : $request['penalty_amount'] + $gstAmount,33,$request['date'],'LP33',$mLoan['loanMember']->id,$request['branch']);
                    }
                    $createPrincipleBankClosing = CommanTransactionsController::updateBackDateloanBankBalance($request['penalty_amount'] + $gstAmount, $request['company_bank'], getSamraddhBankAccount($request['bank_account_number'])->id, date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))) , 0, 0);
                }
                /************* Head Implement ****************/
                /*---------- penalty commission script  start  ---------*/
                /*$daybookId=$createDayBook;
                $total_amount=$request['penalty_amount'];
                $month=NULL;
                $type_id=$request['loan_id'];
                $branch_id=$branchId;
                $associate_exist=0;
                $commission_type=0;
                // Collection start
                $collection_percentage=5;
                $collection_type=6;
                $Collection_associate_id=$mLoan->associate_member_id;
                $collection_associateDetail=Member::where('id',$Collection_associate_id)->first();
                $Collection_carder=$collection_associateDetail->current_carder_id;
                $collection_percentInDecimal = $collection_percentage / 100;
                $collection_commission_amount = round($collection_percentInDecimal * $total_amount,4);
                $coll_associateCommission['member_id'] = $Collection_associate_id;
                $coll_associateCommission['branch_id'] = $branch_id;
                $coll_associateCommission['type'] = $collection_type;
                $coll_associateCommission['type_id'] = $type_id;
                $coll_associateCommission['day_book_id'] = $daybookId;
                $coll_associateCommission['total_amount'] = $total_amount;
                $coll_associateCommission['month'] = $month;
                $coll_associateCommission['commission_amount'] = $collection_commission_amount;
                $coll_associateCommission['percentage'] = $collection_percentage;
                $coll_associateCommission['commission_type'] = $commission_type;
                $coll_date =\App\Models\Daybook::where('id',$daybookId)->first();
                $coll_associateCommission['created_at'] = $coll_date->created_at;
                $coll_associateCommission['pay_type'] = 4;
                $coll_associateCommission['carder_id'] = $Collection_carder;
                $coll_associateCommission['associate_exist'] = $associate_exist;
                $coll_associateCommissionInsert = \App\Models\CommissionEntryLoan::create($coll_associateCommission);*/
                /*---------- penalty commission script  end  ---------*/
                if ($request['loan_emi_payment_mode'] == 0)
                {
                    $paymentMode = 4;
                    $cheque_dd_no = NULL;
                    $ssbpaymentMode = 5;
                    $online_payment_id = NULL;
                    $online_payment_by = NULL;
                    $satRefId = NULL;
                    $bank_name = NULL;
                    $cheque_date = NULL;
                    $account_number = NULL;
                    $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));;
                }
                elseif ($request['loan_emi_payment_mode'] == 1)
                {
                    if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '')
                    {
                        $paymentMode = 1;
                        $cheque_dd_no = $request['customer_cheque'];
                        $ssbpaymentMode = 5;
                        $online_payment_id = NULL;
                        $online_payment_by = NULL;
                        $satRefId = NULL;
                        $bank_name = NULL;
                        $cheque_date = NULL;
                        $account_number = NULL;
                        $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));;
                    }
                    elseif ($request['bank_transfer_mode'] == 1)
                    {
                        $paymentMode = 2;
                        $cheque_dd_no = NULL;
                        $ssbpaymentMode = 5;
                        $online_payment_id = $request['utr_transaction_number'];
                        $online_payment_by = NULL;
                        $satRefId = NULL;
                        $bank_name = NULL;
                        $cheque_date = NULL;
                        $account_number = NULL;
                        $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));;
                    }
                }
                elseif ($request['loan_emi_payment_mode'] == 2)
                {
                    $cheque_dd_no = NULL;
                    $paymentMode = 0;
                    $ssbpaymentMode = 0;
                    $online_payment_id = NULL;
                    $online_payment_by = NULL;
                    $satRefId = NULL;
                    $bank_name = NULL;
                    $cheque_date = NULL;
                    $account_number = NULL;
                    $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));;
                }
                $createLoanDayBook = CommanTransactionsController::createLoanDayBook($penaltyDayBookRef, $createDayBook, $mLoan->loan_type, 1, $mLoan->id, $lId = NULL, $mLoan->account_number, $mLoan->applicant_id, $request['penalty_amount'], $request['penalty_amount'], $dueAmount, $request['penalty_amount'], 'Loan EMI penalty', $branchId, getBranchCode($branchId)->branch_code, 'CR', 'INR', $paymentMode, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, 1, 1, $cheque_date, $account_number, NULL, $request['loan_associate_name'], $request['associate_member_id'], $branchId,$totalDailyInterest,$totalDayInterest,$penalty,);
                if($gstAmount > 0)
                {
                    if($IntraState)
                    {
                        $updateData =$createLoanDayBook->update(['cgst_charge'=>$gstAmount,'sgst_charge'=>$gstAmount]);
                    }
                    else{
                        $updateData =$createLoanDayBook->update(['igst_charge'=>$gstAmount]);
                    }
                }
            }
            DB::commit();
        }
        catch(\Exception $ex)
        {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return back()
            ->with('success', 'Loan EMI Successfully submitted!');
    }
    /**
     * Deposite Group loan EMI.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function depositeGroupLoanEmi(Request $request)
    {
        DB::beginTransaction();
        try
        {
            if($request['penalty_amount']>0)
            {
                $penalty = $request['penalty_amount'];
            }
            else{
                $penalty = 0;
            }
            $entryTime = date("H:i:s");
            Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))));
            $branchId = branchName()->id;
            //$branchId = $branchId;
            $loanId = $request['loan_id'];
            if ($request['loan_emi_payment_mode'] == 0)
            {
                $ssbAccountDetails = SavingAccount::with('ssbMember')->where('id', $request['ssb_id'])->first();
                if ($ssbAccountDetails->balance < $request['deposite_amount'])
                {
                    return back()->with('error', 'Insufficient balance in ssb account!');
                }
            }
            $mLoan = Grouploans::with(['loanMember','loanBranch'])->where('id', $request['loan_id'])->first();
            $stateid = getBranchState($mLoan['loanBranch']->name);
            // $globaldate = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
            $globaldate = date('Y-m-d',strtotime(convertDate($request['application_date'])));
            $getHeadSetting = \App\Models\HeadSetting::where('head_id',33)->first();
            $getGstSetting = \App\Models\GstSetting::where('state_id',$mLoan['loanBranch']->state_id)->where('applicable_date','<=',$globaldate)->exists();
            $getGstSettingno = \App\Models\GstSetting::select('id','gst_no')->where('state_id',$mLoan['loanBranch']->state_id)->where('applicable_date','<=',$globaldate)->first();
            $gstAmount = 0;
                if($request['penalty_amount']>0 &&  $getGstSetting )
                {
                    if($mLoan['loanBranch']->state_id == 33)
                    {
                        $gstAmount =  (($request['penalty_amount']*$getHeadSetting->gst_percentage)/100)/2;
                        $cgstHead = 171;
                        $sgstHead = 172;
                        $IntraState = true;
                        $gstAmount = ceil($gstAmount);
                    }
                    else{
                        $gstAmount =  ($request['penalty_amount']*$getHeadSetting->gst_percentage)/100;
                        $cgstHead = 170;
                        $IntraState = false;
                        $gstAmount = ceil($gstAmount);
                    }
                    $penalty = $request['penalty_amount'];
                }
                else{
                    $penalty = 0;
                }
                $LoanCreatedDate = date('Y-m-d',strtotime($mLoan->approve_date));
                $LoanCreatedYear = date('Y',strtotime($mLoan->approve_date));
                $LoanCreatedMonth = date('m',strtotime($mLoan->approve_date));
                $LoanCreateDate = date('d',strtotime($mLoan->approve_date));
                $currentDate = date('Y-m-d');
                $CurrentDate= date('d');
                $CurrentDateYear = date('Y');
                $CurrentDateMonth = date('m');
                $applicationDate = date('Y-m-d',strtotime(convertDate($request['application_date'])));
                $applicationCurrentDate= date('d',strtotime(convertDate($request['application_date'])));
                $applicationCurrentDateYear =date('Y',strtotime(convertDate($request['application_date'])));
                $applicationCurrentDateMonth =date('m',strtotime(convertDate($request['application_date'])));
                if ($mLoan->emi_option == 1){ //Month
                    $daysDiff =(($CurrentDateYear - $LoanCreatedYear) * 12) + ($CurrentDateMonth - $LoanCreatedMonth);
                    $daysDiff2 =today()->diffInDays($LoanCreatedDate);
                    $nextEmiDates = $this->nextEmiDates($daysDiff,$LoanCreatedDate);
                    $nextEmiDates2 = $this->nextEmiDatesDays($daysDiff2,$LoanCreatedDate);
                    $nextEmiDates3 = $this->nextEmiDates($mLoan->emi_period,$LoanCreatedDate);
                }
                if ($mLoan->emi_option == 2){ //Week
                    $daysDiff =today()->diffInDays($LoanCreatedDate);
                    $daysDiff = $daysDiff/7;
                    $nextEmiDates2 = $this->nextEmiDatesDays($daysDiff,$LoanCreatedDate);
                    $nextEmiDates = $this->nextEmiDatesWeek($daysDiff,$LoanCreatedDate);
                }
                if ($mLoan->emi_option == 3){  //Days
                    $daysDiff =today()->diffInDays($LoanCreatedDate);
                    $nextEmiDates = $this->nextEmiDatesDays($daysDiff,$LoanCreatedDate);
                }
                // $currentEmiDate = $nextEmiDates[$CurrentDate.'_'.$CurrentDateMonth.'_'.$CurrentDateYear];
                $totalDayInterest =0;
                $totalDailyInterest = 0;
                $newApplicationDate = explode('-',$applicationDate);
                $StartnewDAte = $applicationCurrentDateYear.'-'. $applicationCurrentDateMonth.'-01';
                $EndnewDAte = $applicationCurrentDateYear.'-'. $applicationCurrentDateMonth.'-31';
                $dailyoutstandingAmount = 0;
                $lastOutstanding = LoanEmisNew::where('loan_id',$mLoan->id)->where('loan_type',$mLoan->loan_type)->orderBy('id','desc')->first();
                $lastOutstandingDate = LoanEmisNew::where('loan_id',$mLoan->id)->where('loan_type',$mLoan->loan_type)->pluck('emi_date')->toArray();
                    // $eniDateOutstandingArray = array_values($lastOutstandingDate);
                $newDate = array();
                //$checkDate = array_intersect($nextEmiDates,$lastOutstandingDate);
                $deposit = $request['deposite_amount'];
            if(isset($lastOutstanding->out_standing_amount) )
            {
                $checkDateMonth = date('m',strtotime($lastOutstanding->emi_date));
                $checkDateYear = date('Y',strtotime($lastOutstanding->emi_date));
                $newstartDate = $checkDateYear.'-'.  $checkDateMonth.'-01';
                $newEndDate = $checkDateYear.'-'.  $checkDateMonth.'-31';
                $gapDayes = Carbon::parse($lastOutstanding->emi_date)->diff(Carbon::parse($applicationDate))->format('%a');
                $lastOutstanding2 = LoanEmisNew::where('loan_id',$mLoan->id)->where('loan_type',$mLoan->loan_type)->whereBetween('emi_date',[$newstartDate,$newEndDate])->sum('out_standing_amount');
                $roi = ((($mLoan->ROI) / 365) * $lastOutstanding->out_standing_amount) / 100;
                if($mLoan->emi_option == 1 || $mLoan->emi_option == 2)
                {
                    $roi = $roi * $gapDayes;
                    $principal_amount =$deposit - $roi;
                    if(!array_key_exists($applicationCurrentDate.'_'.$applicationCurrentDateMonth.'_'.$applicationCurrentDateYear,$nextEmiDates)){
                        $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit );
                    }else{
                        $preDate =current($nextEmiDates);
                        $oldDate = $nextEmiDates[$applicationCurrentDate.'_'.$applicationCurrentDateMonth.'_'.$applicationCurrentDateYear];
                        if($mLoan->emi_option == 1)
                        {
                            $previousDate = Carbon::parse($oldDate)->subMonth(1);
                        }
                        if($mLoan->emi_option == 2)
                        {
                            $previousDate = Carbon::parse($oldDate)->subDays(7);
                        }
                        $pDate = date('Y-m-d',strtotime("+1 day",strtotime($previousDate)))  ;
                        if($preDate == $applicationDate)
                        {
                            $aqmount = LoanEmisNew::where('loan_id',$mLoan->id)->whereBetween('emi_date',[$LoanCreatedDate,$applicationDate])->sum('roi_amount');
                        }
                        else{
                            $aqmount = LoanEmisNew::where('loan_id',$mLoan->id)->whereBetween('emi_date',[$pDate,$applicationDate])->sum('roi_amount');
                        }
                        if($aqmount > 0)
                        {
                            $outstandingAmount = ($lastOutstanding->out_standing_amount -$deposit + $roi   + $aqmount);
                        }else{
                            $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi );
                        }
                    }
                   $dailyoutstandingAmount = $outstandingAmount + $roi;
                }
                else{
                    $principal_amount =$deposit - $roi;
                    $outstandingAmount = ($lastOutstanding->out_standing_amount - $principal_amount );
                }
                $deposit =  $request['deposite_amount'];
            }
            else{
                $roi = ((($mLoan->ROI) / 365) * $mLoan->amount) / 100;
                $gapDayes = Carbon::parse($mLoan->approve_date)->diff(Carbon::parse($applicationDate))->format('%a');
                if($mLoan->emi_option == 1 || $mLoan->emi_option == 2) //Monthly
                {
                    $roi = $roi * $gapDayes;
                    $principal_amount =$deposit - $roi;
                    if(!array_key_exists($applicationCurrentDate.'_'.$applicationCurrentDateMonth.'_'.$applicationCurrentDateYear,$nextEmiDates)){
                        $outstandingAmount = ($mLoan->amount - $deposit );
                    }else{
                        $outstandingAmount = ($mLoan->amount -$deposit + $roi );
                    }
                   $dailyoutstandingAmount = $outstandingAmount + $roi;
                }else{
                $principal_amount = $deposit- $roi;
                $outstandingAmount = ($mLoan->amount - $principal_amount );
                }
                $deposit =  $request['deposite_amount'];
                $dailyoutstandingAmount = $mLoan->amount + $roi;
            }
            $amountArraySsb = array(
                '1' => $request['deposite_amount']
            );
            if(isset(($ssbAccountDetails['ssbMember'])))
            {
                $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name . ' ' . $ssbAccountDetails['ssbMember']->last_name;
            }
            else{
                $amount_deposit_by_name = NULL;
            }
            $dueAmount = $mLoan->due_amount - round($principal_amount);
            $glResult = Grouploans::find($request['loan_id']);
            $glData['credit_amount'] = $mLoan->credit_amount + round($principal_amount);
            $glData['due_amount'] = $dueAmount;
            if ($dueAmount == 0)
            {
                //$glData['status'] = 3;
            }
            $glData['received_emi_amount'] = $mLoan->received_emi_amount + $request['deposite_amount'];
            $glResult->update($glData);
            $gmLoan = Memberloans::with('loanMember')->where('id', $mLoan->member_loan_id)
                ->first();
            $gmDueAmount = $gmLoan->due_amount - $principal_amount;
            $mlResult = Memberloans::find($mLoan->member_loan_id);
            $lData['credit_amount'] = $mLoan->credit_amount + $principal_amount;
            $lData['due_amount'] = $gmDueAmount;
            if ($dueAmount == 0)
            {
                $lData['status'] = 3;
                $lData['clear_date'] = date("Y-m-d", strtotime(convertDate($request['application_date'])));
            }
            $mlResult->update($lData);
            if ($request['loan_emi_payment_mode'] == 0)
            {
                $cheque_dd_no = NULL;
                $online_payment_id = NULL;
                $online_payment_by = NULL;
                $bank_name = NULL;
                $cheque_date = NULL;
                $account_number = NULL;
                $paymentMode = 4;
                $ssbpaymentMode = 3;
                $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                // $record1 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                //     ->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($request['application_date']))))->orderby('id', 'desc')
                //     ->first();
                $transDate = date("Y-m-d ".$entryTime."",strtotime(convertDate($request['application_date'])));
                $record1 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                    ->whereDate('created_at', '<=',  $transDate)->orderby('id', 'desc')
                    ->first();
                $ssb['saving_account_id'] = $ssbAccountDetails->id;
                $ssb['account_no'] = $ssbAccountDetails->account_no;
                $ssb['opening_balance'] = $record1->opening_balance - $request['deposite_amount'];
                $ssb['branch_id'] = $branchId;
                $ssb['type'] = 9;
                $ssb['deposit'] = 0;
                $ssb['withdrawal'] = $request['deposite_amount'];
                $ssb['description'] = 'Loan EMI Payment'.$mLoan->account_number;
                $ssb['currency_code'] = 'INR';
                $ssb['payment_type'] = 'DR';
                $ssb['payment_mode'] = $ssbpaymentMode;
                $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $ssbAccountTran = SavingAccountTranscation::create($ssb);
                $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;
                // update saving account current balance
                $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                $ssbBalance->balance = $request['ssb_account'] - $request['deposite_amount'];
                $ssbBalance->save();
                $record2 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                    ->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($request['application_date']))))->get();
                foreach ($record2 as $key => $value)
                {
                    $nsResult = SavingAccountTranscation::find($value->id);
                    $nsResult['opening_balance'] = $value->opening_balance - $request['deposite_amount'];
                    $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                    $sResult->update($nsResult);
                }
                $data['saving_account_transaction_id'] = $ssb_transaction_id;
                $data['loan_id'] = $request['loan_id'];
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $satRef = TransactionReferences::create($data);
                $satRefId = $satRef->id;
                $updateSsbDayBook = $this->updateSsbDayBookAmount($request['deposite_amount'], $request['ssb_account_number'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))));
                $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->member_id, $branchId, getBranchCode($branchId)->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $ssbAccountDetails->account_no, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, $ssbAccountDetails->id, 'DR');
                $totalbalance = $request['ssb_account'] - $request['deposite_amount'];
                $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran, $satRefId, 1, $ssbAccountDetails->member_investments_id, 0, $ssbAccountDetails->member_id, $totalbalance, 0, $request['deposite_amount'], 'Withdrawal from SSB', $request['account_number'], $branchId, getBranchCode($branchId)->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $ssbAccountDetails->account_no, $cheque_dd_no, NULL, NULL, $paymentDate, $online_payment_by, $online_payment_by, $ssbAccountDetails->id, 'DR');
            }
            elseif ($request['loan_emi_payment_mode'] == 1)
            {
                if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '')
                {
                    $cheque_dd_no = $request['customer_cheque'];
                    $paymentMode = 1;
                    $ssbpaymentMode = 5;
                    $online_payment_id = NULL;
                    $online_payment_by = NULL;
                    $satRefId = NULL;
                    $bank_name = NULL;
                    $cheque_date = NULL;
                    $account_number = NULL;
                    $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                }
                elseif ($request['bank_transfer_mode'] == 1)
                {
                    $cheque_dd_no = NULL;
                    $paymentMode = 3;
                    $ssbpaymentMode = 5;
                    $online_payment_id = $request['utr_transaction_number'];
                    $online_payment_by = NULL;
                    $satRefId = NULL;
                    $bank_name = NULL;
                    $cheque_date = NULL;
                    $account_number = NULL;
                    $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                }
                $ssb_transaction_id = '';
                $ssb_account_id_from = '';
            }
            elseif ($request['loan_emi_payment_mode'] == 2)
            {
                $cheque_dd_no = NULL;
                $paymentMode = 0;
                $ssbpaymentMode = 0;
                $online_payment_id = NULL;
                $online_payment_by = NULL;
                $satRefId = NULL;
                $bank_name = NULL;
                $cheque_date = NULL;
                $account_number = NULL;
                $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $ssb_transaction_id = '';
                $ssb_account_id_from = '';
            }
            elseif ($request['loan_emi_payment_mode'] == 3)
            {
                $cheque_dd_no = $request['cheque_number'];
                $cheque_date = date("Y-m-d", strtotime(convertDate($request['application_date'])));
                $bank_name = $request['bank_name'];
                $account_number = $request['account_number'];
                $paymentMode = 1;
                $ssbpaymentMode = 1;
                $online_payment_id = NULL;
                $online_payment_by = NULL;
                $satRefId = NULL;
                $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $ssb_transaction_id = '';
                $ssb_account_id_from = '';
            }
            $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 9, $request['loan_id'], $mLoan->applicant_id, $branchId, getBranchCode($branchId)->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $mLoan->account_number, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, NULL, 'CR');
            $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran, $satRefId, 9, $request['loan_id'], 0, $mLoan->applicant_id, $dueAmount, $request['deposite_amount'], 0, 'Loan EMI deposite', $mLoan->account_number, $branchId, getBranchCode($branchId)->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $mLoan->account_number, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_by, $online_payment_by, NULL, 'CR');
            if ($request['loan_emi_payment_mode'] == 3)
            {
                $checkData['type'] = 5;
                $checkData['branch_id'] = $branchId;
                // $checkData['loan_id']=$request['loan_id'];
                $checkData['day_book_id'] = $createDayBook;
                $checkData['cheque_id'] = $cheque_dd_no;
                $checkData['status'] = 1;
                $checkData['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $ssbAccountTran = ReceivedChequePayment::create($checkData);
                $dataRC['status'] = 3;
                $receivedcheque = ReceivedCheque::find($cheque_dd_no);
                $receivedcheque->update($dataRC);
            }
            /*************** Head Implement ************/
            if ($request['loan_emi_payment_mode'] == 0)
            {
                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $v_no = "";
                for ($i = 0;$i < 10;$i++)
                {
                    $v_no .= $chars[mt_rand(0, strlen($chars) - 1) ];
                }
                $roidayBookRef = CommanTransactionsController::createBranchDayBookReference($roi + $principal_amount, $request['application_date']);
                $roibranchDayBook = CommanTransactionsController::branchDayBookNew($roidayBookRef, $branchId, 5, 55, $loanId, $ssb_transaction_id, $request['associate_member_id'], $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $roi + $principal_amount, $roi + $principal_amount, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'SSB A/C Cr ' . ($roi + $principal_amount) . '', 'SSB A/C Cr ' . ($roi + $principal_amount) . '', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_transaction_id, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 31, 5, 524, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 56, 5, 55, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                /*$roiallTransaction = $this->createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,55,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);
                $roiallTransaction = $this->createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,55,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
                $loan_head_id = 66;
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, $loan_head_id, 5, 55, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 56, 5, 55, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                /*$principalallTransaction = $this->createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,55,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);
                $principalallTransaction = $this->createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,55,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
                $principalmemberTransaction = CommanTransactionsController::memberTransactionNew($roidayBookRef, 5, 55, $loanId, $ssb_transaction_id, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_transaction_id, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
            }
            elseif ($request['loan_emi_payment_mode'] == 2)
            {
                $roidayBookRef = CommanTransactionsController::createBranchDayBookReference($roi + $principal_amount, $request['application_date']);
                $roibranchDayBook = CommanTransactionsController::branchDayBookNew($roidayBookRef, $branchId, 5, 55, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $roi + $principal_amount, $roi + $principal_amount, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'Cash A/C Dr ' . ($roi + $principal_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($roi + $principal_amount) . '', 'CR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 31, 5, 524, $loanId, $createDayBook, $request['associate_member_id'], NULL, $branchId, NULL, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'CR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                /*$roiallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 28, 5, 55, $loanId, $createDayBook, $request['associate_member_id'], NULL, $branchId, NULL, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'DR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                /*$roiallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
                $loan_head_id = 66;
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, $loan_head_id, 5, 55, $loanId, $createDayBook, $request['associate_member_id'], NULL, $branchId, NULL, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'CR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                /*$principalallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 28, 5, 55, $loanId, $createDayBook, $request['associate_member_id'], NULL, $branchId, NULL, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'DR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                /*$principalallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
                $principalmemberTransaction = CommanTransactionsController::memberTransactionNew($roidayBookRef, 5, 55, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'DR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                $createRoiBranchCash = $this->updateBranchCashFromBackDate($roi + $principal_amount, $branchId, date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))));
            }
            elseif ($request['loan_emi_payment_mode'] == 1)
            {
                $loan_head_id = 66;
                if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '')
                { //cheque payment
                    $payment_type = 1;
                    $cheque_type = 1;
                    $amount_from_id = $request['associate_member_id'];
                    $amount_from_name = getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name;
                    $cheque_no = $request['customer_cheque'];
                    $cheque_date = NULL;
                    $cheque_bank_from = $request['customer_bank_name'];
                    $cheque_bank_ac_from = $request['customer_bank_account_number'];
                    $cheque_bank_ifsc_from = $request['customer_ifsc_code'];
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = $request['company_bank'];
                    $cheque_bank_ac_to = $request['bank_account_number'];
                    $v_no = NULL;
                    $v_date = NULL;
                    $ssb_account_id_from = NULL;
                    $transction_no = NULL;
                    $transction_bank_from = NULL;
                    $transction_bank_ac_from = NULL;
                    $transction_bank_ifsc_from = NULL;
                    $transction_bank_branch_from = NULL;
                    $company_name = $request->cheque_company_bank;;
                    $bankId = getSamraddhBankAccount($request['company_bank_account_number'])->id;
                    $head_id = getSamraddhBankAccount($request['company_bank_account_number'])->account_head_id;
                    $cId = \App\Models\SamraddhBank::where('account_head_id', $head_id)->first();
                    $company_bankId = $cId->id;
                    $transction_bank_ac_to = $bankId;
                    $transction_bank_to = $company_bankId;
                    $ifsc = getSamraddhBankAccount($request['company_bank_account_number'])->ifsc_code;
                }
                elseif ($request['bank_transfer_mode'] == 1)
                {
                    $payment_type = 2;
                    $cheque_type = NULL;
                    $amount_from_id = $request['associate_member_id'];
                    $amount_from_name = getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name;
                    $cheque_no = NULL;
                    $cheque_date = NULL;
                    $cheque_bank_from = NULL;
                    $cheque_bank_ac_from = NULL;
                    $cheque_bank_ifsc_from = NULL;
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = NULL;
                    $cheque_bank_ac_to = NULL;
                    $transction_no = $request['utr_transaction_number'];
                    $v_no = NULL;
                    $v_date = NULL;
                    $ssb_account_id_from = NULL;
                    $transction_bank_from = $request['customer_bank_name'];
                    $transction_bank_ac_from = $request['customer_bank_account_number'];
                    $transction_bank_ifsc_from = $request['customer_ifsc_code'];
                    $transction_bank_branch_from = $request['customer_branch_name'];
                    $transction_bank_to = $request['company_bank'];
                    $transction_bank_ac_to = $request['bank_account_number'];
                    $company_name = getSamraddhBank($request['company_bank'])->bank_name;
                    $ifsc = getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code;
                    $bankId = getSamraddhBankAccount($request['bank_account_number'])->id;
                    $head_id = getSamraddhBankAccount($request['bank_account_number'])->account_head_id;
                    $company_bankId = getSamraddhBank($request['company_bank'])->id;
                }
                $roidayBookRef = CommanTransactionsController::createBranchDayBookReference($roi + $principal_amount, date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))));
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 31, 5, 524, $loanId, $createDayBook, $request['associate_member_id'], NULL, NULL, NULL, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'CR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, NULL, NULL, NULL, $cheque_type, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, NULL, NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_from, $branchId, $transction_bank_ac_to, $ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, NULL, NULL, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, NULL, 1, Auth::user()->id);
                /*$roiallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, $head_id, 5, 55, $loanId, $createDayBook, $request['associate_member_id'], NULL, NULL, NULL, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'CR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, NULL, NULL, NULL, $cheque_type, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, NULL, NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_from, $branchId, $transction_bank_ac_to, $ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, NULL, NULL, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, NULL, 1, Auth::user()->id);
                /*$allTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,$head5=NULL,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, $loan_head_id, 5, 55, $loanId, $createDayBook, $request['associate_member_id'], NULL, NULL, NULL, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'CR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, NULL, NULL, NULL, $cheque_type, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, NULL, NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_from, $branchId, $transction_bank_ac_to, $ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, NULL, NULL, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, NULL, 1, Auth::user()->id);
                /*$principalallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
                $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, $head_id, 5, 55, $loanId, $createDayBook, $request['associate_member_id'], NULL, NULL, NULL, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'CR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, NULL, NULL, NULL, $cheque_type, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, NULL, NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_from, $branchId, $transction_bank_ac_to, $ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, NULL, NULL, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, NULL, 1, Auth::user()->id);
                /*$allTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,$head5=NULL,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
                $roibranchDayBook = CommanTransactionsController::branchDayBookNew($roidayBookRef, $branchId, 5, 55, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $roi + $principal_amount, $roi + $principal_amount, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'Online A/C Dr ' . ($roi + $principal_amount) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($roi + $principal_amount) . '', 'CR', $payment_type, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no, $cheque_date, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                $principalmemberTransaction = CommanTransactionsController::memberTransactionNew($roidayBookRef, 5, 55, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'DR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                $samraddhBankDaybook = CommanTransactionsController::samraddhBankDaybookNew($roidayBookRef, $bank_id = NULL, $account_id = NULL, 5, 55, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $roi + $principal_amount, $roi + $principal_amount, $roi + $principal_amount, 'Loan Panelty Charge', 'Online A/C Cr. ' . ($roi + $principal_amount) . '', 'Online A/C Cr. ' . ($roi + $principal_amount) . '', 'CR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
                $createPrincipleBankClosing = CommanTransactionsController::updateBackDateloanBankBalance($roi + $principal_amount, $company_bankId, $bankId, date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))) , 0, 0);
                $dataRC['status'] = 3;
                $receivedcheque = \App\Models\ReceivedCheque::find($request['customer_cheque']);
                $receivedcheque->update($dataRC);
            }
            /*************** Head Implement ************/
            /*---------- commission script  start  ---------*/
            $daybookId = $createDayBook;
            $total_amount = $request['deposite_amount'];
            $percentage = 2;
            $month = NULL;
            $type_id = $request['loan_id'];
            $type = 7;
            $associate_id = $request['associate_member_id'];
            $branch_id = $branchId;
            $commission_type = 0;
            $associateDetail = Member::where('id', $associate_id)->first();
            $carder = $associateDetail->current_carder_id;
            $associate_exist = 0;
            $percentInDecimal = $percentage / 100;
            $commission_amount = round($percentInDecimal * $total_amount, 4);
            $associateCommission['member_id'] = $associate_id;
            $associateCommission['branch_id'] = $branch_id;
            $associateCommission['type'] = $type;
            $associateCommission['type_id'] = $type_id;
            $associateCommission['day_book_id'] = $daybookId;
            $associateCommission['total_amount'] = $total_amount;
            $associateCommission['month'] = $month;
            $associateCommission['commission_amount'] = $commission_amount;
            $associateCommission['percentage'] = $percentage;
            $associateCommission['commission_type'] = $commission_type;
            $date = \App\Models\Daybook::where('id', $daybookId)->first();
            $associateCommission['created_at'] = $date->created_at;
            $associateCommission['pay_type'] = 4;
            $associateCommission['carder_id'] = $carder;
            $associateCommission['associate_exist'] = $associate_exist;
            $loan_associate_code = $request->loan_associate_code;
            if($loan_associate_code != 9999999)
            {
            $associateCommissionInsert = \App\Models\CommissionEntryLoan::create($associateCommission);
            }
            // Collection start
            /*  $collection_percentage=5;
            $collection_type=8;
            $Collection_associate_id=$mLoan->associate_member_id;
            $collection_associateDetail=Member::where('id',$Collection_associate_id)->first();
            $Collection_carder=$collection_associateDetail->current_carder_id;
            $collection_percentInDecimal = $collection_percentage / 100;
            $collection_commission_amount = round($collection_percentInDecimal * $total_amount,4);
            $coll_associateCommission['member_id'] = $Collection_associate_id;
            $coll_associateCommission['branch_id'] = $branch_id;
            $coll_associateCommission['type'] = $collection_type;
            $coll_associateCommission['type_id'] = $type_id;
            $coll_associateCommission['day_book_id'] = $daybookId;
            $coll_associateCommission['total_amount'] = $total_amount;
            $coll_associateCommission['month'] = $month;
            $coll_associateCommission['commission_amount'] = $collection_commission_amount;
            $coll_associateCommission['percentage'] = $collection_percentage;
            $coll_associateCommission['commission_type'] = $commission_type;
            $coll_date =\App\Models\Daybook::where('id',$daybookId)->first();
            $coll_associateCommission['created_at'] = $coll_date->created_at;
            $coll_associateCommission['pay_type'] = 4;
            $coll_associateCommission['carder_id'] = $Collection_carder;
            $coll_associateCommission['associate_exist'] = $associate_exist;
            $coll_associateCommissionInsert = \App\Models\CommissionEntryLoan::create($coll_associateCommission);*/
            /*---------- commission script  end  ---------*/
            $createLoanDayBook = CommanTransactionsController::createLoanDayBook($roidayBookRef, $daybookId, $mLoan->loan_type, 0, $mLoan->id, $lId = NULL, $mLoan->account_number, $mLoan->applicant_id, $roi, $principal_amount, $dueAmount, $request['deposite_amount'], 'Loan EMI deposite', $branchId, getBranchCode($branchId)->branch_code, 'CR', 'INR', $paymentMode, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, 1, 1, $cheque_date, $account_number, NULL, $request['loan_associate_name'], $request['associate_member_id'], $branchId,$totalDailyInterest,$totalDayInterest,$penalty);
            $totalDepsoit = LoanDaybooks::where('account_number',$mLoan->account_number)->where('loan_sub_type',0)->where('is_deleted',0)->sum('deposit');
            $text = 'Dear Member,Received Rs.'.  $request['deposite_amount']. ' as EMI of Loan A/C '. $mLoan->account_number.' on '. date('d/m/Y',strtotime(convertDate($request['application_date']))).' Total recovery Rs. '.$totalDepsoit.' Thank You. Samraddh Bestwin Microfinance';;
            $temaplteId = 1207166308935249821;
            $contactNumber = array();
            $memberDetail = Member::find($mLoan->member_id);
            $contactNumber[] = $memberDetail->mobile_no;
            $sendToMember = new Sms();
            $sendToMember->sendSms( $contactNumber, $text, $temaplteId);
            if ($request['penalty_amount'] != '' && $request['penalty_amount'] > 0)
            {
                $amountArray = array(
                    '1' => $request['penalty_amount']
                );
                if ($request['loan_emi_payment_mode'] == 0)
                {
                    $cheque_dd_no = NULL;
                    $online_payment_id = NULL;
                    $online_payment_by = NULL;
                    $bank_name = NULL;
                    $cheque_date = NULL;
                    $account_number = NULL;
                    $paymentMode = 4;
                    $ssbpaymentMode = 3;
                    $paymentDate = date("Y-m-d", strtotime(convertDate($request['application_date'])));
                    $ssb['saving_account_id'] = $ssbAccountDetails->id;
                    $ssb['account_no'] = $ssbAccountDetails->account_no;
                    $ssb['opening_balance'] = $request['ssb_account'] - $request['penalty_amount'];
                    $ssb['deposit'] = 0;
                    $ssb['withdrawal'] = $request['penalty_amount'];
                    $ssb['description'] = 'Loan EMI Penalty';
                    $ssb['currency_code'] = 'INR';
                    $ssb['payment_type'] = 'DR';
                    $ssb['payment_mode'] = $ssbpaymentMode;
                    $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                    $ssbAccountTran = SavingAccountTranscation::create($ssb);
                    $ssb_transaction_id = $ssbAccountTran->id;
                    // update saving account current balance
                    $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                    $ssbBalance->balance = $request['ssb_account'] - $request['penalty_amount'];
                    $ssbBalance->save();
                    $data['saving_account_transaction_id'] = $ssb_transaction_id;
                    $data['loan_id'] = $request['loan_id'];
                    $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                    $satRef = TransactionReferences::create($data);
                    $satRefId = $satRef->id;
                    $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->member_id, $branchId, getBranchCode($branchId)->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $ssbAccountDetails->account_no, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, $ssbAccountDetails->id, 'DR');
                    $totalbalance = $request['ssb_account'] - $request['penalty_amount'];
                    $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran, $satRefId, 1, $ssbAccountDetails->member_investments_id, 0, $ssbAccountDetails->member_id, $totalbalance, 0, $request['penalty_amount'], 'Withdrawal from SSB', $request['account_number'], $branchId, getBranchCode($branchId)->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $ssbAccountDetails->account_no, $cheque_dd_no, NULL, NULL, $paymentDate, $online_payment_by, $online_payment_by, $ssbAccountDetails->id, 'DR');
                }
                elseif ($request['loan_emi_payment_mode'] == 1)
                {
                    if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '')
                    {
                        $paymentMode = 1;
                        $cheque_dd_no = $request['customer_cheque'];
                        $ssbpaymentMode = 5;
                        $online_payment_id = NULL;
                        $online_payment_by = NULL;
                        $satRefId = NULL;
                        $bank_name = NULL;
                        $cheque_date = NULL;
                        $account_number = NULL;
                        $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));;
                    }
                    elseif ($request['bank_transfer_mode'] == 1)
                    {
                        $paymentMode = 3;
                        $cheque_dd_no = NULL;
                        $ssbpaymentMode = 5;
                        $online_payment_id = $request['utr_transaction_number'];
                        $online_payment_by = NULL;
                        $satRefId = NULL;
                        $bank_name = NULL;
                        $cheque_date = NULL;
                        $account_number = NULL;
                        $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));;
                    }
                    $ssb_transaction_id = '';
                }
                elseif ($request['loan_emi_payment_mode'] == 2)
                {
                    $cheque_dd_no = NULL;
                    $paymentMode = 0;
                    $ssbpaymentMode = 0;
                    $online_payment_id = NULL;
                    $online_payment_by = NULL;
                    $satRefId = NULL;
                    $bank_name = NULL;
                    $cheque_date = NULL;
                    $account_number = NULL;
                    $paymentDate = date("Y-m-d", strtotime(convertDate($request['application_date'])));
                    $ssb_transaction_id = '';
                }
                $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 11, $request['loan_id'], $mLoan->applicant_id, $branchId, getBranchCode($branchId)->branch_code, $amountArray, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $mLoan->account_number, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, NULL, 'CR');
                $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran, $satRefId, 11, $request['loan_id'], 0, $mLoan->applicant_id, $dueAmount, $request['penalty_amount'], 0, 'Loan EMI penalty', $mLoan->account_number, $branchId, getBranchCode($branchId)->branch_code, $amountArray, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $mLoan->account_number, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_by, $online_payment_by, NULL, 'CR');
                if ($request['loan_emi_payment_mode'] == 3)
                {
                    $checkData['type'] = 5;
                    $checkData['branch_id'] = $branchId;
                    // $checkData['loan_id']=$request['loan_id'];
                    $checkData['day_book_id'] = $createDayBook;
                    $checkData['cheque_id'] = $cheque_dd_no;
                    $checkData['status'] = 1;
                    $checkData['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                    $ssbAccountTran = ReceivedChequePayment::create($checkData);
                    $dataRC['status'] = 3;
                    $receivedcheque = ReceivedCheque::find($cheque_dd_no);
                    $receivedcheque->update($dataRC);
                }
                /************** Head Implement *************/
                if ($request['loan_emi_payment_mode'] == 0)
                {
                    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                    $v_no = "";
                    for ($i = 0;$i < 10;$i++)
                    {
                        $v_no .= $chars[mt_rand(0, strlen($chars) - 1) ];
                    }
                    $penaltyDayBookRef = CommanTransactionsController::createBranchDayBookReference($request['penalty_amount'], $request['application_date']);
                    $roibranchDayBook = CommanTransactionsController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 56, $loanId, $ssb_transaction_id, $request['associate_member_id'], $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'SSB A/C Cr ' . $request['penalty_amount'] . '', 'SSB A/C Cr ' . $request['penalty_amount'] . '', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_transaction_id, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                    $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $branchId, NULL, NULL, 33, 5, 56, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                    /*$roiallTransaction = $this->createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,56,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
                    $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $branchId, NULL, NULL, 56, 5, 56, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                    /*$roiallTransaction = $this->createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,56,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
                    $roimemberTransaction = CommanTransactionsController::memberTransactionNew($penaltyDayBookRef, 5, 56, $loanId, $ssb_transaction_id, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $request['penalty_amount'], 'Loan Panelty Charge', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_transaction_id, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                    if($gstAmount)
                    {
                        if($IntraState)
                        {
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 542, $loanId, $ssb_transaction_id, $request['associate_member_id'], $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty CGST Charge', 'SSB A/C Cr ' . $gstAmount . '', 'SSB A/C Cr ' . $gstAmount . '', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 543, $loanId, $ssb_transaction_id, $request['associate_member_id'], $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty SGST Charge', 'SSB A/C Cr ' . $gstAmount . '', 'SSB A/C Cr ' . $gstAmount . '', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $cgstHead, 5, 542, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty  CGST Charge', 'CR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $sgstHead, 5, 543, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty  SGST Charge', 'CR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 56, 5, 542, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty CGST Charge', 'DR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 56, 5, 543, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty SGST Charge', 'DR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 542, $loanId, $ssb_transaction_id, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Group Loan Panelty CGST Charge', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], date("H:i:s") , 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 543, $loanId, $ssb_transaction_id, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Group Loan Panelty SGST Charge', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], date("H:i:s") , 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                        }
                        else{
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 544, $loanId, $ssb_transaction_id, $request['associate_member_id'], $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Group Panelty IGST Charge', 'SSB A/C Cr ' . $gstAmount . '', 'SSB A/C Cr ' . $gstAmount . '', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $cgstHead, 5, 544, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty  IGST Charge', 'CR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 56, 5, 544, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty IGST Charge', 'DR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);

                            // $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 56, 5, 544, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty SGST Charge', 'DR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);

                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 544, $loanId, $ssb_transaction_id, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Group Loan Panelty IGST Charge', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($request['application_date']))) , $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], date("H:i:s") , 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                        }
                        $createdGstTransaction = CommanController::gstTransaction($dayBookId = $penaltyDayBookRef,$getGstSettingno->gst_no,(!isset($mLoan['loanMember']->gst_no)) ? NULL : $mLoan['loanMember']->gst_no,$request['penalty_amount'],$getHeadSetting->gst_percentage,($IntraState == false ? $gstAmount : 0 ) ,($IntraState == true ? $gstAmount : 0),($IntraState == true ? $gstAmount : 0),($IntraState == true) ? $request['penalty_amount'] + $gstAmount + $gstAmount : $request['penalty_amount'] + $gstAmount,33,$request['date'],'LP33',$mLoan['loanMember']->id,$mLoan->branch_id);
                    }
                }
                elseif ($request['loan_emi_payment_mode'] == 2)
                {
                    $penaltyDayBookRef = CommanTransactionsController::createBranchDayBookReference($request['penalty_amount'], $request['application_date']);
                    $roibranchDayBook = CommanTransactionsController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 56, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'Cash A/C Dr ' . $request['penalty_amount'] . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . $request['penalty_amount'] . '', 'CR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                    $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $branchId, NULL, NULL, 33, 5, 56, $loanId, $createDayBook, $request['associate_member_id'], NULL, $branchId, NULL, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'CR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                    /*$roiallTransaction = CommanTransactionsController::createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,56,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
                    $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $branchId, NULL, NULL, 28, 5, 56, $loanId, $createDayBook, $request['associate_member_id'], NULL, $branchId, NULL, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'DR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                    /*$roiallTransaction = CommanTransactionsController::createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,56,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
                    $roimemberTransaction = CommanTransactionsController::memberTransactionNew($penaltyDayBookRef, 5, 56, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $request['penalty_amount'], 'Loan Panelty Charge', 'DR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                    if($gstAmount > 0)
                    {
                        if($IntraState)
                        {
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 536, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty CGST Charge', 'Cash A/C Dr ' . $gstAmount . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . $gstAmount . '', 'CR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 537, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty SGST Charge', 'Cash A/C Dr ' . $gstAmount . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . $gstAmount . '', 'CR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $cgstHead, 5, 536, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty CGST Charge', 'CR', 0, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 28, 5, 537, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty CGST Charge', 'DR', 0, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $sgstHead, 5, 536, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty SGST Charge', 'CR', 0, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 28, 5, 537, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, ' Group Loan Panelty SGST Charge', 'DR', 0, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 536, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Group Loan Panelty CGST Charge', 'DR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 537, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Group Loan Panelty SGST Charge', 'DR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                        }
                        else{
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 538, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty IGST Charge', 'Cash A/C Dr ' . $gstAmount . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . $gstAmount . '', 'CR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $cgstHead, 5, 538, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty IGST Charge', 'CR', 0, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 28, 5, 538, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty IGST Charge', 'DR', 0, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 538, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Group Loan Panelty IGST Charge', 'DR', 0, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                        }
                        $createdGstTransaction = CommanController::gstTransaction($dayBookId = $penaltyDayBookRef,$getGstSettingno->gst_no,(!isset($mLoan['loanMember']->gst_no)) ? NULL : $mLoan['loanMember']->gst_no,$request['penalty_amount'],$getHeadSetting->gst_percentage,($IntraState == false ? $gstAmount : 0 ) ,($IntraState == true ? $gstAmount : 0),($IntraState == true ? $gstAmount : 0),($IntraState == true) ? $request['penalty_amount'] + $gstAmount + $gstAmount : $request['penalty_amount'] + $gstAmount,33,$request['date'],'LP33',$mLoan['loanMember']->id,$request['branch']);
                    }
                    $createRoiBranchCash = $this->updateBranchCashFromBackDate($request['penalty_amount'] + $gstAmount, $branchId, date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))));
                }
                elseif ($request['loan_emi_payment_mode'] == 1)
                {
                    if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '')
                    {
                        $payment_type = 1;
                        $cheque_type = 1;
                        $amount_from_id = $request['associate_member_id'];
                        $amount_from_name = getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name;
                        $cheque_no = $request['customer_cheque'];
                        $cheque_date = NULL;
                        $cheque_bank_from = $request['customer_bank_name'];
                        $cheque_bank_ac_from = $request['customer_bank_account_number'];
                        $cheque_bank_ifsc_from = $request['customer_ifsc_code'];
                        $cheque_bank_branch_from = NULL;
                        $cheque_bank_to = $request['company_bank'];
                        $cheque_bank_ac_to = $request['bank_account_number'];
                        $v_no = NULL;
                        $v_date = NULL;
                        $ssb_account_id_from = NULL;
                        $transction_no = NULL;
                        $transction_bank_from = NULL;
                        $transction_bank_ac_from = NULL;
                        $transction_bank_ifsc_from = NULL;
                        $transction_bank_branch_from = NULL;
                        $transction_bank_to = NULL;
                        $transction_bank_ac_to = NULL;
                    }
                    elseif ($request['bank_transfer_mode'] == 1)
                    {
                        $payment_type = 2;
                        $cheque_type = NULL;
                        $amount_from_id = $request['associate_member_id'];
                        $amount_from_name = getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name;
                        $cheque_no = NULL;
                        $cheque_date = NULL;
                        $cheque_bank_from = NULL;
                        $cheque_bank_ac_from = NULL;
                        $cheque_bank_ifsc_from = NULL;
                        $cheque_bank_branch_from = NULL;
                        $cheque_bank_to = NULL;
                        $cheque_bank_ac_to = NULL;
                        $transction_no = $request['utr_transaction_number'];
                        $v_no = NULL;
                        $v_date = NULL;
                        $ssb_account_id_from = NULL;
                        $transction_bank_from = $request['customer_bank_name'];
                        $transction_bank_ac_from = $request['customer_bank_account_number'];
                        $transction_bank_ifsc_from = $request['customer_ifsc_code'];
                        $transction_bank_branch_from = $request['customer_branch_name'];
                        $transction_bank_to = $request['company_bank'];
                        $transction_bank_ac_to = $request['bank_account_number'];
                    }
                    $penaltyDayBookRef = CommanController::createBranchDayBookReference($request['penalty_amount'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))));
                    $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $branchId, NULL, NULL, 33, 5, 56, $loanId, $createDayBook, $request['associate_member_id'], NULL, NULL, NULL, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'CR', $payment_type, 'INR', $request['company_bank'], getSamraddhBank($request['company_bank'])->bank_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, NULL, NULL, NULL, $cheque_type, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, NULL, NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_from, $branchId, $transction_bank_ac_to, getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, NULL, NULL, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($request['company_bank'])->bank_name, $transction_bank_ac_to, NULL, getSamraddhBankAccount($request['bank_account_number'])->ifsc_code, NULL, 1, Auth::user()
                        ->id);
                    /*$roiallTransaction = CommanController::createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,56,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
                    $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $branchId, NULL, NULL, getSamraddhBankAccount($request['bank_account_number'])->account_head_id, 5, 56, $loanId, $createDayBook, $request['associate_member_id'], NULL, NULL, NULL, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'DR', $payment_type, 'INR', $request['company_bank'], getSamraddhBank($request['company_bank'])->bank_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, NULL, NULL, NULL, $cheque_type, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, NULL, NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_from, $branchId, $transction_bank_ac_to, getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, NULL, NULL, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($request['company_bank'])->bank_name, $transction_bank_ac_to, NULL, getSamraddhBankAccount($request['bank_account_number'])->ifsc_code, NULL, 1, Auth::user()
                        ->id);
                    /*$allTransaction = CommanController::createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,$head5=NULL,5,56,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
                    $roibranchDayBook = CommanTransactionsController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 56, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'Cash A/C Dr ' . $request['penalty_amount'] . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . $request['penalty_amount'] . '', 'CR', $payment_type, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                    $principalmemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 56, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $request['penalty_amount'], 'Loan Panelty Charge', 'DR', $payment_type, 'INR', $request['company_bank'], getSamraddhBank($request['company_bank'])->bank_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                    $samraddhBankDaybook = CommanTransactionsController::samraddhBankDaybookNew($penaltyDayBookRef, $bank_id = getSamraddhBank($request['company_bank'])->id, $account_id = getSamraddhBank($request['company_bank'])->id, 5, 56, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $request['penalty_amount'], $request['penalty_amount'], $request['penalty_amount'], 'Loan Panelty Charge', 'Cash A/C Cr. ' . $request['penalty_amount'] . '', 'Cash A/C Cr. ' . $request['penalty_amount'] . '', 'CR', $payment_type, 'INR', $request['company_bank'], getSamraddhBank($request['company_bank'])->bank_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
                    if($gstAmount > 0)
                    {
                        if($IntraState)
                        {
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 536, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty CGST Charge', 'Bank A/C Dr ' . $gstAmount . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . $gstAmount . '', 'CR', $payment_type, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 537, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty SGST Charge', 'Cash A/C Dr ' . $gstAmount . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . $gstAmount . '', 'CR', $payment_type, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $cgstHead, 5, 536, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty CGST Charge', 'CR', $payment_type, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 28, 5, 537, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty CGST Charge', 'DR', $payment_type, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $sgstHead, 5, 536, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty SGST Charge', 'CR', $payment_type, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, 28, 5, 537, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty SGST Charge', 'DR', $payment_type, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 536, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Group Loan Panelty CGST Charge', 'DR', $payment_type, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 537, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Group Loan Panelty SGST Charge', 'DR', $payment_type, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($penaltyDayBookRef, $bank_id = $company_bankId, $account_id = NULL, 5, 536, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty CGST Charge', 'Cash A/C Cr. ' . $gstAmount . '', 'Cash A/C Cr. ' . $gstAmount . '', 'CR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, $request['application_date'], $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
                            $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($penaltyDayBookRef, $bank_id = $company_bankId, $account_id = NULL, 5, 537, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty SGST Charge', 'Cash A/C Cr. ' . $gstAmount . '', 'Cash A/C Cr. ' . $gstAmount. '', 'CR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, $request['application_date'], $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
                        }
                        else{
                            $roibranchDayBook = CommanController::branchDayBookNew($penaltyDayBookRef, $branchId, 5, 538, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty IGST Charge', 'Cash A/C Dr ' . $gstAmount . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . $gstAmount . '', 'CR', $payment_type, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $request['branch'], NULL, NULL, $cgstHead, 5, 538, $loanId, $createDayBook, $request['associate_member_id'], NULL, $request['branch'], NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty IGST Charge', 'CR', 0, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id);
                            $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef, $branchId, NULL, NULL, getSamraddhBankAccount($request['bank_account_number'])->account_head_id, 5, 538, $loanId, $createDayBook, $request['associate_member_id'], NULL, NULL, NULL, $gstAmount, $gstAmount, $gstAmount, 'Loan Panelty IGST Charge', 'DR', $payment_type, 'INR', $request['company_bank'], getSamraddhBank($request['company_bank'])->bank_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, NULL, NULL, NULL, $cheque_type, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, NULL, NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_from, $branchId, $transction_bank_ac_to, getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, NULL, NULL, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($request['company_bank'])->bank_name, $transction_bank_ac_to, NULL, getSamraddhBankAccount($request['bank_account_number'])->ifsc_code, NULL, 1, Auth::user()
                            ->id);
                            $roimemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef, 5, 538, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $gstAmount, 'Group Loan Panelty IGST Charge', 'DR', $payment_type, 'INR', $branchId, getBranchDetail($branchId)->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $request['application_date'], $request['application_date'], $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
                            $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($penaltyDayBookRef, $bank_id = $company_bankId, $account_id = NULL, 5, 538, $loanId, $createDayBook, $request['associate_member_id'], $member_id = NULL, $branchId, $gstAmount, $gstAmount, $gstAmount, 'Group Loan Panelty IGST Charge', 'Cash A/C Cr. ' . $gstAmount . '', 'Cash A/C Cr. ' . $gstAmount. '', 'CR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, $request['application_date'], $entry_date = NULL, $entry_time = NULL, 2, Auth::user()->id, $request['application_date'], $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
                        }
                        $createdGstTransaction = CommanController::gstTransaction($dayBookId = $penaltyDayBookRef,$getGstSettingno->gst_no,(!isset($mLoan['loanMember']->gst_no)) ? NULL : $mLoan['loanMember']->gst_no,$request['penalty_amount'],$getHeadSetting->gst_percentage,($IntraState == false ? $gstAmount : 0 ) ,($IntraState == true ? $gstAmount : 0),($IntraState == true ? $gstAmount : 0),($IntraState == true) ? $request['penalty_amount'] + $gstAmount + $gstAmount : $request['penalty_amount'] + $gstAmount,33,$request['date'],'LP33',$mLoan['loanMember']->id,$request['branch']);
                    }
                    $createPrincipleBankClosing = CommanController::updateBackDateloanBankBalance($request['penalty_amount'] + $gstAmount, $request['company_bank'], getSamraddhBankAccount($request['bank_account_number'])->id, date("Y-m-d H:i:s", strtotime(convertDate($request['application_date']))) , 0, 0);
                }
                /************** Head Implement *************/
                /*---------- penalty commission script  start  ---------*/
                /* $daybookId=$createDayBook;
                $total_amount=$request['penalty_amount'];
                $month=NULL;
                $type_id=$request['loan_id'];
                $branch_id=$branchId;
                $associate_exist=0;
                $commission_type=0;
                // Collection start
                $collection_percentage=5;
                $collection_type=8;
                $Collection_associate_id=$mLoan->associate_member_id;
                $collection_associateDetail=Member::where('id',$Collection_associate_id)->first();
                $Collection_carder=$collection_associateDetail->current_carder_id;
                $collection_percentInDecimal = $collection_percentage / 100;
                $collection_commission_amount = round($collection_percentInDecimal * $total_amount,4);
                $coll_associateCommission['member_id'] = $Collection_associate_id;
                $coll_associateCommission['branch_id'] = $branch_id;
                $coll_associateCommission['type'] = $collection_type;
                $coll_associateCommission['type_id'] = $type_id;
                $coll_associateCommission['day_book_id'] = $daybookId;
                $coll_associateCommission['total_amount'] = $total_amount;
                $coll_associateCommission['month'] = $month;
                $coll_associateCommission['commission_amount'] = $collection_commission_amount;
                $coll_associateCommission['percentage'] = $collection_percentage;
                $coll_associateCommission['commission_type'] = $commission_type;
                $coll_date =\App\Models\Daybook::where('id',$daybookId)->first();
                $coll_associateCommission['created_at'] = $coll_date->created_at;
                $coll_associateCommission['pay_type'] = 4;
                $coll_associateCommission['carder_id'] = $Collection_carder;
                $coll_associateCommission['associate_exist'] = $associate_exist;
                $coll_associateCommissionInsert = \App\Models\CommissionEntryLoan::create($coll_associateCommission);*/
                /*---------- commission script  end  ---------*/
                if ($request['loan_emi_payment_mode'] == 0)
                {
                    $paymentMode = 4;
                    $cheque_dd_no = NULL;
                    $ssbpaymentMode = 5;
                    $online_payment_id = NULL;
                    $online_payment_by = NULL;
                    $satRefId = NULL;
                    $bank_name = NULL;
                    $cheque_date = NULL;
                    $account_number = NULL;
                    $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));;
                }
                elseif ($request['loan_emi_payment_mode'] == 1)
                {
                    if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '')
                    {
                        $paymentMode = 1;
                        $cheque_dd_no = $request['customer_cheque'];
                        $ssbpaymentMode = 5;
                        $online_payment_id = NULL;
                        $online_payment_by = NULL;
                        $satRefId = NULL;
                        $bank_name = NULL;
                        $cheque_date = NULL;
                        $account_number = NULL;
                        $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));;
                    }
                    elseif ($request['bank_transfer_mode'] == 1)
                    {
                        $paymentMode = 2;
                        $cheque_dd_no = NULL;
                        $ssbpaymentMode = 5;
                        $online_payment_id = $request['utr_transaction_number'];
                        $online_payment_by = NULL;
                        $satRefId = NULL;
                        $bank_name = NULL;
                        $cheque_date = NULL;
                        $account_number = NULL;
                        $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));;
                    }
                }
                elseif ($request['loan_emi_payment_mode'] == 2)
                {
                    $cheque_dd_no = NULL;
                    $paymentMode = 0;
                    $ssbpaymentMode = 0;
                    $online_payment_id = NULL;
                    $online_payment_by = NULL;
                    $satRefId = NULL;
                    $bank_name = NULL;
                    $cheque_date = NULL;
                    $account_number = NULL;
                    $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));;
                }
                $createLoanDayBook = CommanTransactionsController::createLoanDayBook($penaltyDayBookRef, $createDayBook, $mLoan->loan_type, 1, $mLoan->id, $lId = NULL, $mLoan->account_number, $mLoan->applicant_id, $request['penalty_amount'], $request['penalty_amount'], $opening_balance = NULL, $request['penalty_amount'], 'Loan EMI penalty', $branchId, getBranchCode($branchId)->branch_code, 'CR', 'INR', $paymentMode, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, 1, 1, $cheque_date, $account_number, NULL, $request['loan_associate_name'], $request['associate_member_id'], $branchId,$totalDailyInterest,$totalDayInterest,$penalty);
                if($gstAmount)
                {
                    if($IntraState)
                    {
                        $updateData =$createLoanDayBook->update(['cgst_charge'=>$gstAmount,'sgst_charge'=>$gstAmount]);
                    }
                    else{
                        $updateData =$createLoanDayBook->update(['igst_charge'=>$gstAmount]);
                    }
                }
            }
            DB::commit();
        }
        catch(\Exception $ex)
        {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return back()
            ->with('success', 'Loan EMI Successfully submitted!');
    }
    // public function depositeGroupLoanEmi(Request $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $entryTime = date("H:i:s");
    //         Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date']))));
    //         $branchId = branchName()->id;
    //         //$branchId = $request['branch'];
    //         $loanId = $request['loan_id'];
    //         $ssbAccountDetails = SavingAccount::with('ssbMember')->where('id',$request['ssb_id'])->first();
    //         if($request['loan_emi_payment_mode'] == 0){
    //             if($ssbAccountDetails->balance < $request['deposite_amount']){
    //                 return back()->with('error', 'Insufficient balance in ssb account!');
    //             }
    //         }
    //         $mLoan = Grouploans::with('loanMember')->where('id',$request['loan_id'])->first();
    //         if($mLoan->emi_option == 1){
    //             $roi = $mLoan->due_amount*$mLoan->ROI/1200;
    //         }elseif($mLoan->emi_option == 2){
    //             $roi = $mLoan->due_amount*$mLoan->ROI/5200;
    //         }elseif($mLoan->emi_option == 3){
    //             $roi = $mLoan->due_amount*$mLoan->ROI/36500;
    //         }
    //         $principal_amount = $request['deposite_amount']-$roi;
    //         $amountArraySsb=array('1'=>$request['deposite_amount']);
    //         $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
    //         $dueAmount = $mLoan->due_amount-round($principal_amount);
    //         $glResult = Grouploans::find($request['loan_id']);
    //         $glData['credit_amount'] = $mLoan->credit_amount+round($principal_amount);
    //         $glData['due_amount'] = $dueAmount;
    //         if($dueAmount == 0){
    //             //$glData['status'] = 3;
    //         }
    //         $glData['received_emi_amount'] = $mLoan->received_emi_amount+$request['deposite_amount'];
    //         $glResult->update($glData);
    //         $gmLoan = Memberloans::with('loanMember')->where('id',$mLoan->member_loan_id)->first();
    //         $gmDueAmount = $gmLoan->due_amount-$principal_amount;
    //         $mlResult = Memberloans::find($mLoan->member_loan_id);
    //         $lData['credit_amount'] = $mLoan->credit_amount+$principal_amount;
    //         $lData['due_amount'] = $gmDueAmount;
    //         if($dueAmount == 0){
    //             //$lData['status'] = 3;
    //             //$lData['clear_date'] = date("Y-m-d", strtotime(convertDate($request['application_date'])));
    //         }
    //         $mlResult->update($lData);
    //         if($request['loan_emi_payment_mode'] == 0){
    //             $cheque_dd_no=NULL;
    //             $online_payment_id=NULL;
    //             $online_payment_by=NULL;
    //             $bank_name=NULL;
    //             $cheque_date=NULL;
    //             $account_number=NULL;
    //             $paymentMode=4;
    //             $ssbpaymentMode=3;
    //             $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //             $record1=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','<',date("Y-m-d", strtotime(convertDate($request['application_date']))))->orderby('id','desc')->first();
    //             $ssb['saving_account_id']=$ssbAccountDetails->id;
    //             $ssb['account_no']=$ssbAccountDetails->account_no;
    //             $ssb['opening_balance']=$record1->opening_balance-$request['deposite_amount'];
    //             $ssb['branch_id']=$branchId;
    //             $ssb['type']=9;
    //             $ssb['deposit']=0;
    //             $ssb['withdrawal']=$request['deposite_amount'];
    //             $ssb['description']='Loan EMI Payment';
    //             $ssb['currency_code']='INR';
    //             $ssb['payment_type']='DR';
    //             $ssb['payment_mode']=$ssbpaymentMode;
    //             $ssb['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //             $ssbAccountTran = SavingAccountTranscation::create($ssb);
    //             $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;
    //             // update saving account current balance
    //             $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
    //             $ssbBalance->balance=$request['ssb_account']-$request['deposite_amount'];
    //             $ssbBalance->save();
    //             $record2=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','>',date("Y-m-d", strtotime(convertDate($request['application_date']))))->get();
    //             foreach ($record2 as $key => $value) {
    //                 $nsResult = SavingAccountTranscation::find($value->id);
    //                 $nsResult['opening_balance']=$value->opening_balance-$request['deposite_amount'];
    //                 $nsResult['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //                 $sResult->update($nsResult);
    //             }
    //             $data['saving_account_transaction_id']=$ssb_transaction_id;
    //             $data['loan_id']=$request['loan_id'];
    //             $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //             $satRef = TransactionReferences::create($data);
    //             $satRefId = $satRef->id;
    //             $updateSsbDayBook = $this->updateSsbDayBookAmount($request['deposite_amount'],$request['ssb_account_number'],date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date']))));
    //             $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,1,$ssbAccountDetails->id,$ssbAccountDetails->member_id,$branchId,getBranchCode($branchId)->branch_code,$amountArraySsb,$paymentMode,$request['loan_associate_name'],$request['associate_member_id'],$ssbAccountDetails->account_no,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'DR');
    //             $totalbalance = $request['ssb_account']-$request['deposite_amount'];
    //             $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->member_investments_id,0,$ssbAccountDetails->member_id,$totalbalance,0,$request['deposite_amount'],'Withdrawal from SSB',$request['account_number'],$branchId,getBranchCode($branchId)->branch_code,$amountArraySsb,$paymentMode,$request['loan_associate_name'],$request['associate_member_id'],$ssbAccountDetails->account_no,$cheque_dd_no,NULL,NULL,$paymentDate,$online_payment_by,$online_payment_by,$ssbAccountDetails->id,'DR');
    //         }elseif($request['loan_emi_payment_mode'] == 1){
    //             if($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != ''){
    //                     $cheque_dd_no=$request['customer_cheque'];
    //                     $paymentMode=1;
    //                     $ssbpaymentMode=5;
    //                     $online_payment_id=NULL;
    //                     $online_payment_by=NULL;
    //                     $satRefId = NULL;
    //                     $bank_name=NULL;
    //                     $cheque_date=NULL;
    //                     $account_number=NULL;
    //                     $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //                 }elseif($request['bank_transfer_mode'] == 1){
    //                     $cheque_dd_no=NULL;
    //                     $paymentMode=3;
    //                     $ssbpaymentMode=5;
    //                     $online_payment_id=$request['utr_transaction_number'];
    //                     $online_payment_by=NULL;
    //                     $satRefId = NULL;
    //                     $bank_name=NULL;
    //                     $cheque_date=NULL;
    //                     $account_number=NULL;
    //                     $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //                 }
    //                 $ssb_transaction_id = '';
    //                 $ssb_account_id_from = '';
    //         }elseif($request['loan_emi_payment_mode'] == 2){
    //             $cheque_dd_no=NULL;
    //             $paymentMode=0;
    //             $ssbpaymentMode=0;
    //             $online_payment_id=NULL;
    //             $online_payment_by=NULL;
    //             $satRefId = NULL;
    //             $bank_name=NULL;
    //             $cheque_date=NULL;
    //             $account_number=NULL;
    //             $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //             $ssb_transaction_id = '';
    //             $ssb_account_id_from = '';
    //         }elseif($request['loan_emi_payment_mode'] == 3){
    //             $cheque_dd_no=$request['cheque_number'];
    //             $cheque_date=date("Y-m-d", strtotime(convertDate($request['application_date'])));
    //             $bank_name=$request['bank_name'];
    //             $account_number=$request['account_number'];
    //             $paymentMode=1;
    //             $ssbpaymentMode=1;
    //             $online_payment_id=NULL;
    //             $online_payment_by=NULL;
    //             $satRefId = NULL;
    //             $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //             $ssb_transaction_id = '';
    //             $ssb_account_id_from = '';
    //         }
    //         $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,9,$request['loan_id'],$mLoan->applicant_id,$branchId,getBranchCode($branchId)->branch_code,$amountArraySsb,$paymentMode,$request['loan_associate_name'],$request['associate_member_id'],$mLoan->account_number,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'CR');
    //         $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,9,$request['loan_id'],0,$mLoan->applicant_id,$dueAmount,$request['deposite_amount'],0,'Loan EMI deposite',$mLoan->account_number,$branchId,getBranchCode($branchId)->branch_code,$amountArraySsb,$paymentMode,$request['loan_associate_name'],$request['associate_member_id'],$mLoan->account_number,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_by,$online_payment_by,$ssbAccountDetails->id,'CR');
    //         if($request['loan_emi_payment_mode'] == 3){
    //             $checkData['type']=5;
    //             $checkData['branch_id']=$branchId;
    //             // $checkData['loan_id']=$request['loan_id'];
    //             $checkData['day_book_id']=$createDayBook;
    //             $checkData['cheque_id']=$cheque_dd_no;
    //             $checkData['status']=1;
    //             $checkData['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //             $ssbAccountTran = ReceivedChequePayment::create($checkData);
    //             $dataRC['status']=3;
    //             $receivedcheque = ReceivedCheque::find($cheque_dd_no);
    //             $receivedcheque->update($dataRC);
    //         }
    //         /*************** Head Implement ************/
    //         if($request['loan_emi_payment_mode'] == 0){
    //             $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    //             $v_no = "";
    //             for ($i = 0; $i < 10; $i++) {
    //                 $v_no .= $chars[mt_rand(0, strlen($chars)-1)];
    //             }
    //             $roidayBookRef = CommanTransactionsController::createBranchDayBookReference($roi+$principal_amount,$request['application_date']);
    //             $roibranchDayBook = CommanTransactionsController::branchDayBookNew($roidayBookRef,$branchId,5,55,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi+$principal_amount,$roi+$principal_amount,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','SSB A/C Cr '.($roi+$principal_amount).'','SSB A/C Cr '.($roi+$principal_amount).'','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$is_contra=NULL,$contra_id=NULL,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_transaction_id,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,31,5,524,$loanId,$ssb_transaction_id,$request['associate_member_id'],NULL,$mLoan->branch_id,$ssbAccountDetails->branch_id,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],NULL,NULL,$ssb_transaction_id,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,56,5,55,$loanId,$ssb_transaction_id,$request['associate_member_id'],NULL,$mLoan->branch_id,$ssbAccountDetails->branch_id,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],NULL,NULL,$ssb_transaction_id,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //             /*$roiallTransaction = $this->createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,55,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);
    //             $roiallTransaction = $this->createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,55,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
    //             $loan_head_id = 66;
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,$loan_head_id,5,55,$loanId,$ssb_transaction_id,$request['associate_member_id'],NULL,$mLoan->branch_id,$ssbAccountDetails->branch_id,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],NULL,NULL,$ssb_transaction_id,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,56,5,55,$loanId,$ssb_transaction_id,$request['associate_member_id'],NULL,$mLoan->branch_id,$ssbAccountDetails->branch_id,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],NULL,NULL,$ssb_transaction_id,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //             /*$principalallTransaction = $this->createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,55,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);
    //             $principalallTransaction = $this->createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,55,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
    //             $principalmemberTransaction = CommanTransactionsController::memberTransactionNew($roidayBookRef,5,55,$loanId,$ssb_transaction_id,$request['associate_member_id'],$mLoan->applicant_id,$branchId,$bank_id=NULL,$account_id=NULL,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_transaction_id,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //         }elseif($request['loan_emi_payment_mode'] == 2){
    //             $roidayBookRef = CommanTransactionsController::createBranchDayBookReference($roi+$principal_amount,$request['application_date']);
    //             $roibranchDayBook = CommanTransactionsController::branchDayBookNew($roidayBookRef,$branchId,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$roi+$principal_amount,$roi+$principal_amount,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','Cash A/C Dr '.($roi+$principal_amount).'','To '.$mLoan->account_number.' A/C Cr '.($roi+$principal_amount).'','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$is_contra=NULL,$contra_id=NULL,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,31,5,524,$loanId,$createDayBook,$request['associate_member_id'],NULL,$branchId,NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //             /*$roiallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,28,5,55,$loanId,$createDayBook,$request['associate_member_id'],NULL,$branchId,NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //             /*$roiallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
    //             $loan_head_id = 66;
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,$loan_head_id,5,55,$loanId,$createDayBook,$request['associate_member_id'],NULL,$branchId,NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //             /*$principalallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,28,5,55,$loanId,$createDayBook,$request['associate_member_id'],NULL,$branchId,NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //             /*$principalallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
    //             $principalmemberTransaction = CommanTransactionsController::memberTransactionNew($roidayBookRef,5,55,$loanId,$createDayBook,$request['associate_member_id'],$mLoan->applicant_id,$branchId,$bank_id=NULL,$account_id=NULL,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //             $createRoiBranchCash = $this->updateBranchCashFromBackDate($roi+$principal_amount,$branchId,date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date']))));
    //         }elseif($request['loan_emi_payment_mode'] == 1){
    //             $loan_head_id = 66;
    //             if($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != ''){
    //                 $payment_type = 1;
    //                 $cheque_type = 1;
    //                 $amount_from_id =$request['associate_member_id'];
    //                 $amount_from_name = getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name;
    //                 $cheque_no = $request['customer_cheque'];
    //                 $cheque_date = NULL;
    //                 $cheque_bank_from = $request['customer_bank_name'];
    //                 $cheque_bank_ac_from = $request['customer_bank_account_number'];
    //                 $cheque_bank_ifsc_from = $request['customer_ifsc_code'];
    //                 $cheque_bank_branch_from=NULL;
    //                 $cheque_bank_to=$request['company_bank'];
    //                 $cheque_bank_ac_to=$request['bank_account_number'];
    //                 $v_no=NULL;
    //                 $v_date=NULL;
    //                 $ssb_account_id_from=NULL;
    //                 $transction_no = NULL;
    //                 $transction_bank_from = NULL;
    //                 $transction_bank_ac_from = NULL;
    //                 $transction_bank_ifsc_from = NULL;
    //                 $transction_bank_branch_from = NULL;
    //                 $transction_bank_to = $request['company_bank'];
    //                 $transction_bank_ac_to = $request['bank_account_number'];
    //             }elseif($request['bank_transfer_mode'] == 1){
    //                 $payment_type = 2;
    //                 $cheque_type = NULL;
    //                 $amount_from_id =$request['associate_member_id'];
    //                 $amount_from_name = getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name;
    //                 $cheque_no = NULL;
    //                 $cheque_date = NULL;
    //                 $cheque_bank_from = NULL;
    //                 $cheque_bank_ac_from = NULL;
    //                 $cheque_bank_ifsc_from = NULL;
    //                 $cheque_bank_branch_from = NULL;
    //                 $cheque_bank_to = NULL;
    //                 $cheque_bank_ac_to = NULL;
    //                 $transction_no = $request['utr_transaction_number'];
    //                 $v_no=NULL;
    //                 $v_date=NULL;
    //                 $ssb_account_id_from=NULL;
    //                 $transction_bank_from = $request['customer_bank_name'];
    //                 $transction_bank_ac_from = $request['customer_bank_account_number'];
    //                 $transction_bank_ifsc_from = $request['customer_ifsc_code'];
    //                 $transction_bank_branch_from = $request['customer_branch_name'];
    //                 $transction_bank_to = $request['company_bank'];
    //                 $transction_bank_ac_to = $request['bank_account_number'];
    //             }
    //             $roidayBookRef = CommanTransactionsController::createBranchDayBookReference($roi+$principal_amount,date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date']))));
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,31,5,524,$loanId,$createDayBook,$request['associate_member_id'],NULL,NULL,NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,NULL,NULL,NULL,$cheque_type,NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,NULL,NULL,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_from,$branchId,$transction_bank_ac_to,getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,NULL,NULL,$transction_bank_to,$transction_bank_ac_to,getSamraddhBank($request['company_bank'])->bank_name,$transction_bank_ac_to,NULL,getSamraddhBankAccount($request['bank_account_number'])->ifsc_code,NULL,1,Auth::user()->id);
    //             /*$roiallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,5,55,$loanId,$createDayBook,$request['associate_member_id'],NULL,NULL,NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,NULL,NULL,NULL,$cheque_type,NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,NULL,NULL,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_from,$branchId,$transction_bank_ac_to,getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,NULL,NULL,$transction_bank_to,$transction_bank_ac_to,getSamraddhBank($request['company_bank'])->bank_name,$transction_bank_ac_to,NULL,getSamraddhBankAccount($request['bank_account_number'])->ifsc_code,NULL,1,Auth::user()->id);
    //             /*$allTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,$head5=NULL,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,$loan_head_id,5,55,$loanId,$createDayBook,$request['associate_member_id'],NULL,NULL,NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,NULL,NULL,NULL,$cheque_type,NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,NULL,NULL,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_from,$branchId,$transction_bank_ac_to,getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,NULL,NULL,$transction_bank_to,$transction_bank_ac_to,getSamraddhBank($request['company_bank'])->bank_name,$transction_bank_ac_to,NULL,getSamraddhBankAccount($request['bank_account_number'])->ifsc_code,NULL,1,Auth::user()->id);
    //             /*$principalallTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
    //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef,$branchId,NULL,NULL,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,5,55,$loanId,$createDayBook,$request['associate_member_id'],NULL,NULL,NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,NULL,NULL,NULL,$cheque_type,NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,NULL,NULL,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_from,$branchId,$transction_bank_ac_to,getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,NULL,NULL,$transction_bank_to,$transction_bank_ac_to,getSamraddhBank($request['company_bank'])->bank_name,$transction_bank_ac_to,NULL,getSamraddhBankAccount($request['bank_account_number'])->ifsc_code,NULL,1,Auth::user()->id);
    //             /*$allTransaction = CommanTransactionsController::createAllTransaction($roidayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,$head5=NULL,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
    //             $roibranchDayBook = CommanTransactionsController::branchDayBookNew($roidayBookRef,$branchId,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$roi+$principal_amount,$roi+$principal_amount,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','Online A/C Dr '.($roi+$principal_amount).'','To '.$mLoan->account_number.' A/C Cr '.($roi+$principal_amount).'','CR',$payment_type,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$is_contra=NULL,$contra_id=NULL,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //             $principalmemberTransaction = CommanTransactionsController::memberTransactionNew($roidayBookRef,5,55,$loanId,$createDayBook,$request['associate_member_id'],$mLoan->applicant_id,$branchId,$bank_id=NULL,$account_id=NULL,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','DR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$request['application_date'],$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //             $samraddhBankDaybook = CommanTransactionsController::samraddhBankDaybookNew($roidayBookRef,$bank_id=NULL,$account_id=NULL,5,55,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$roi+$principal_amount,$roi+$principal_amount,$roi+$principal_amount,'Loan Panelty Charge','Online A/C Cr. '.($roi+$principal_amount).'','Online A/C Cr. '.($roi+$principal_amount).'','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,getSamraddhBank($request['company_bank'])->bank_name,$transction_bank_ac_to,NULL,getSamraddhBankAccount($request['bank_account_number'])->ifsc_code,$request['application_date'],$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL);
    //             $createPrincipleBankClosing = CommanTransactionsController::updateBackDateloanBankBalance($roi+$principal_amount,$request['company_bank'],getSamraddhBankAccount($request['bank_account_number'])->id,date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date']))),0,0);
    //         }
    //         /*************** Head Implement ************/
    //         /*---------- commission script  start  ---------*/
    //         $daybookId=$createDayBook;
    //         $total_amount=$request['deposite_amount'];
    //         $percentage=2;
    //         $month=NULL;
    //         $type_id=$request['loan_id'];
    //         $type=7;
    //         $associate_id=$request['associate_member_id'];
    //         $branch_id=$branchId;
    //         $commission_type=0;
    //         $associateDetail=Member::where('id',$associate_id)->first();
    //         $carder=$associateDetail->current_carder_id;
    //         $associate_exist=0;
    //         $percentInDecimal = $percentage / 100;
    //         $commission_amount = round($percentInDecimal * $total_amount,4);
    //         $associateCommission['member_id'] = $associate_id;
    //         $associateCommission['branch_id'] = $branch_id;
    //         $associateCommission['type'] = $type;
    //         $associateCommission['type_id'] = $type_id;
    //         $associateCommission['day_book_id'] = $daybookId;
    //         $associateCommission['total_amount'] = $total_amount;
    //         $associateCommission['month'] = $month;
    //         $associateCommission['commission_amount'] = $commission_amount;
    //         $associateCommission['percentage'] = $percentage;
    //         $associateCommission['commission_type'] = $commission_type;
    //         $date =\App\Models\Daybook::where('id',$daybookId)->first();
    //         $associateCommission['created_at'] = $date->created_at;
    //         $associateCommission['pay_type'] = 4;
    //         $associateCommission['carder_id'] = $carder;
    //         $associateCommission['associate_exist'] = $associate_exist;
    //         $associateCommissionInsert = \App\Models\CommissionEntryLoan::create($associateCommission);
    //         // Collection start
    //       /*  $collection_percentage=5;
    //         $collection_type=8;
    //         $Collection_associate_id=$mLoan->associate_member_id;
    //         $collection_associateDetail=Member::where('id',$Collection_associate_id)->first();
    //         $Collection_carder=$collection_associateDetail->current_carder_id;
    //         $collection_percentInDecimal = $collection_percentage / 100;
    //         $collection_commission_amount = round($collection_percentInDecimal * $total_amount,4);
    //         $coll_associateCommission['member_id'] = $Collection_associate_id;
    //         $coll_associateCommission['branch_id'] = $branch_id;
    //         $coll_associateCommission['type'] = $collection_type;
    //         $coll_associateCommission['type_id'] = $type_id;
    //         $coll_associateCommission['day_book_id'] = $daybookId;
    //         $coll_associateCommission['total_amount'] = $total_amount;
    //         $coll_associateCommission['month'] = $month;
    //         $coll_associateCommission['commission_amount'] = $collection_commission_amount;
    //         $coll_associateCommission['percentage'] = $collection_percentage;
    //         $coll_associateCommission['commission_type'] = $commission_type;
    //         $coll_date =\App\Models\Daybook::where('id',$daybookId)->first();
    //         $coll_associateCommission['created_at'] = $coll_date->created_at;
    //         $coll_associateCommission['pay_type'] = 4;
    //         $coll_associateCommission['carder_id'] = $Collection_carder;
    //         $coll_associateCommission['associate_exist'] = $associate_exist;
    //         $coll_associateCommissionInsert = \App\Models\CommissionEntryLoan::create($coll_associateCommission);*/
    //         /*---------- commission script  end  ---------*/
    //         $createLoanDayBook = CommanTransactionsController::createLoanDayBook($daybookId,$mLoan->loan_type,0,$mLoan->id,$lId=NULL,$mLoan->account_number,$mLoan->applicant_id,$roi,$principal_amount,$dueAmount,$request['deposite_amount'],'Loan EMI deposite',$branchId,getBranchCode($branchId)->branch_code,'CR','INR',$paymentMode,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,1,1,$cheque_date,$account_number,NULL,$request['loan_associate_name'],$request['associate_member_id'],$branchId);
    //         if($request['penalty_amount'] != '' && $request['penalty_amount'] > 0){
    //             $amountArray=array('1'=>$request['penalty_amount']);
    //             if($request['loan_emi_payment_mode'] == 0){
    //                 $cheque_dd_no=NULL;
    //                 $online_payment_id=NULL;
    //                 $online_payment_by=NULL;
    //                 $bank_name=NULL;
    //                 $cheque_date=NULL;
    //                 $account_number=NULL;
    //                 $paymentMode=4;
    //                 $ssbpaymentMode=3;
    //                 $paymentDate = date("Y-m-d", strtotime(convertDate($request['application_date'])));
    //                 $ssb['saving_account_id']=$ssbAccountDetails->id;
    //                 $ssb['account_no']=$ssbAccountDetails->account_no;
    //                 $ssb['opening_balance']=$request['ssb_account']-$request['penalty_amount'];
    //                 $ssb['deposit']=0;
    //                 $ssb['withdrawal']=$request['penalty_amount'];
    //                 $ssb['description']='Loan EMI Penalty';
    //                 $ssb['currency_code']='INR';
    //                 $ssb['payment_type']='DR';
    //                 $ssb['payment_mode']=$ssbpaymentMode;
    //                 $ssb['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //                 $ssbAccountTran = SavingAccountTranscation::create($ssb);
    //                 $ssb_transaction_id = $ssbAccountTran->id;
    //                 // update saving account current balance
    //                 $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
    //                 $ssbBalance->balance=$request['ssb_account']-$request['penalty_amount'];
    //                 $ssbBalance->save();
    //                 $data['saving_account_transaction_id']=$ssb_transaction_id;
    //                 $data['loan_id']=$request['loan_id'];
    //                 $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //                 $satRef = TransactionReferences::create($data);
    //                 $satRefId = $satRef->id;
    //                 $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,1,$ssbAccountDetails->id,$ssbAccountDetails->member_id,$branchId,getBranchCode($branchId)->branch_code,$amountArraySsb,$paymentMode,$request['loan_associate_name'],$request['associate_member_id'],$ssbAccountDetails->account_no,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'DR');
    //                 $totalbalance = $request['ssb_account']-$request['penalty_amount'];
    //                 $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->member_investments_id,0,$ssbAccountDetails->member_id,$totalbalance,0,$request['penalty_amount'],'Withdrawal from SSB',$request['account_number'],$branchId,getBranchCode($branchId)->branch_code,$amountArraySsb,$paymentMode,$request['loan_associate_name'],$request['associate_member_id'],$ssbAccountDetails->account_no,$cheque_dd_no,NULL,NULL,$paymentDate,$online_payment_by,$online_payment_by,$ssbAccountDetails->id,'DR');
    //             }elseif($request['loan_emi_payment_mode'] == 1){
    //                 if($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != ''){
    //                     $paymentMode = 1;
    //                     $cheque_dd_no = $request['customer_cheque'];
    //                     $ssbpaymentMode=5;
    //                     $online_payment_id = NULL;
    //                     $online_payment_by=NULL;
    //                     $satRefId = NULL;
    //                     $bank_name=NULL;
    //                     $cheque_date=NULL;
    //                     $account_number=NULL;
    //                     $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));;
    //                 }elseif($request['bank_transfer_mode'] == 1){
    //                     $paymentMode = 2;
    //                     $cheque_dd_no = NULL;
    //                     $ssbpaymentMode=5;
    //                     $online_payment_id = $request['utr_transaction_number'];
    //                     $online_payment_by=NULL;
    //                     $satRefId = NULL;
    //                     $bank_name=NULL;
    //                     $cheque_date=NULL;
    //                     $account_number=NULL;
    //                     $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));;
    //                 }
    //                 $ssb_transaction_id = '';
    //             }elseif($request['loan_emi_payment_mode'] == 2){
    //                 $cheque_dd_no=NULL;
    //                 $paymentMode=0;
    //                 $ssbpaymentMode=0;
    //                 $online_payment_id=NULL;
    //                 $online_payment_by=NULL;
    //                 $satRefId = NULL;
    //                 $bank_name=NULL;
    //                 $cheque_date=NULL;
    //                 $account_number=NULL;
    //                 $paymentDate = date("Y-m-d", strtotime(convertDate($request['application_date'])));
    //                 $ssb_transaction_id = '';
    //             }
    //             $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,11,$request['loan_id'],$mLoan->applicant_id,$branchId,getBranchCode($branchId)->branch_code,$amountArray,$paymentMode,$request['loan_associate_name'],$request['associate_member_id'],$mLoan->account_number,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'CR');
    //             $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,11,$request['loan_id'],0,$mLoan->applicant_id,$dueAmount,$request['penalty_amount'],0,'Loan EMI penalty',$mLoan->account_number,$branchId,getBranchCode($branchId)->branch_code,$amountArray,$paymentMode,$request['loan_associate_name'],$request['associate_member_id'],$mLoan->account_number,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_by,$online_payment_by,$ssbAccountDetails->id,'CR');
    //             if($request['loan_emi_payment_mode'] == 3){
    //                 $checkData['type']=5;
    //                 $checkData['branch_id']=$branchId;
    //                 // $checkData['loan_id']=$request['loan_id'];
    //                 $checkData['day_book_id']=$createDayBook;
    //                 $checkData['cheque_id']=$cheque_dd_no;
    //                 $checkData['status']=1;
    //                 $checkData['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));
    //                 $ssbAccountTran = ReceivedChequePayment::create($checkData);
    //                 $dataRC['status']=3;
    //                 $receivedcheque = ReceivedCheque::find($cheque_dd_no);
    //                 $receivedcheque->update($dataRC);
    //             }
    //             /************** Head Implement *************/
    //             if($request['loan_emi_payment_mode'] == 0){
    //                 $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    //                 $v_no = "";
    //                 for ($i = 0; $i < 10; $i++) {
    //                     $v_no .= $chars[mt_rand(0, strlen($chars)-1)];
    //                 }
    //                 $penaltyDayBookRef = CommanTransactionsController::createBranchDayBookReference($request['penalty_amount'],$request['application_date']);
    //                 $roibranchDayBook = CommanTransactionsController::branchDayBookNew($penaltyDayBookRef,$branchId,5,56,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','SSB A/C Cr '.$request['penalty_amount'].'','SSB A/C Cr '.$request['penalty_amount'].'','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$is_contra=NULL,$contra_id=NULL,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_transaction_id,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //                 $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef,$branchId,NULL,NULL,33,5,56,$loanId,$ssb_transaction_id,$request['associate_member_id'],NULL,$mLoan->branch_id,$ssbAccountDetails->branch_id,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],NULL,NULL,$ssb_transaction_id,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //                 /*$roiallTransaction = $this->createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,56,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
    //                 $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef,$branchId,NULL,NULL,56,5,56,$loanId,$ssb_transaction_id,$request['associate_member_id'],NULL,$mLoan->branch_id,$ssbAccountDetails->branch_id,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],NULL,NULL,$ssb_transaction_id,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //                 /*$roiallTransaction = $this->createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,56,$loanId,$ssb_transaction_id,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_transaction_id);*/
    //                 $roimemberTransaction = CommanTransactionsController::memberTransactionNew($penaltyDayBookRef,5,56,$loanId,$ssb_transaction_id,$request['associate_member_id'],$mLoan->applicant_id,$branchId,$bank_id=NULL,$account_id=NULL,$request['penalty_amount'],'Loan Panelty Charge','DR',3,'INR',$branchId,getBranchDetail($branchId)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['application_date']))),$request['ssb_id'],$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_transaction_id,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //             }elseif($request['loan_emi_payment_mode'] == 2){
    //                 $penaltyDayBookRef = CommanTransactionsController::createBranchDayBookReference($request['penalty_amount'],$request['application_date']);
    //                 $roibranchDayBook = CommanTransactionsController::branchDayBookNew($penaltyDayBookRef,$branchId,5,56,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','Cash A/C Dr '.$request['penalty_amount'].'','To '.$mLoan->account_number.' A/C Cr '.$request['penalty_amount'].'','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$is_contra=NULL,$contra_id=NULL,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //                 $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef,$branchId,NULL,NULL,33,5,56,$loanId,$createDayBook,$request['associate_member_id'],NULL,$branchId,NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //                 /*$roiallTransaction = CommanTransactionsController::createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,56,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
    //                 $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef,$branchId,NULL,NULL,28,5,56,$loanId,$createDayBook,$request['associate_member_id'],NULL,$branchId,NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','DR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$jv_unique_id=NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,Auth::user()->id);
    //                 /*$roiallTransaction = CommanTransactionsController::createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,56,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$request['application_date']);*/
    //                 $roimemberTransaction = CommanTransactionsController::memberTransactionNew($penaltyDayBookRef,5,56,$loanId,$createDayBook,$request['associate_member_id'],$mLoan->applicant_id,$branchId,$bank_id=NULL,$account_id=NULL,$request['penalty_amount'],'Loan Panelty Charge','DR',0,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //                 $createRoiBranchCash = $this->updateBranchCashFromBackDate($request['penalty_amount'],$branchId,date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date']))));
    //             }elseif($request['loan_emi_payment_mode'] == 1){
    //                 if($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != ''){
    //                     $payment_type = 1;
    //                     $cheque_type = 1;
    //                     $amount_from_id =$request['associate_member_id'];
    //                     $amount_from_name = getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name;
    //                     $cheque_no = $request['customer_cheque'];
    //                     $cheque_date = NULL;
    //                     $cheque_bank_from = $request['customer_bank_name'];
    //                     $cheque_bank_ac_from = $request['customer_bank_account_number'];
    //                     $cheque_bank_ifsc_from = $request['customer_ifsc_code'];
    //                     $cheque_bank_branch_from=NULL;
    //                     $cheque_bank_to=$request['company_bank'];
    //                     $cheque_bank_ac_to=$request['bank_account_number'];
    //                     $v_no=NULL;
    //                     $v_date=NULL;
    //                     $ssb_account_id_from=NULL;
    //                     $transction_no = NULL;
    //                     $transction_bank_from = NULL;
    //                     $transction_bank_ac_from = NULL;
    //                     $transction_bank_ifsc_from = NULL;
    //                     $transction_bank_branch_from = NULL;
    //                     $transction_bank_to = NULL;
    //                     $transction_bank_ac_to = NULL;
    //                 }elseif($request['bank_transfer_mode'] == 1){
    //                     $payment_type = 2;
    //                     $cheque_type = NULL;
    //                     $amount_from_id =$request['associate_member_id'];
    //                     $amount_from_name = getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name;
    //                     $cheque_no = NULL;
    //                     $cheque_date = NULL;
    //                     $cheque_bank_from = NULL;
    //                     $cheque_bank_ac_from = NULL;
    //                     $cheque_bank_ifsc_from = NULL;
    //                     $cheque_bank_branch_from = NULL;
    //                     $cheque_bank_to = NULL;
    //                     $cheque_bank_ac_to = NULL;
    //                     $transction_no = $request['utr_transaction_number'];
    //                     $v_no=NULL;
    //                     $v_date=NULL;
    //                     $ssb_account_id_from=NULL;
    //                     $transction_bank_from = $request['customer_bank_name'];
    //                     $transction_bank_ac_from = $request['customer_bank_account_number'];
    //                     $transction_bank_ifsc_from = $request['customer_ifsc_code'];
    //                     $transction_bank_branch_from = $request['customer_branch_name'];
    //                     $transction_bank_to = $request['company_bank'];
    //                     $transction_bank_ac_to = $request['bank_account_number'];
    //                 }
    //                 $penaltyDayBookRef = CommanController::createBranchDayBookReference($request['penalty_amount'],date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date']))));
    //                 $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef,$branchId,NULL,NULL,33,5,56,$loanId,$createDayBook,$request['associate_member_id'],NULL,NULL,NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,NULL,NULL,NULL,$cheque_type,NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,NULL,NULL,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_from,$branchId,$transction_bank_ac_to,getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,NULL,NULL,$transction_bank_to,$transction_bank_ac_to,getSamraddhBank($request['company_bank'])->bank_name,$transction_bank_ac_to,NULL,getSamraddhBankAccount($request['bank_account_number'])->ifsc_code,NULL,1,Auth::user()->id);
    //                 /*$roiallTransaction = CommanController::createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,56,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
    //                 $allHeadTransaction = $this->createAllHeadTransaction($penaltyDayBookRef,$branchId,NULL,NULL,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,5,56,$loanId,$createDayBook,$request['associate_member_id'],NULL,NULL,NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','DR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,NULL,NULL,NULL,$cheque_type,NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,NULL,NULL,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_from,$branchId,$transction_bank_ac_to,getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,NULL,NULL,$transction_bank_to,$transction_bank_ac_to,getSamraddhBank($request['company_bank'])->bank_name,$transction_bank_ac_to,NULL,getSamraddhBankAccount($request['bank_account_number'])->ifsc_code,NULL,1,Auth::user()->id);
    //                 /*$allTransaction = CommanController::createAllTransaction($penaltyDayBookRef,$branchId,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request['bank_account_number'])->account_head_id,$head5=NULL,5,56,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$created_at=NULL);*/
    //                 $roibranchDayBook = CommanTransactionsController::branchDayBookNew($penaltyDayBookRef,$branchId,5,56,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$branch_id_from=NULL,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','Cash A/C Dr '.$request['penalty_amount'].'','To '.$mLoan->account_number.' A/C Cr '.$request['penalty_amount'].'','CR',$payment_type,'INR',$branchId,getBranchDetail($branchId)->name,$request['associate_member_id'],getMemberData($request['associate_member_id'])->first_name.' '.getMemberData($request['associate_member_id'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$request['application_date'],$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$is_contra=NULL,$contra_id=NULL,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //                 $principalmemberTransaction = CommanController::memberTransactionNew($penaltyDayBookRef,5,56,$loanId,$createDayBook,$request['associate_member_id'],$mLoan->applicant_id,$branchId,$bank_id=NULL,$account_id=NULL,$request['penalty_amount'],'Loan Panelty Charge','DR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$request['application_date'],$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    //                 $samraddhBankDaybook = CommanTransactionsController::samraddhBankDaybookNew($penaltyDayBookRef,$bank_id=NULL,$account_id=NULL,5,56,$loanId,$createDayBook,$request['associate_member_id'],$member_id=NULL,$branchId,$request['penalty_amount'],$request['penalty_amount'],$request['penalty_amount'],'Loan Panelty Charge','Cash A/C Cr. '.$request['penalty_amount'].'','Cash A/C Cr. '.$request['penalty_amount'].'','CR',$payment_type,'INR',$request['company_bank'],getSamraddhBank($request['company_bank'])->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$request['application_date'],$request['application_date'],$entry_time=NULL,2,Auth::user()->id,$request['application_date'],$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL);
    //                 $createPrincipleBankClosing = CommanController::updateBackDateloanBankBalance($request['penalty_amount'],$request['company_bank'],getSamraddhBankAccount($request['bank_account_number'])->id,date("Y-m-d H:i:s", strtotime(convertDate($request['application_date']))),0,0);
    //             }
    //             /************** Head Implement *************/
    //             /*---------- penalty commission script  start  ---------*/
    //            /* $daybookId=$createDayBook;
    //             $total_amount=$request['penalty_amount'];
    //             $month=NULL;
    //             $type_id=$request['loan_id'];
    //             $branch_id=$branchId;
    //             $associate_exist=0;
    //             $commission_type=0;
    //             // Collection start
    //             $collection_percentage=5;
    //             $collection_type=8;
    //             $Collection_associate_id=$mLoan->associate_member_id;
    //             $collection_associateDetail=Member::where('id',$Collection_associate_id)->first();
    //             $Collection_carder=$collection_associateDetail->current_carder_id;
    //             $collection_percentInDecimal = $collection_percentage / 100;
    //             $collection_commission_amount = round($collection_percentInDecimal * $total_amount,4);
    //             $coll_associateCommission['member_id'] = $Collection_associate_id;
    //             $coll_associateCommission['branch_id'] = $branch_id;
    //             $coll_associateCommission['type'] = $collection_type;
    //             $coll_associateCommission['type_id'] = $type_id;
    //             $coll_associateCommission['day_book_id'] = $daybookId;
    //             $coll_associateCommission['total_amount'] = $total_amount;
    //             $coll_associateCommission['month'] = $month;
    //             $coll_associateCommission['commission_amount'] = $collection_commission_amount;
    //             $coll_associateCommission['percentage'] = $collection_percentage;
    //             $coll_associateCommission['commission_type'] = $commission_type;
    //             $coll_date =\App\Models\Daybook::where('id',$daybookId)->first();
    //             $coll_associateCommission['created_at'] = $coll_date->created_at;
    //             $coll_associateCommission['pay_type'] = 4;
    //             $coll_associateCommission['carder_id'] = $Collection_carder;
    //             $coll_associateCommission['associate_exist'] = $associate_exist;
    //             $coll_associateCommissionInsert = \App\Models\CommissionEntryLoan::create($coll_associateCommission);*/
    //             /*---------- commission script  end  ---------*/
    //             if($request['loan_emi_payment_mode'] == 0){
    //                 $paymentMode = 4;
    //                 $cheque_dd_no = NULL;
    //                 $ssbpaymentMode=5;
    //                 $online_payment_id = NULL;
    //                 $online_payment_by=NULL;
    //                 $satRefId = NULL;
    //                 $bank_name=NULL;
    //                 $cheque_date=NULL;
    //                 $account_number=NULL;
    //                 $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));;
    //             }elseif($request['loan_emi_payment_mode'] == 1){
    //                 if($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != ''){
    //                     $paymentMode = 1;
    //                     $cheque_dd_no = $request['customer_cheque'];
    //                     $ssbpaymentMode=5;
    //                     $online_payment_id = NULL;
    //                     $online_payment_by=NULL;
    //                     $satRefId = NULL;
    //                     $bank_name=NULL;
    //                     $cheque_date=NULL;
    //                     $account_number=NULL;
    //                     $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));;
    //                 }elseif($request['bank_transfer_mode'] == 1){
    //                     $paymentMode = 2;
    //                     $cheque_dd_no = NULL;
    //                     $ssbpaymentMode=5;
    //                     $online_payment_id = $request['utr_transaction_number'];
    //                     $online_payment_by=NULL;
    //                     $satRefId = NULL;
    //                     $bank_name=NULL;
    //                     $cheque_date=NULL;
    //                     $account_number=NULL;
    //                     $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));;
    //                 }
    //             }elseif($request['loan_emi_payment_mode'] == 2){
    //                 $cheque_dd_no=NULL;
    //                 $paymentMode=0;
    //                 $ssbpaymentMode=0;
    //                 $online_payment_id=NULL;
    //                 $online_payment_by=NULL;
    //                 $satRefId = NULL;
    //                 $bank_name=NULL;
    //                 $cheque_date=NULL;
    //                 $account_number=NULL;
    //                 $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['application_date'])));;
    //             }
    //             $createLoanDayBook = CommanTransactionsController::createLoanDayBook($createDayBook,$mLoan->loan_type,1,$mLoan->id,$lId=NULL,$mLoan->account_number,$mLoan->applicant_id,$request['penalty_amount'],$request['penalty_amount'],$opening_balance=NULL,$request['penalty_amount'],'Loan EMI penalty',$branchId,getBranchCode($branchId)->branch_code,'CR','INR',$paymentMode,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,1,1,$cheque_date,$account_number,NULL,$request['loan_associate_name'],$request['associate_member_id'],$branchId);
    //         }
    //     DB::commit();
    //     } catch (\Exception $ex) {
    //         DB::rollback();
    //         return back()->with('alert', $ex->getMessage());
    //     }
    //     return back()->with('success', 'Loan EMI Successfully submitted!');
    // }
    /**
     * Print loan form details.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadLoanForm($id, $type)
    {
        $data['loanDetails'] = Memberloans::with('loan', 'loanMember', 'loanMemberBankDetails', 'loanMemberIdProofs', 'LoanApplicants', 'LoanCoApplicants', 'LoanGuarantor', 'Loanotherdocs', 'GroupLoanMembers', 'loanInvestmentPlans')->findOrFail($id);
        $data['loanDetails'] = array(
            ''
        );
        return view('templates.admin.loan.personalAndEmployDetail', $data);
    }
    public function update_pdf_generate_status(Request $request)
    {
        $id = $request->id;
        $status = Memberloans::where('id', $id)->update(['pdf_generate_status' => 1]);
        $return_array = compact('status');
        return json_encode($return_array);
    }
    public function update_nodues_print_status(Request $request)
    {
        $id = $request->id;
        $status = Memberloans::where('id', $id)->update(['no_dues_print' => 1]);
        $return_array = compact('status');
        return json_encode($return_array);
    }
    /**
     * Print loan form details.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function printLoanForm($id, $type)
    {
        if (!in_array('Loan Print PDF form', auth()->user()
            ->getPermissionNames()
            ->toArray()))
        {
            return redirect()
                ->route('branch.dashboard');
        }
        $data['loanDetails'] = array(
            ''
        );
        $data['loanDetails'] = Memberloans::with('loan', 'LoanApplicants', 'loanMember', 'loanMemberBankDetails', 'loanMemberIdProofs', 'LoanApplicants', 'LoanCoApplicants', 'LoanGuarantor', 'Loanotherdocs', 'GroupLoanMembers', 'loanInvestmentPlans')->findOrFail($id);
        $data['EMI'] = [];
        if ($type != 3)
        {
            $data['EMI'] = LoanDayBooks::where('loan_type', $type)->where('loan_id', $id)->get()
                ->toArray();
        }
        else
        {
            $data['EMI'] = LoanDayBooks::where('loan_type', $type)->where('group_loan_id', $id)->get()
                ->toArray();
        }
        /*echo '<pre>';
        print_r($data['loanDetails']->toArray());
         exit;*/
        /* $data['loanDetails'] = Memberloans::with('loan','LoanApplicants','LoanCoApplicants','LoanGuarantor','Loanotherdocs','GroupLoanMembers','loanInvestmentPlans')->findOrFail($id);*/
        /*loan_guarantor*/
        /* echo '<pre>';
        print_r($data['loanDetails']->toArray());
         exit;  */
        $data['loanDetails'] = $data['loanDetails']->toArray();
        //$data['loan_guarantor'][0]['educational_qualification_exam_name']='';
        /*if(isset($data['loan_guarantor'][0]['educational_qualification']) && $data['loan_guarantor'][0]['educational_qualification'] >0)
          {
        $loan_guarantor=\App\Models\EmployeeQualification::where([['id', '=', $data['loan_guarantor'][0]['educational_qualification']],['status', '=', '1'],['is_deleted', '=', '0'],])->get(['id','name']);
        }*/
        /*loan_guarantor*/
        /*echo '<pre>';
        print_r($data['loanDetails']->toArray());
        exit; */
        return view('templates.branch.loan_management.personalAndEmployDetail', $data);
    }
    /**
     * Show loan  commission list.
     * Route: /loan
     * Method: get
     * @return  view
     */
    public function loanCommission($id)
    {
        if (!in_array('Loan Commission', auth()->user()
            ->getPermissionNames()
            ->toArray()))
        {
            return redirect()
                ->route('branch.dashboard');
        }
        $data['title'] = 'Loan Commission Detail | Listing';
        // $data['plans'] = Plans::where('status',1)->get();
        $data['loan'] = Memberloans::where('id', $id)->first();
        return view('templates.branch.loan_management.commissionDetailLoan', $data);
    }
    /**
     * Get loan  commission list
     * Route: ajax call from - /branch/loan
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function loanCommissionList(Request $request)
    {
        if ($request->ajax())
        {
            $arrFormData = array();
            if (!empty($_POST['searchform']))
            {
                foreach ($_POST['searchform'] as $frm_data)
                {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = \App\Models\AssociateCommission::where('type_id', $arrFormData['id'])->whereIn('type', array(
                4,
                6
            ))
                ->where('status', 1);
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
            {
                if ($arrFormData['start_date'] != '')
                {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '')
                    {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    }
                    else
                    {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)') , [$startDate, $endDate]);
                }
                if (isset($arrFormData['plan_id']) && $arrFormData['plan_id'] != '')
                {
                    $meid = $arrFormData['plan_id'];
                    $data = $data->whereHas('investment', function ($query) use ($meid)
                    {
                        $query->where('member_investments.plan_id', $meid);
                    });
                }
            }
            $count = $data->orderby('id', 'DESC')
                ->count();
            // $count=count($data1);
            $data = $data->orderby('id', 'DESC')
                ->offset($_POST['start'])->limit($_POST['length'])->get();
            $totalCount = \App\Models\AssociateCommission::where('type_id', $arrFormData['id'])->whereIn('type', array(
                4,
                6,
                7,
                8
            ))
                ->where('status', 1)
                ->count();
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $val)
            {
                $sno++;
                $row['DT_RowIndex'] = $sno;
                $row['investment_account'] = getSeniorData($val->member_id, 'first_name') . ' ' . getSeniorData($val->member_id, 'last_name');
                $row['plan_name'] = getSeniorData($val->member_id, 'associate_no');
                $row['total_amount'] = $val->total_amount;
                $row['commission_amount'] = $val->commission_amount;
                $row['percentage'] = $val->percentage;
                $carder_name = getCarderName($val->carder_id);
                $row['carder_name'] = $carder_name;
                $commission_for = '';
                if ($val->type == 4)
                {
                    $commission_for = 'Loan Commission';
                }
                if ($val->type == 6)
                {
                    $commission_for = 'Loan Collection';
                }
                if ($val->type == 7)
                {
                    $commission_for = 'Group Loan Commission';
                }
                if ($val->type == 8)
                {
                    $commission_for = 'Group Loan Collection';
                }
                $row['commission_type'] = $commission_for;
                $pay_type = '';
                if ($val->pay_type == 4)
                {
                    $pay_type = 'Loan Emi';
                }
                elseif ($val->pay_type == 5)
                {
                    $pay_type = 'Loan Panelty';
                }
                $row['pay_type'] = $pay_type;
                if ($val->is_distribute == 1)
                {
                    $is_distribute = 'Yes';
                }
                else
                {
                    $is_distribute = 'No';
                }
                $row['is_distribute'] = $is_distribute;
                $created_at = date("d/m/Y", strtotime($val->created_at));
                $row['created_at'] = $created_at;
                $rowReturn[] = $row;
            }
            // echo 'hi';
            //  print_r($rowReturn);die;
            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $totalCount,
                "recordsFiltered" => $count,
                "data" => $rowReturn,
            );
            return json_encode($output);
        }
    }
    /**
     * Show loan  commission list.
     * Route: /loan
     * Method: get
     * @return  view
     */
    public function loanGroupCommission($id)
    {
        if (!in_array('Group Loan Commission', auth()->user()
            ->getPermissionNames()
            ->toArray()))
        {
            return redirect()
                ->route('branch.dashboard');
        }
        $data['title'] = 'Loan Commission Detail | Listing';
        // $data['plans'] = Plans::where('status',1)->get();
        $data['loan'] = Grouploans::where('id', $id)->first();
        return view('templates.branch.loan_management.commissionDetailLoanGroup', $data);
    }
    /**
     * Get loan  commission list
     * Route: ajax call from - /branch/loan
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function loanGroupCommissionList(Request $request)
    {
        if ($request->ajax())
        {
            $arrFormData = array();
            if (!empty($_POST['searchform']))
            {
                foreach ($_POST['searchform'] as $frm_data)
                {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = \App\Models\AssociateCommission::where('type_id', $arrFormData['id'])->whereCompany_id()->whereIn('type', array(
                7,
                8
            ))
                ->where('status', 1);

            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
            {
                if ($arrFormData['start_date'] != '')
                {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '')
                    {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    }
                    else
                    {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)') , [$startDate, $endDate]);
                }
                if (isset($arrFormData['plan_id']) && $arrFormData['plan_id'] != '')
                {
                    $meid = $arrFormData['plan_id'];
                    $data = $data->whereHas('investment', function ($query) use ($meid)
                    {
                        $query->where('member_investments.plan_id', $meid);
                    });
                }
            }
            $count = $data->orderby('id', 'DESC')
                ->count();
            // $count=count($data1);
            $data = $data->orderby('id', 'DESC')
                ->offset($_POST['start'])->limit($_POST['length'])->get();
            $totalCount = \App\Models\AssociateCommission::where('type_id', $arrFormData['id'])->whereIn('type', array(
                7,
                8
            ))
                ->where('status', 1)
                ->count();
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $val)
            {
                $sno++;
                $row['DT_RowIndex'] = $sno;
                $row['investment_account'] = getSeniorData($val->member_id, 'first_name') . ' ' . getSeniorData($val->member_id, 'last_name');
                $row['plan_name'] = getSeniorData($val->member_id, 'associate_no');
                $row['total_amount'] = $val->total_amount;
                $row['commission_amount'] = $val->commission_amount;
                $row['percentage'] = $val->percentage;
                $carder_name = getCarderName($val->carder_id);
                $row['carder_name'] = $carder_name;
                $commission_for = '';
                if ($val->type == 4)
                {
                    $commission_for = 'Loan Commission';
                }
                if ($val->type == 6)
                {
                    $commission_for = 'Loan Collection ';
                }
                if ($val->type == 7)
                {
                    $commission_for = 'Group Loan Commission';
                }
                if ($val->type == 8)
                {
                    $commission_for = 'Group Loan Collection';
                }
                $row['commission_type'] = $commission_for;
                $pay_type = '';
                if ($val->pay_type == 4)
                {
                    $pay_type = 'Loan Emi';
                }
                elseif ($val->pay_type == 5)
                {
                    $pay_type = 'Loan Panelty';
                }
                $row['pay_type'] = $pay_type;
                if ($val->is_distribute == 1)
                {
                    $is_distribute = 'Yes';
                }
                else
                {
                    $is_distribute = 'No';
                }
                $row['is_distribute'] = $is_distribute;
                $created_at = date("d/m/Y", strtotime($val->created_at));
                $row['created_at'] = $created_at;
                $rowReturn[] = $row;
            }
            // echo 'hi';
            //  print_r($rowReturn);die;
            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $totalCount,
                "recordsFiltered" => $count,
                "data" => $rowReturn,
            );
            return json_encode($output);
        }
    }
    /**
     * Display loan details.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function printView($id, $type)
    {
        $data['loanDetails'] = Memberloans::with('loan')->findOrFail($id);
        if (!empty($data['loanDetails']))
        {
            $loanDetails = $data['loanDetails']->toArray();
            $loan_type = $loanDetails['loan_type'];
            //if($loan_type==3){
            if ($type != 3)
            {
                if (!in_array('Loan Print PDF form', auth()->user()
                    ->getPermissionNames()
                    ->toArray()))
                {
                    return redirect()
                        ->route('branch.dashboard');
                }
            }
            else
            {
                if (!in_array('Group Loan Print PDF form', auth()
                    ->user()
                    ->getPermissionNames()
                    ->toArray()))
                { //group loan
                    return redirect()
                        ->route('branch.dashboard');
                }
            }
        }
        $data['title'] = "Download Loan PDF";
        $data['id'] = $id;
        $data['loanDetails'] = Memberloans::with('loan', 'LoanApplicants', 'LoanCoApplicants', 'LoanGuarantor', 'Loanotherdocs', 'GroupLoanMembers', 'loanInvestmentPlans')->findOrFail($id);
        $data['formPrintUrl'] = URL::to("branch/loan/form/print/" . $id . "/" . $type . "");
        return view('templates.branch.loan_management.print_view', $data);
    }
    public function updateBranchCashFromBackDate($amount, $branch_id, $ftdate)
    {
        $globaldate = $ftdate;
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($globaldate)));
        $getCurrentBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($getCurrentBranchRecord)
        {
            $bResult = \App\Models\BranchCash::find($getCurrentBranchRecord->id);
            $bData['balance'] = $getCurrentBranchRecord->balance + $amount;
            if ($getCurrentBranchRecord->closing_balance > 0)
            {
                $bData['closing_balance'] = $getCurrentBranchRecord->closing_balance + $amount;
            }
            $bData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            $bResult->update($bData);
            $getNextBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')
                ->get();
            if ($getNextBranchRecord)
            {
                foreach ($getNextBranchRecord as $key => $value)
                {
                    $sResult = \App\Models\BranchCash::find($value->id);
                    $sData['opening_balance'] = $value->closing_balance;
                    $sData['balance'] = $value->balance + $amount;
                    if ($value->closing_balance > 0)
                    {
                        $sData['closing_balance'] = $value->closing_balance + $amount;
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $sResult->update($sData);
                }
            }
        }
        else
        {
            $oldDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')
                ->first();
            if ($oldDateRecord)
            {
                $Result1 = \App\Models\BranchCash::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $data1['closing_balance'] = $oldDateRecord->balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data['balance'] = $oldDateRecord->balance + $amount;
                //$data['opening_balance']=$oldDateRecord->balance;
                //$data['loan_closing_balance']=0;
                $data['opening_balance'] = $oldDateRecord->closing_balance;
                $data['closing_balance'] = 0;
                $data['balance'] = $oldDateRecord->balance;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = 1;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            }
            else
            {
                $data['balance'] = $amount;
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                //$data['loan_opening_balance']=0;
                // $data['loan_closing_balance']=0;
                //$data['balance']=0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = 1;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            }
        }
        $getCurrentBranchClosingRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($getCurrentBranchClosingRecord)
        {
            $bResult = \App\Models\BranchClosing::find($getCurrentBranchClosingRecord->id);
            $bData['balance'] = $getCurrentBranchClosingRecord->balance + $amount;
            if ($getCurrentBranchClosingRecord->closing_balance > 0)
            {
                $bData['closing_balance'] = $getCurrentBranchClosingRecord->closing_balance + $amount;
            }
            $bData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            $bResult->update($bData);
            $getNextBranchClosingRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')
                ->get();
            if ($getNextBranchClosingRecord)
            {
                foreach ($getNextBranchClosingRecord as $key => $value)
                {
                    $sResult = \App\Models\BranchClosing::find($value->id);
                    $sData['opening_balance'] = $value->closing_balance;
                    $sData['balance'] = $value->balance + $amount;
                    if ($value->closing_balance > 0)
                    {
                        $sData['closing_balance'] = $value->closing_balance + $amount;
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $sResult->update($sData);
                }
            }
        }
        else
        {
            $oldDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')
                ->first();
            if ($oldDateRecord)
            {
                $Result1 = \App\Models\BranchClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                //$data1['loan_closing_balance']=$oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data['balance'] = $oldDateRecord->balance + $amount;
                //$data['opening_balance']=$oldDateRecord->balance;
                //$data['loan_closing_balance']=0;
                $data['opening_balance'] = $oldDateRecord->closing_balance;
                $data['balance'] = $oldDateRecord->balance;
                $data['closing_balance'] = 0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = 1;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            }
            else
            {
                $data['balance'] = $amount;
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                //$data['loan_opening_balance']=0;
                //$data['loan_closing_balance']=0;
                //$data['balance']=0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = 1;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            }
        }
    }
    public function updateSsbDayBookAmount($amount, $account_number, $date)
    {
        $globaldate = $date;
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $getCurrentBranchRecord = SavingAccountTranscation::where('account_no', $account_number)->whereDate('created_at', $entryDate)->first();
        $bResult = SavingAccountTranscation::find($getCurrentBranchRecord->id);
        $bData['opening_balance'] = $getCurrentBranchRecord->opening_balance - $amount;
        $bData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
        $bResult->update($bData);
        $getNextBranchRecord = SavingAccountTranscation::where('account_no', $account_number)->whereDate('created_at', '>', $entryDate)->orderby('created_at', 'ASC')
            ->get();
        if ($getNextBranchRecord)
        {
            foreach ($getNextBranchRecord as $key => $value)
            {
                $sResult = SavingAccountTranscation::find($value->id);
                $sData['opening_balance'] = $value->opening_balance - $amount;
                $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $sResult->update($sData);
            }
        }
    }
    /**
     * Display renew form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCollectorAssociate(Request $request)
    {
        $code = $request->code;
        $applicationDate = $request->applicationDate;
        $collectorDetails = Member::with('savingAccount')->leftJoin('carders', 'members.current_carder_id', '=', 'carders.id')
            ->where('members.associate_no', $code)->where('members.status', 1)
            ->where('members.is_deleted', 0)
            ->where('members.is_associate', 1)
            ->where('members.is_block', 0)
            ->where('members.associate_status', 1)
            ->select('carders.name as carders_name', 'members.first_name', 'members.last_name', 'members.id')
            ->first();
        if ($collectorDetails)
        {
            if ($collectorDetails['savingAccount'])
            {
                $ssbTransaction = SavingAccountTranscation::select('id', 'opening_balance')->where('account_no', $collectorDetails['savingAccount'][0]->account_no)
                    ->whereDate('created_at', date("Y-m-d", strtotime(convertDate($applicationDate))))->orderBy('id', 'desc')
                    ->first();
                if ($ssbTransaction)
                {
                    $ssbAmount = $ssbTransaction->opening_balance;
                }
                else
                {
                    $ssbTransaction = SavingAccountTranscation::select('id', 'opening_balance')->where('account_no', $collectorDetails['savingAccount'][0]->account_no)
                        ->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($applicationDate))))->first();
                    if ($ssbTransaction)
                    {
                        $ssbAmount = $ssbTransaction->opening_balance;
                    }
                    else
                    {
                        $ssbAmount = 0;
                    }
                }
            }
            else
            {
                $ssbAmount = 0;
            }
        }
        else
        {
            $ssbAmount = 0;
        }
        if ($collectorDetails)
        {
            return Response::json(['msg_type' => 'success', 'collectorDetails' => $collectorDetails, 'ssbAmount' => $ssbAmount]);
        }
        else
        {
            return Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }
    public static function createMemberTransaction($daybook_ref_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $account_id, $amount, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from)
    {
        $t = date("H:i:s");
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bank_id;
        $data['account_id'] = $account_id;
        $data['amount'] = $amount;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($created_at)));
        //echo "<pre>"; print_r($data); die;
        $transcation = \App\Models\MemberTransaction::create($data);
        return $transcation->id;
    }
    public static function createAllTransactionNew($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head1, $head2, $head3, $head4, $head5, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from)
    {
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bank_id;
        $data['bank_ac_id'] = $bank_ac_id;
        $data['head1'] = $head1;
        $data['head2'] = $head2;
        $data['head3'] = $head3;
        $data['head4'] = $head4;
        $data['head5'] = $head5;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id_to'] = $branch_id_to;
        $data['branch_id_from'] = $branch_id_from;
        $data['opening_balance'] = $opening_balance;
        $data['amount'] = $amount;
        $data['closing_balance'] = $closing_balance;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $transcation = \App\Models\AllTransaction::create($data);
        return $transcation->id;
    }
    public static function createBranchDayBook($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from)
    {
        $t = date("H:i:s");
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id_to'] = $branch_id_to;
        $data['branch_id_from'] = $branch_id_from;
        $data['opening_balance'] = $opening_balance;
        $data['amount'] = $amount;
        $data['closing_balance'] = $closing_balance;
        $data['description'] = $description;
        $data['description_dr'] = $description_dr;
        $data['description_cr'] = $description_cr;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['is_contra'] = $is_contra;
        $data['contra_id'] = $contra_id;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($created_at)));
        $transcation = \App\Models\BranchDaybook::create($data);
        return $transcation->id;
    }
    public static function createAllHeadTransaction($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $created_by, $created_by_id)
    {
        //$globaldate = Session::get('created_at');
        $stateid = getBranchState(Auth::user()->username);
        $globaldate = checkMonthAvailability(date('d') , date('m') , date('Y') , $stateid);
        $t = date("H:i:s");
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bank_id;
        $data['bank_ac_id'] = $bank_ac_id;
        $data['head_id'] = $head_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id_to'] = $branch_id_to;
        $data['branch_id_from'] = $branch_id_from;
        $data['opening_balance'] = $opening_balance;
        $data['amount'] = $amount;
        $data['closing_balance'] = $closing_balance;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_from_id'] = $cheque_bank_from_id;
        $data['cheque_bank_ac_from_id'] = $cheque_bank_ac_from_id;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['cheque_bank_to_name'] = $cheque_bank_to_name;
        $data['cheque_bank_to_branch'] = $cheque_bank_to_branch;
        $data['cheque_bank_to_ac_no'] = $cheque_bank_to_ac_no;
        $data['cheque_bank_to_ifsc'] = $cheque_bank_to_ifsc;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_from_id'] = $transction_bank_from_id;
        $data['transction_bank_from_ac_id'] = $transction_bank_from_ac_id;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_bank_to_name'] = $transction_bank_to_name;
        $data['transction_bank_to_ac_no'] = $transction_bank_to_ac_no;
        $data['transction_bank_to_branch'] = $transction_bank_to_branch;
        $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $transcation = \App\Models\AllHeadTransaction::create($data);
        return true;
    }
    public function getBranchApprovedCheque(Request $request)
    {
        $BranchId = branchName()->id;
        $cheque = ReceivedCheque::where('branch_id', $BranchId)->where('status', 2)
            ->get();
        return response()
            ->json($cheque);
    }
    public function gst_amount_penalty(Request $req)
    {
        $loanId = $req->loanId;
        $loanType = $req->loanType;
        $penaltyAmount = $req->penaltyAmount;
        $globaldate = date('Y-m-d',strtotime(convertDate('2022-07-01')));
        if($loanType == 'loan')
        {
            $loanDetails = Memberloans::select('id','branch_id')->with('loanBranch')->where('id',$loanId)->first();
        }
        else{
            $loanDetails = Grouploans::select('id','branch_id')->with('loanBranch')->where('id',$loanId)->first();
        }
        // $stateid = getBranchState($loanDetails['loanBranch']->name);
        // $globaldate = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
        $getHeadSetting = \App\Models\HeadSetting::where('head_id',33)->first();
        $stateid = getBranchState($loanDetails['loanBranch']->name);
        $getGstSetting = \App\Models\GstSetting::where('state_id',$loanDetails['loanBranch']->state_id)->whereDate('applicable_date','<=',$globaldate)->exists();
        if($penaltyAmount >0 && $getGstSetting)
        {
            if($loanDetails['loanBranch']->state_id == $stateid)
            {
                $gstAmount =  (($penaltyAmount*$getHeadSetting->gst_percentage)/100)/2;
                $label1 ='CGST '.($getHeadSetting->gst_percentage/2).' %';
                $label2 ='SGST '.($getHeadSetting->gst_percentage/2).' %';;
            }
            else{
                $gstAmount =  ($penaltyAmount*$getHeadSetting->gst_percentage)/100;
                $label1 ='IGST '.($getHeadSetting->gst_percentage).' %';
                $label2 = '';
            }
        }
        elseif(!empty($penaltyAmount) && $loanDetails->amount )
        {
            if($loanDetails['loanBranch']->state_id == 33)
            {
                $gstAmount =  (($loanDetails->amount*$getHeadSetting->gst_percentage)/100)/2;
                $label1 ='CGST '.($getHeadSetting->gst_percentage/2).' %';
                $label2 ='SGST '.($getHeadSetting->gst_percentage/2).' %';;
            }
            else{
                $gstAmount =  ($loanDetails->amount*$getHeadSetting->gst_percentage)/100;
                $label1 ='IGST '.($getHeadSetting->gst_percentage).' %';
                $label2 = '';
            }
        }
        else{
            $gstAmount = 0;
            $label1 = 0;
            $label2 = 0;
        }
        return response()->json(['gstAmount'=>$gstAmount,'label1'=>$label1,'label2'=>$label2]);
    }
     public function loanTransaction(Request $request)
    {
        if (!in_array('Loan Transaction', auth()
        ->user()
        ->getPermissionNames()
        ->toArray()))
    {
        return redirect()
            ->route('branch.dashboard');
    }
        $data['title']='Loan Transactions Detail';
   /*        $data=Memberloans::with('loan','loanMember','loanMemberAssociate')->with(['loanBranch' => function($query){ $query->select('id', 'name','branch_code','sector','regan','zone');}])->where('loan_type','!=',3);*/
        return view('templates.branch.loan_management.loan-transactions', $data);
    }
     public function loanTransactionAjax(Request $request)
    {
        if ($request->ajax()) {
            $BranchId = branchName()->id;
            $arrFormData = array();
            if(!empty($_POST['searchform']))
            {
                foreach($_POST['searchform'] as $frm_data)
                {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
                $data=\App\Models\LoanDayBooks::with(['loan_member' => function($query){ $query->select('id', 'member_id','first_name','last_name','associate_code');}])->with(['loanBranch' => function($query){ $query->select('id', 'name','branch_code','sector','regan','zone');}])->with(['member_loan' => function($query){ $query->select('id', 'emi_option','emi_period');},'group_member_loan'=>function($q){
                    $q->select('id','account_number','member_id')->with(['loanMember'=>function($q){
                        $q->select('id','member_id','first_name','last_name');
                    }]);
                }])->where('is_deleted',0)
                    ->with(['loanMemberAssociate'])
                    ->WHERE('status',1)->where('branch_id',$BranchId);
			if(Auth::user()->branch_id>0){
			 $id=Auth::user()->branch_id;
		     $data=$data->where('branch_id','=',$id);
		    }
            if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
              {
                if($arrFormData['date_from'] !=''){
                    $startDate=date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));
                    if($arrFormData['date_to'] !=''){
                        $endDate=date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));
                    }
                    else
                    {
                        $endDate='';
                    }
                    $data=$data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
                if($arrFormData['plan'] !=''){
                    $planId=$arrFormData['plan'];
                    $data=$data->where('loan_type','=',$planId);
                }
                if($arrFormData['application_number'] !=''){
                    $application_number=$arrFormData['application_number'];
                    $data=$data->where('account_number','=',$application_number);
                }
                if($arrFormData['associate_code'] !=''){
                    $associateCode=$arrFormData['associate_code'];
                    $data=$data->whereHas('loanMemberAssociate', function ($query) use ($associateCode) {
                      $query->where('members.associate_no','LIKE','%'.$associateCode.'%');
                    });
                }
                if($arrFormData['payment_mode'] !=''){
                    $payment_mode=$arrFormData['payment_mode'];
                    $data=$data->where('payment_mode','=', $payment_mode);
                }
            }
            $count = $data->count('id');
           $totalCount  = $count;
            $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $sno=$_POST['start'];
        $rowReturn = array();
        foreach($data as $i => $row)
        {
             $sno++;
            $val['DT_RowIndex'] = $sno;
            $val['created_at'] =  date("d/m/Y", strtotime( $row['created_at']));;
            $val['branch'] = $row['loanBranch']->name;
            $val['sector'] =  $row['loanBranch']->sector;
            $val['branch_code'] =$row['loanBranch']->branch_code;
            $val['region'] = $row['loanBranch']->regan;
            $val['zone'] = $row['loanBranch']->zone;
            $url =URL::to("branch/loan/emi-transactions/".$row->loan_id."/".$row->loan_type."");
             $btn = '<a class=" " href="'.$url.'" title="Edit Member" target="_blank">' . $row->account_number.'</a>';
            $val['account_number'] = $btn;
            if($row->loan_type == 3)
            {
              if(isset($row['loan_member']))
              {
                $val['member_name'] =$row['group_member_loan']['loanMember']->first_name.' '.$row['group_member_loan']['loanMember']->last_name;
                $val['member_id'] = $row['group_member_loan']['loanMember']->member_id;
              }
            }else{
                if(isset( $row['loan_member']))
                {
                    $val['member_id'] = $row['loan_member']->member_id;
                    $val['member_name'] = $row['loan_member']->first_name.' '.$row['loan_member']->last_name;
                }
            }
           if(isset($row['loanMemberAssociate']))
           {
                $val['associate_code'] =$row['loanMemberAssociate']->associate_no;
           }
           else{
            $val['associate_code'] ='N/A';
           }
            $plan_name = 'N/A';
            if($row->loan_type == 1){
                    $plan_name = 'Personal Loan';
                }elseif($row->loan_type == 2){
                    $plan_name = 'Staff Loan(SL)';
                }elseif($row->loan_type == 3){
                    $plan_name = 'Group Loan';
                }elseif($row->loan_type == 4){
                    $plan_name = 'Loan against Investment plan(DL) ';
                }
            $val['plan_name'] = $plan_name;
            $emi_tenure ='N/A';
             if(isset($row['member_loan']->emi_option) && $row['member_loan']->emi_option == 1){
            $emi_tenure = $row['member_loan']->emi_period." Months";
            }elseif(isset($row['member_loan']->emi_option) && $row['member_loan']->emi_option == 2){
                $emi_tenure = $row['member_loan']->emi_period." Weeks";
            }elseif(isset($row['member_loan']->emi_option) && $row['member_loan']->emi_option == 3){
                $emi_tenure = $row['member_loan']->emi_period." Days";
            }
            $val['tenure'] =$emi_tenure;
            $val['emi_amount'] = $row->deposit;
             $loan_sub_type = $row->loan_sub_type;
				if($loan_sub_type==0){
					$loan_sub_type	=	'EMI';
				}else{
					$loan_sub_type	=	'Late Penalty';
				}
            $val['loan_sub_type'] = 	$loan_sub_type;
            // $member =Member::where('id',$row->associate_id)->first(['id','first_name','last_name']);
            if(isset($row['loanMemberAssociate']))
            {
                 $val['associate_name'] =$row['loanMemberAssociate']->first_name.' '.$row['loanMemberAssociate']->last_name;
            }
            else{
             $val['associate_name'] ='N/A';
            }
             $payment_mode = '';
				switch($row->payment_mode){
					case 0:
					$payment_mode='Cash';
					break;
					case 1:
					$payment_mode='Cheque';
					break;
					case 2:
					$payment_mode='DD';
					break;
					case 3:
					$payment_mode='Online Transaction';
					break;
					case 4:
					$payment_mode='By Saving Account';
					break;
				default :
					$payment_mode='Cash';
					break;
				}
            $val['payment_mode'] =$payment_mode;
            $rowReturn[] = $val;
        }
    }
            $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
            return json_encode($output);
    }

    /**
     * get active plan basis of effective from
     * @param application_date as a_date
     */
    public function getActiveLoans(Request $request)
    {
        $applicationDate = date('Y-m-d', strtotime(convertDate($request->aDate)));
        if ($request->purpose == 'tenure') {
            $loans = \App\Models\LoanTenure::where('loan_id', $request->loanType)->whereDate('effective_from', '<=', $applicationDate)->where('status', 1)->get();
        } else {
            $loans = \App\Models\Loans::where('loan_type', $request->loanType)->whereDate('effective_from', '<=', $applicationDate)->where('status', 1)->get()->groupBy('loan_category');
        }
            $stateid = Auth::user()->branch->state_id;
            $getHeadSetting = \App\Models\HeadSetting::where('head_id',294)->first();
            $getHeadSettingFileCHrage = \App\Models\HeadSetting::where('head_id',90)->first();
            $getGstSetting = \App\Models\GstSetting::where('state_id',$stateid)->where('applicable_date','<=',$applicationDate)->exists();
            $getGstSettingno = \App\Models\GstSetting::select('id','gst_no')->where('state_id',$stateid)->where('applicable_date','<=',$applicationDate)->first();
            $gstData = array();
            $gstFileChargeData = array();
            //Gst Insuramce
            if(isset($getHeadSetting->gst_percentage) &&  $getGstSetting )
            {
                $gstData['gst_percentage'] =  $getHeadSetting->gst_percentage;
                $gstData['IntraState'] = ($stateid == 33 ? true : false);
            }
            else{
                $gstData['gst_percentage'] =  '';
                $gstData['IntraState'] ='';
            }
            if(isset($getHeadSettingFileCHrage->gst_percentage) &&  $getGstSetting )
            {
                $gstFileChargeData['gst_percentage'] =  $getHeadSettingFileCHrage->gst_percentage;
                $gstFileChargeData['IntraState'] = ($stateid == 33 ? true : false);
            }
            else{
                $gstFileChargeData['gst_percentage'] =  '';
                $gstFileChargeData['IntraState'] ='';
            }
        return response()->json(['loans' => $loans,'gstData'=>$gstData,'gstFileChargeData'=>$gstFileChargeData]);
    }

    /**
     * Gewt file charge on the basis of amount
     */
    public function getFileCharge(Request $request)
    {
        $gDate = date('Y-m-d', strtotime(convertDate($request->applicationDate)));
        $loans = \App\Models\LoanCharge::where('min_amount', '<=', $request->amount)->where('max_amount', '>=', $request->amount)->where('plan_name', $request->loanType)->where('type', 1)->where('status', 1)/*->where('tenure',$request->tenure)*/->whereDate('effective_from', '<=', $gDate)->first();
        $insurance = \App\Models\LoanCharge::where('min_amount', '<=', $request->amount)->where('max_amount', '>=', $request->amount)->where('plan_name', $request->loanType)->where('type', 2)->where('status', 1)/*->where('tenure',$request->tenure)*/->whereDate('effective_from', '<=', $gDate)->first();
        return response()->json(['loans' => $loans,'insurance'=> $insurance]);
    }
    public function getInvestmentLoan(Request $request)
    {
        $plan = \App\Models\Memberinvestments::where('account_number', $request->invetsment_id)->first('id');
        $existRecord = MemberLoans::with('loanInvestmentPlans')->whereHas("loanInvestmentPlans", function ($q) use ($plan) {
            $q->where('plan_id', $plan->id);
        })->where('applicant_id', $request->applicantDetail)->whereIn('status', [0, 1, 2, 4])->exists();
        if ($existRecord) {
            return Response::json(['msg' => 'Loan is Running on this Investment', 'msg_type' => 'warning']);
        }
    }
}