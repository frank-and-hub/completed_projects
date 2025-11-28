<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Loans;
use App\Models\Memberloans;
use App\Models\Grouploans;
use App\Models\LoanDayBooks;
use App\Models\ReceivedCheque;
use App\Models\SamraddhBank;
use URL;
use DB;
use DateTime;
use App\Http\Traits\Oustanding_amount_trait;
use App\Http\Traits\getRecordUsingDayBookRefId;
use Carbon\Carbon;
class RecoveryLoanController extends Controller
{
    use Oustanding_amount_trait, getRecordUsingDayBookRefId;
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
     * Display a listing of the loans.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * Loan recovery listing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function recovery()
    {
        if (check_my_permission(Auth::user()->id, "28") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Loan Recovery';
        $data['cBanks'] = SamraddhBank::select('id', 'bank_name')->with(['bankAccount' => function ($query) {
            $query->select('id', 'bank_id', 'account_no');
        }])->get();
        $data['cheques'] = ReceivedCheque::select('cheque_no', 'deposit_account_id')->where('status', 2)
            ->get();
        $data['loan_plan'] = Loans::select('id', 'name', 'code')->where('loan_type', 'L')->get();
        return view('templates.admin.loan.loan_recovery', $data);
    }
    /**
     *Group loan recovery listing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function groupLoanRecovery()
    {
        if (check_my_permission(Auth::user()->id, "29") != "1") {
            return redirect()
                ->route('admin.dashboard');
        }
        $data['title'] = 'Group Loan Recovery';
        $data['cBanks'] = SamraddhBank::select('id', 'bank_name')->with(['bankAccount' => function ($query) {
            $query->select('id', 'bank_id', 'account_no');
        }])->get();
        $data['cheques'] = ReceivedCheque::select('cheque_no', 'deposit_account_id')->where('status', 2)
            ->get();
        $data['loan_plan'] = Loans::select('id', 'name', 'code')->where('loan_type', 'G')->get();
        return view('templates.admin.loan.group_loan_recovery', $data);
    }
    /**
     * Loan recovery ajax listing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function recoveryListAjax(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $companyId = $arrFormData['company_id'];
                $data = Memberloans::whereHas('loans', function ($q) {
                    $q->where('loan_type', 'L')->select('id', 'name', 'loan_type');
                })->with(['loanMemberCompany' => function ($q) use($companyId) {
                        $q->select('id', 'member_id')
                            ->with(['ssb_detail' => function ($q1)  use($companyId){
                                $q1->select('id', 'account_no', 'member_id', 'customer_id')->where('company_id',$companyId)
                                    ->with(['getSSBAccountBalance']);
                            }]);
                    }])
                    ->with('member:id,member_id,first_name,last_name',  'MemberCompany:id,member_id', 'CollectorAccount.member_collector', 'loanMemberAssociate:id,first_name,last_name,associate_no', 'loanBranch:id,name,branch_code,state_id', 'loanTransactionNew:id,deposit,group_loan_id,account_number', 'company:id,name')
                    ->where('company_id', $arrFormData['company_id'])
                    ->where('is_deleted',0);
                if ($arrFormData['date_from'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));
                    if ($arrFormData['date_to'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));
                    } else {
                        $endDate = '';
                    }
                    if ($endDate) {
                        $data = $data->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
                    } else {
                        $data = $data->whereDate('approve_date', '>=', $startDate);
                    }
                }
                if ($arrFormData['loan_recovery_plan'] != '') {
                    $planId = $arrFormData['loan_recovery_plan'];
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
                if ($arrFormData['branch_id'] != '') {
                    $branch_id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', $branch_id);
                }
                if ($arrFormData['customer_id'] != '') {
                    $meid = $arrFormData['customer_id'];
                    $data = $data->whereHas('loanMember', function ($query) use ($meid) {
                        $query->where('members.member_id', 'LIKE', '%' . $meid . '%');
                    });
                }
                if ($arrFormData['member_id'] != '') {
                    $meid = $arrFormData['member_id'];
                    $data = $data->whereHas('MemberCompany', function ($query) use ($meid) {
                        $query->where('member_companies.member_id', 'LIKE', '%' . $meid . '%');
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
                $count = $data->count('id');
                $data = $data
                    ->orderby('id', 'DESC')
                    ->offset($_POST['start'])
                    ->limit($_POST['length'])
                    ->get();
                $totalCount = $count;
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company'] = $row['company']->name;
                    $val['branch'] = $row['loanBranch']->name;
                    $val['branch_code'] = $row['loanBranch']->branch_code;
                    $val['account_number'] = $row->account_number;
                    $val['member_name'] = $row['member']->first_name . ' ' . $row['member']->last_name;
                    $val['member_id'] = $row['MemberCompany']->member_id;
                    $val['customer_id'] = $row['member']->member_id;
                    $plan_name = $row['loan']->name;
                    $val['plan_name'] = $plan_name;
                    if ($row->emi_option == 1) {
                        $tenure = $row->emi_period . ' Months';
                    } elseif ($row->emi_option == 2) {
                        $tenure = $row->emi_period . ' Weeks';
                    } elseif ($row->emi_option == 3) {
                        $tenure = $row->emi_period . ' Days';
                    } else {
                        $tenure = '';
                    }
                    $val['tenure'] = $tenure;
                    if ($row->file_charges) {
                        $file_charge = $row->file_charges . ' <i class="fa fa-inr"></i>';
                    } else {
                        $file_charge = 'N/A';
                    }
                    $val['file_charge'] = $file_charge;
                    $val['insurance_charge'] = $row->insurance_charge . ' <i class="fa fa-inr"></i>';;
                    $file_charges_payment_mode = 'N/A';
                    if ($row->file_charge_type == 1) {
                        $file_charges_payment_mode = 'Loan';
                    } elseif ($row->file_charge_type == 0) {
                        $file_charges_payment_mode = 'Cash';
                    } else {
                        $file_charges_payment_mode = 'N/A';
                    }
                        $insurance_cgst = 'N/A';
                        $insurance_sgst = 'N/A';
                        $insurance_igst = 'N/A';
                        $filecharge_igst = 'N/A';
                        $filecharge_sgst = 'N/A';
                        $filecharge_cgst = 'N/A';
                    if($row->insurance_cgst>0)
                    {
                        $insurance_cgst = $row->insurance_cgst . ' <i class="fa fa-inr"></i>';
                        $insurance_sgst = $row->insurance_sgst . ' <i class="fa fa-inr"></i>';
                    }
                    if($row->filecharge_cgst>0)
                    {
                        $filecharge_cgst = $row->filecharge_cgst . ' <i class="fa fa-inr"></i>';
                        $filecharge_sgst = $row->filecharge_sgst . ' <i class="fa fa-inr"></i>';
                    }
                    if($row->insurance_charge_igst>0)
                    {
                        $insurance_igst = $row->insurance_charge_igst . ' <i class="fa fa-inr"></i>';
                    }
                    if($row->filecharge_igst>0)
                    {
                        $filecharge_igst = $row->filecharge_igst . ' <i class="fa fa-inr"></i>';
                    }
                    $val['cgst_insurance_charge'] = $insurance_cgst;
                    $val['sgst_insurance_charge'] = $insurance_sgst;
                    $val['igst_insurance_charge'] = $insurance_igst;
                    $val['igst_file_charge'] = $filecharge_igst;
                    $val['cgst_file_charge'] = $filecharge_cgst;
                    $val['sgst_file_charge'] = $filecharge_sgst;
                    $val['file_charges_payment_mode'] = $file_charges_payment_mode;
                    $totalbalance = $row->emi_period * $row->emi_amount;
                    if (isset($row->transfer_amount) && !empty($row->transfer_amount)) {
                        $val['transfer_amount'] = $row->transfer_amount . ' <i class="fas fa-rupee-sign"></i>';
                    } else {
                        $val['transfer_amount'] = 'N/A';
                    }

                    $val['loan_amount'] = $row->amount . ' <i class="fas fa-rupee-sign"></i>';
                    $totalbalance = $row->emi_period * $row->emi_amount;
                    $outAmount = $row->getOutstanding()->latest('created_at')->where('is_deleted','0')->orderby('id', 'DESC')->first() ? $row->getOutstanding()->latest('created_at')->where('is_deleted','0')->orderby('id', 'DESC')->first()->out_standing_amount : 0;
                    $outstandingAmount = isset($outAmount)
                    ? ($outAmount > 0 ? $outAmount : 0)
                    : $row->amount;
                    $Finaloutstanding_amount = $totalbalance - $row->received_emi_amount;
                    $val['outstanding_amount'] = $outstandingAmount. ' <i class="fa fa-inr"></i>' ;
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
                    if (isset($row['loanMemberCustom']->id)) { // getMemberData($row->associate_member_id)
                        $associate_code = $row['loanMemberCustom']; //getMemberData($row->associate_member_id);
                        $val['associate_code'] = $associate_code->associate_no;
                    } else {
                        $val['associate_code'] = 'N/A';
                    }
                    //$val['associate_code'] = $row['loanBranch']->name;
                    if (isset($row['loanMemberCustom']->id)) {
                        $associate_name = $row['loanMemberCustom']->first_name . ' ' . $row['loanMemberCustom']->last_name;
                    }
                    $val['associate_name'] = $associate_name ?? 'N/A';
                    $url2 = URL::to("admin/loan/deposit/emi-transactions/" . $row->id . "/" . $row->loan_type . "");
                    if (isset($row->account_number)) {
                        $btn = '<a class=" " href="' . $url2 . '" title="View Statement" target="_blank">' . $row['loanTransactionNew']->sum('deposit') . '</a>';
                        $val['total_payment'] = $btn . ' <i class="fa fa-inr"></i>';
                    } else {
                        $val['total_payment'] = 'N/A';
                    }
                    if ($row['approved_date']) {
                        $val['approved_date'] = date("d/m/Y", strtotime($row['approved_date']));
                    } else {
                        $val['approved_date'] = 'N/A';
                    }
                    if ($row['approve_date']) {
                        $val['sanction_date'] = date("d/m/Y", strtotime($row['approve_date']));
                    } else {
                        $val['sanction_date'] = 'N/A';
                    }
                    $val['application_date'] = date("d/m/Y", strtotime($row['created_at']));
                    $status = 'N/A';
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
                    $val['status'] = $status;
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
                    $closingAmountROI = 0;
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
                    if ($row['loanMemberCompany']['ssb_detail']) {
                        $ssbBalance = $row['loanMemberCompany']['ssb_detail']['getSSBAccountBalance'] ? $row['loanMemberCompany']['ssb_detail']['getSSBAccountBalance']->totalBalance : 0;
                        $ssbId = $row['loanMemberCompany']['ssb_detail']->id;
                        $ssbAccount = $row['loanMemberCompany']['ssb_detail']['getSSBAccountBalance'] ? $row['loanMemberCompany']['ssb_detail']['getSSBAccountBalance']->account_no : '';
                    } else {
                        $ssbBalance = 0;
                        $ssbId = 0;
                        $ssbAccount = '';
                    }
                    $viewUrl = URL::to("admin/loan/view/" . $row->id . "/" . $row->loan_type . "");
                    $purl = URL::to("admin/loan/print/" . $row->id . "/" . $row->loans->loan_type . "");
                    $turl = URL::to("admin/loan/emi-transactions/" . $row->id . "/" . "L" . "");
                    $urlCom = URL::to("admin/loan/commission/" . $row->id . "");
                    $pdf = URL::to("admin/loan/download-recovery-clear/" . $row->id . "/" . $row->loan_type . "");
                    $print = URL::to("admin/loan/print-recovery-clear/" . $row->id . "/" . $row->loan_type . "");
                    $btn = '';
                    $btn .= '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $btn .= '<a class="dropdown-item" href="' . $viewUrl . '" title="View"><i class="icon-pencil5  mr-2"></i>View</a>  ';
                    if ($row->status == 4) {
                        if ($row->emi_option == 1) {
                            $closingDate = date('Y-m-d', strtotime("+" . $row->emi_period . " months", strtotime($row['created_at'])));
                        } elseif ($row->emi_option == 2) {
                            $days = $row->emi_period * 7;
                            $start_date = $row['created_at'];
                            $date = strtotime($start_date);
                            $date = strtotime("+" . $days . " day", $date);
                            $closingDate = date('Y-m-d', $date);
                        } elseif ($row->emi_option == 3) {
                            $days = $row->emi_period;
                            $start_date = $row['created_at'];
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
                            // $dueAmount = round($cAmount - $row->received_emi_amount);
                        } elseif ($row->emi_option == 2) {
                            $loanStartDate = $row->approve_date;
                            $startDate = date("m/d/Y", strtotime(convertDate($loanStartDate)));
                            $endDate = date('m/d/Y');
                            $first = DateTime::createFromFormat('m/d/Y', $startDate);
                            $second = DateTime::createFromFormat('m/d/Y', $endDate);
                            $dueTime = floor($first->diff($second)->days / 7);
                            $cAmount = round($dueTime * $row->emi_amount);
                            // $dueAmount = round($cAmount - $row->received_emi_amount);
                        } elseif ($row->emi_option == 3) {
                            // $startDate = strtotime($row->approve_date);
                            // $endDate = strtotime(date('Y-m-d'));
                            // $datediff = $endDate - $startDate;
                            // $dueTime = round($datediff / (60 * 60 * 24));
                            // $cAmount = round($dueTime * $row->emi_amount);
                            // $dueAmount = round($cAmount - $row->received_emi_amount);
                            $startDate = strtotime($row->approve_date);
                            $endDate = strtotime(date('Y-m-d'));
                            $startDate = Carbon::parse($startDate);
                            $endDate =  Carbon::parse($endDate);
                            $dueTime = $startDate->diffInDays($endDate);
                            // $datediff = $endDate - $startDate;
                            // $dueTime = round($datediff / (60 * 60 * 24));
                            $cAmount = round($dueTime * $row->emi_amount);
                            // $dueAmount = round($cAmount - $row->received_emi_amount);
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
                        $EmiDates = $this->emiDates($LoanCreatedDate, $row->emi_period, NULL);
                        $emi_date = $nextEmiDates;
                        $emiDetail = \App\Models\LoanEmisNew::select('out_standing_amount','emi_date')->where('loan_id', $row->id)->where('loan_type', $row->loan_type)->where('is_deleted','0')->orderBY('id','desc')->first();
                        $recoverdAmount = loanOutsandingAmount($row->id,$row->account_number);
                        $outstandingAmount = isset($emiDetail->out_standing_amount)
                        ? ($emiDetail->out_standing_amount > 0 ? round($emiDetail->out_standing_amount) : 0)
                        : $row->amount;
                        if(date('Y-m-d') > $closingDate)
                        {
                            $dueAmount =   $outstandingAmount;
                        }
                        else{
                            $dueAmount = round($cAmount - $recoverdAmount);
                        }
                        $lastEmidate =isset($emiDetail->emi_date)  ? date('d/m/Y',strtotime($emiDetail->emi_date)) : date('d/m/Y',strtotime($row->approve_date));
                        $stateId = $row['loanBranch']->state_id;
                        $closerAmount = calculateCloserAmount($outstandingAmount,$lastEmidate,$row->ROI,$stateId);
                        $dueAmount = emiAmountUotoTodaysDate($row->id,$row->account_number,$row->approve_date,$stateId,$row->emi_option,$row->emi_amount,$row->closing_date);
                        if (Auth::user()->id != "13" && $outstandingAmount > 0) {
                            // if($row['loanMember']['savingAccount_Custom']){
                                //aman commented on 22-07-2023
                                $btn .= '<a class="dropdown-item pay-emi" href="javascript:void(0);" title="Pay EMI" data-loan-id="' . $row->id . '" data-loan-emi="' . $row->emi_amount . '" data-ssb-amount="' . $ssbBalance . '" data-ssb-id="' . $ssbId . '" data-recovered-amount="' . $recoverdAmount . '"  data-last-recovered-amount="' . lastLoanRecoveredAmount($row->id, 'loan_id') . '" data-closing-amount="' . $closingAmount . '" data-due-amount="' . $dueAmount . '" data-penalty-amount="' . $penaltyAmount . '" data-penalty-time="' . $penaltyTime . '" data-toggle="modal" data-target="#pay-loan-emi" data-ssb-account="' . $ssbAccount . '"  data-emiDate = "' . implode(',', $emi_date) . '" data-AllemiDate = "' . implode(',', $EmiDates) . '" data-emiOption = "' . $row->emi_option . '" data-company-id="'.$row->company_id.'" data-outstanding_amount="'.$closerAmount  .'" data-branch-id="' . $row->branch_id . '" data-ecs_type = "' . $row->ecs_type  . '" ><i class="icon-pencil5  mr-2"   ></i>Pay EMI</a>  ';
                            // if($row->emi_option ==1)
                            // {
                            //     $btn .= '<a class="dropdown-item pay-emi" href="javascript:void(0);" title="Pay Advanced EMI" data-loan-id="' . $row->id . '" data-loan-emi="' . $row->emi_amount . '" data-ssb-amount="' . $ssbBalance . '" data-ssb-id="' . $ssbId . '" data-recovered-amount="' . loanOutsandingAmount($row->id, $row->account_number) . '"  data-last-recovered-amount="' . lastLoanRecoveredAmount($row->id, 'loan_id') . '" data-closing-amount="' . $closingAmount . '" data-due-amount="' . $dueAmount . '" data-penalty-amount="' . $penaltyAmount . '" data-penalty-time="' . $penaltyTime . '" data-toggle="modal" data-target="#pay-loan-emi"  data-emiDate = "'.implode(',',$emi_date).'" data-AllemiDate = "'.implode(',',$EmiDates).'" data-emiOption = "'.$row->emi_option.'"><i class="icon-pencil5  mr-2"></i>Pay  Advanced EMI</a>  ';
                            // }
                            // }
                        }
                        if (Auth::user()->id != "13") {
                            // $btn .= '<a class="dropdown-item close-loan" href="javascript:void(0);" data-id="' . $row->id . '"><i class="far fa-calendar-check"></i>Close Loan</a>';
                            $btn .= '<a class="dropdown-item close-loan" href="javascript:void(0);" data-id="' . $row->id . '" data-ssb_id="' . $row->ssb_id. '" data-branch_id="' . $row->branch_id . '"><i class="far fa-calendar-check"></i>Close Loan</a>';

                        }
                    }
                    if (Auth::user()->id != "13")
                    {
                         $btn .= '<a class="dropdown-item" href="' . $purl . '" target="_blank"><i class="fa fa-print mr-2" aria-hidden="true"></i>Print</a>';
                    }
                    $btn .= '<a class="dropdown-item" href="' . $turl . '"><i class="fas fa-money-bill-wave-alt mr-2" aria-hidden="true"></i>Transactions</a>';
                    // $btn .= '<a class="dropdown-item" href="' . $urlCom . '"><i class="fas fa-percent text-default mr-2"></i>Loan Commission</a>';
                    if ($row->status == 3)
                    {
                        if (Auth::user()->id != "13")
                        {
                            $btn .= '<a class="dropdown-item" href="' . $pdf . '"><i class="fas fa-download text-default mr-2"></i>Download No Dues</a>';
                            $btn .= '<a class="dropdown-item" href="' . $print . '" target="_blank"><i class="fas fa-print text-default mr-2" ></i>print No Dues</a>';
                        }
                    }
                    $btn .= '</div></div></div>';
                    $val['action'] = $btn;
                    $rowReturn[] = $val;
                }
                $output = array(
                    "draw" => $_POST['draw'],
                    "recordsTotal" => $totalCount,
                    "recordsFiltered" => $totalCount,
                    "data" => $rowReturn,
                );
                return json_encode($output);
            } else {
                $output = array(
                    "draw" => 0,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => 0,
                );
                return json_encode($output);
            }
        }
    }
    /**
     * Loan recovery ajax listing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function groupLoanRecoveryListAjax(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $companyId = $arrFormData['company_id'];
                $data = Grouploans::with(['loanMemberCompanyid' => function ($q) use($companyId){
                    $q->select('id', 'member_id')
                        ->with(['ssb_detail' => function ($q1) use($companyId){
                            $q1->select('id', 'account_no', 'member_id', 'customer_id')->where('company_id',$companyId)
                                ->with(['getSSBAccountBalance']);
                        }]);
                }])
                    ->whereHas('loans', function ($q) {
                        $q->where('loan_type', 'G')->select('id', 'name', 'loan_type');
                    })
                    ->with('loanMember:id,member_id,first_name,last_name', 'getOutstanding:loan_id,id,out_standing_amount,is_deleted,loan_type', 'CollectorAccount.member_collector', 'loanMemberAssociate:id,first_name,last_name,associate_no', 'gloanBranch:id,name,branch_code,state_id', 'loanTransactionNew:id,deposit,group_loan_id,account_number', 'company:id,name')->with([
                        'getOutstanding'=>function($q) {
                            $q->with(['loans'=>function($q){
                                $q->where('loan_type','=','G');
                            }]);
                        }
                    ])->where('company_id', $arrFormData['company_id']);
                if ($arrFormData['date_from'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));
                    if ($arrFormData['date_to'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));
                    } else {
                        $endDate = '';
                    }
                    if ($endDate) {
                        $data = $data->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
                    } else {
                        $data = $data->whereDate('approve_date', '>=', $startDate);
                    }
                }
                if ($arrFormData['branch_id'] != '') {
                    $branch_id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', $branch_id);
                }
                if ($arrFormData['loan_account_number'] != '') {
                    $loan_account_number = $arrFormData['loan_account_number'];
                    $data = $data->where('account_number', '=', $loan_account_number);
                }
                if ($arrFormData['group_loan_common_id'] != '') {
                    $group_loan_common_id = $arrFormData['group_loan_common_id'];
                    $data = $data->where('group_loan_common_id', '=', $group_loan_common_id);
                }
                if ($arrFormData['member_name'] != '') {
                    $name = $arrFormData['member_name'];
                    $data = $data->whereHas('loanMember', function ($query) use ($name) {
                        $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
                    });
                }
                if ($arrFormData['member_id'] != '') {
                    $meid = $arrFormData['member_id'];
                    $data = $data->whereHas('loanMemberCompanyid', function ($query) use ($meid) {
                        $query->where('member_companies.member_id', 'LIKE', '%' . $meid . '%');
                    });
                }
                if ($arrFormData['customer_id'] != '') {
                    $meid = $arrFormData['customer_id'];
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
                $count = $data->count('id');
                $data = $data->orderby('id', 'DESC')
                    ->offset($_POST['start'])->limit($_POST['length'])->get();
                $totalCount = $count;
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company'] = $row['company']->name;
                    $val['branch'] = $row['gloanBranch']->name;
                    $val['branch_code'] = $row['gloanBranch']->branch_code;
                    $val['sector_name'] = $row['gloanBranch']->sector;
                    $val['region_name'] = $row['gloanBranch']->regan;
                    $val['zone_name'] = $row['gloanBranch']->zone;
                    $val['group_loan_id'] = $row->group_loan_common_id;
                    $val['account_number'] = $row->account_number;
                    $val['member_name'] = $row['loanMember']->first_name . ' ' . $row['loanMember']->last_name;
                    $val['customer_id'] = $row['loanMember']->member_id;
                    // pd($row);
                    $val['member_id'] = $row['loanMemberCompanyid']->member_id;
                    $plan_name = $row['loan']->name;
                    $val['plan_name'] = $plan_name;
                    $filecharge_cgst = 'N/A';
                    $filecharge_sgst = 'N/A';
                    $filecharge_igst =  'N/A';
                    $insurance_cgst = 'N/A';
                    $insurance_sgst = 'N/A';
                    $insurance_igst = 'N/A';
                    if (($row->insurance_cgst > 0) && (isset($row->insurance_cgst))) {
                        $insurance_cgst = $row->insurance_cgst . ' <i class="fa fa-inr"></i>';
                    }
                    if (($row->insurance_sgst > 0) && (isset($row->insurance_sgst))) {
                        $insurance_sgst = $row->insurance_sgst . ' <i class="fa fa-inr"></i>';
                    }
                    if (($row->insurance_charge_igst > 0) && (isset($row->insurance_charge_igst))) {
                        $insurance_igst = $row->insurance_charge_igst . ' <i class="fa fa-inr"></i>';
                    }
                    if (($row->filecharge_cgst > 0) && (isset($row->filecharge_cgst))) {
                        $filecharge_cgst = $row->filecharge_cgst . ' <i class="fa fa-inr"></i>';
                    }
                    if (($row->filecharge_sgst > 0) && (isset($row->filecharge_sgst))) {
                        $filecharge_sgst = $row->filecharge_sgst . ' <i class="fa fa-inr"></i>';
                    }
                    if (($row->filecharge_igst > 0) && (isset($row->filecharge_igst))) {
                        $filecharge_igst = $row->filecharge_igst . ' <i class="fa fa-inr"></i>';
                    }
                    $val['cgst_insurance_charge'] = $insurance_cgst;
                    $val['sgst_insurance_charge'] = $insurance_sgst;
                    $val['igst_insurance_charge'] = $insurance_igst;
                    $val['igst_file_charge'] = $filecharge_igst;
                    $val['cgst_file_charge'] = $filecharge_cgst;
                    $val['sgst_file_charge'] = $filecharge_sgst;
                    if ($row->emi_option == 1) {
                        $tenure = $row->emi_period . ' Months';
                    } elseif ($row->emi_option == 2) {
                        $tenure = $row->emi_period . ' Weeks';
                    } elseif ($row->emi_option == 3) {
                        $tenure = $row->emi_period . ' Days';
                    }
                    $val['tenure'] = $tenure;
                    if ($row->file_charges) {
                        $file_charge = $row->file_charges . ' <i class="fa fa-inr"></i>';
                    } else {
                        $file_charge = 'N/A';
                    }
                    $val['insurance_charge'] = $row->insurance_charge . ' <i class="fa fa-inr"></i>';;
                    $val['file_charge'] = $file_charge;
                    $file_charges_payment_mode = 'N/A';
                    if ($row->file_charge_type) {
                        $file_charges_payment_mode = 'Loan';
                    } else {
                        $file_charges_payment_mode = 'Cash';
                    }
                    $val['file_charges_payment_mode'] = $file_charges_payment_mode;
                    $val['loan_amount'] = $row->transfer_amount . ' <i class="fa fa-inr"></i>';
                    $val['amount'] = $row->amount . ' <i class="fas fa-rupee-sign"></i>';
                    $totalbalance = $row->emi_period * $row->emi_amount;
                    $Finaloutstanding_amount = $totalbalance - $row->received_emi_amount;
                    $outAmount = $row->getOutstanding()->latest('created_at')->first() ? $row->getOutstanding()->latest('created_at')->first()->out_standing_amount : 0;

                    $outstandingAmount = isset($outAmount)
                    ? ($outAmount > 0 ? $outAmount : 0)
                    : $row->amount;
                    $val['outstanding_amount'] =   $outstandingAmount. ' <i class="fa fa-inr"></i>' ;
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
                    $val['associate_code'] = $row['loanMemberAssociate']->associate_no;
                    $val['associate_name'] = $row['loanMemberAssociate']->first_name . ' ' . $row['loanMemberAssociate']->last_name;
                    $url2 = URL::to("admin/loan/deposit/emi-transactions/" . $row->id . "/" . $row->loan_type . "");
                    if (isset($row->account_number)) {
                        $btn = '<a class=" " href="' . $url2 . '" title="View Statement" target="_blank">' . $row['loanTransactionNew']->sum('deposit') . '</a>';
                        $val['total_payment'] = $btn . ' <i class="fa fa-inr"></i>';
                    } else {
                        $val['total_payment'] = 'N/A';
                    }
                    // $val['total_payment'] = $row['loanTransactionNew']->sum('deposit'). ' <i class="fa fa-inr"></i>';
                    if ($row['approved_date']) {
                        $val['approved_date'] = date("d/m/Y", strtotime(convertDate($row['approved_date'])));
                    } else {
                        $val['approved_date'] = 'N/A';
                    }
                    if ($row['approve_date']) {
                        $val['sanction_date'] = date("d/m/Y", strtotime(convertDate($row['approve_date'])));
                    } else {
                        $val['sanction_date'] = 'N/A';
                    }
                    $val['application_date'] = date("d/m/Y", strtotime(convertDate($row['created_at'])));
                    // $val['application_date'] = date("d/m/Y", strtotime($row['created_at']));;

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
                    $val['status'] = $status;
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
                    if ($row->emi_option == 1) {
                        $closingAmountROI = $row->due_amount * $row->ROI / 1200;
                    } elseif ($row->emi_option == 2) {
                        $closingAmountROI = $row->due_amount * $row->ROI / 5200;
                    } elseif ($row->emi_option == 3) {
                        $closingAmountROI = $row->due_amount * $row->ROI / 36500;
                    }
                    $closingAmount = round($row->due_amount + $closingAmountROI);
                    // pd($row['loanMemberCompanyid']['ssb_detail']['getSSBAccountBalance']->totalBalance);
                    $ssbAmount = ($row['loanMemberCompanyid']['ssb_detail']['getSSBAccountBalance']) ?? " "; //$row->loanSavingAccount2; //getMemberSsbAccountDetail($row['loanMember']->id);
                    //  pd($ssbAmount);
                    if ($ssbAmount != " ") {
                        $ssbBalance = $ssbAmount->totalBalance;
                        $ssbId = $row['loanMemberCompanyid']['ssb_detail']->id;
                        $ssbAccount = $ssbAmount->account_no;
                    } else {
                        $ssbBalance = 0;
                        $ssbId = 0;
                        $ssbAccount = '';
                    }
                    $viewUrl = URL::to("admin/loan/view/" . $row->member_loan_id . "/".$row->loans->loan_type);
                    $purl = URL::to("admin/loan/print/" . $row->id . "/".$row->loans->loan_type);
                    $turl = URL::to("admin/loan/emi-transactions/" . "$row->id" . "/G");
                    $urlCom = URL::to("admin/loan/commission-group/" . $row->id . "");
                    $pdf = URL::to("admin/loan/download-recovery-clear/" . $row->id . "/3");
                    $print = URL::to("admin/loan/print-recovery-clear/" . $row->id . "/3");
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $btn .= '<a class="dropdown-item" href="' . $viewUrl . '" title="View"><i class="icon-pencil5  mr-2"></i>View</a>  ';
                    $created_at = $row['created_at'];
                    if ($row->status == 4) {
                        if ($row->emi_option == 1) {
                            $closingDate = date('Y-m-d', strtotime("+" . $row->emi_period . " months", strtotime($created_at)));
                        } elseif ($row->emi_option == 2) {
                            $days = $row->emi_period * 7;
                            $start_date = $created_at;
                            $date = strtotime($start_date);
                            $date = strtotime("+" . $days . " day", $date);
                            $closingDate = date('Y-m-d', $date);
                        } elseif ($row->emi_option == 3) {
                            $days = $row->emi_period;
                            $start_date = $created_at;
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
                            // $dueAmount = round($cAmount - $row->received_emi_amount);
                        } elseif ($row->emi_option == 2) {
                            $loanStartDate = $row->approve_date;
                            $startDate = date("m/d/Y", strtotime(convertDate($loanStartDate)));
                            $endDate = date('m/d/Y');
                            $first = DateTime::createFromFormat('m/d/Y', $startDate);
                            $second = DateTime::createFromFormat('m/d/Y', $endDate);
                            $dueTime = floor($first->diff($second)->days / 7);
                            $cAmount = round($dueTime * $row->emi_amount);
                            // $dueAmount = round($cAmount - $row->received_emi_amount);
                        } elseif ($row->emi_option == 3) {
                            // $startDate = strtotime($row->approve_date);
                            // $endDate = strtotime(date('Y-m-d'));
                            // $datediff = $endDate - $startDate;
                            // $dueTime = round($datediff / (60 * 60 * 24));
                            // $cAmount = round($dueTime * $row->emi_amount);
                            // $dueAmount = round($cAmount - $row->received_emi_amount);
                            $startDate = strtotime($row->approve_date);
                            $endDate = strtotime(date('Y-m-d'));
                            $startDate = Carbon::parse($startDate);
                            $endDate =  Carbon::parse($endDate);
                            $dueTime = $startDate->diffInDays($endDate);
                            // $datediff = $endDate - $startDate;
                            // $dueTime = round($datediff / (60 * 60 * 24));
                            $cAmount = round($dueTime * $row->emi_amount);
                            // $dueAmount = round($cAmount - $row->received_emi_amount);
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
                        $recoverdAmount  =  loanGroupOutsandingAmount($row->id);
                        $emiDetail = \App\Models\LoanEmisNew::select('out_standing_amount','emi_date')->where('loan_id', $row->id)->where('loan_type', $row->loan_type)->where('is_deleted','0')->orderBY('id','desc')->first();
                        $outstandingAmount = isset($emiDetail->out_standing_amount)
                        ? ($emiDetail->out_standing_amount > 0 ? round($emiDetail->out_standing_amount) : 0)
                        : $row->amount;
                        if(date('Y-m-d') > $closingDate)
                        {
                            $dueAmount =   $outstandingAmount;
                        }
                        else{
                            $dueAmount = round($cAmount - $recoverdAmount);
                        }
                        $dueAmount = round($dueAmount < 0) ? 0 : $dueAmount;
                        $lastEmidate =isset($emiDetail->emi_date)  ? date('d/m/Y',strtotime($emiDetail->emi_date)) : date('d/m/Y',strtotime($row->approve_date));
                        $stateId = $row['gloanBranch']->state_id;
                        $closingDateNew = date('d/m/Y',strtotime(convertDate($row->closing_date)));
                        $closerAmount = calculateCloserAmount($outstandingAmount,$lastEmidate,$row->ROI,$stateId);

                        $dueAmount = emiAmountUotoTodaysDate($row->id,$row->account_number,$row->approve_date,$stateId,$row->emi_option,$row->emi_amount,$row->closing_date);
                        if (Auth::user()->id != "13" && $outstandingAmount > 0) {
                             //aman commented on 22-07-2023
                            $btn .= '<a class="dropdown-item pay-emi" href="javascript:void(0);" title="Pay EMI" data-loan-id="' . $row->id . '" data-loan-emi="' . $row->emi_amount . '" data-ssb-amount="' . $ssbBalance . '" data-ssb-id="' . $ssbId . '" data-recovered-amount="' . loanGroupOutsandingAmount($row->id, $row->account_number) . '"  data-last-recovered-amount="' . lastLoanRecoveredAmount($row->id, 'group_loan_id') . '" data-closing-amount="' . $closingAmount . '" data-due-amount="' . $dueAmount . '" data-penalty-amount="' . $penaltyAmount . '" data-penalty-time="' . $penaltyTime . '" data-toggle="modal" data-target="#pay-loan-emi" data-ssb-account="' . $ssbAccount . '"  data-emiDate = "' . implode(',', $emi_date) . '" data-AllemiDate = "' . implode(',', $EmiDates) . '" data-emiOption = "' . $row->emi_option . '" data-company-id = "'.$row->company_id.'" data-outstanding_amount="'.$closerAmount  .'" data-branch-id="' . $row->branch_id . '" data-ecs_type = "' . $row->ecs_type  . '" ><i class="icon-pencil5  mr-2" ></i>Pay EMI</a>  ';
                        }
                if (Auth::user()->id != "13")
                {
                    // $btn .= '<a class="dropdown-item close-group-loan" href="javascript:void(0);" data-id="' . $row->id . '"><i class="far fa-calendar-check"></i>Close Loan</a>';

                    $btn .= '<a class="dropdown-item close-group-loan" href="javascript:void(0);" data-id="' . $row->id . '" data-ssb_id="' . $row->ssb_id. '" data-branch_id="' . $row->branch_id . '"><i class="far fa-calendar-check"></i>Close Loan</a>';
                }
                        // ----------
                    }
                     if (Auth::user()->id != "13")
                    {
                        // $btn .= '<a class="dropdown-item" href="' . $purl . '" target="_blank"><i class="fa fa-print mr-2" aria-hidden="true"></i>Print</a>';
                    }
                    $btn .= '<a class="dropdown-item" href="' . $turl . '"><i class="fas fa-money-bill-wave-alt mr-2" aria-hidden="true"></i>Transactions</a>';
                    $grpCount = Grouploans::where('member_loan_id', $row->member_loan_id)
                    ->count('id');
                $grpCountStatus = Grouploans::where('member_loan_id', $row->member_loan_id)
                    ->where('status', 3)
                    ->count('id');
                if ($grpCount == $grpCountStatus)
                {
                    if (Auth::user()->id != "13")
                    {
                        $btn .= '<a class="dropdown-item" href="' . $pdf . '"><i class="fas fa-download text-default mr-2"></i>Download No Dues</a>';
                        $btn .= '<a class="dropdown-item" href="' . $print . '" target="_blank"><i class="fas fa-print text-default mr-2" ></i>print No Dues</a>';
                    }
                }
                // $btn .= '<a class="dropdown-item" href="' . $urlCom . '"><i class="fas fa-percent text-default mr-2"></i>Loan Commission</a>';
                    $btn .= '</div></div></div>';
                    $val['action'] = $btn;
                    $rowReturn[] = $val;
                }
                $output = array(
                    "draw" => $_POST['draw'],
                    "recordsTotal" => $totalCount,
                    "recordsFiltered" => $totalCount,
                    "data" => $rowReturn,
                );
            } else {
                $output = array(
                    "draw" => 0,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => 0,
                );
            }
            return json_encode($output);
        }
    }
    /**
     * Display loan details.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function loantype(Request $request)
    {
        // pp($request['loan_type']);
        if ($request['loan_type'] == "") {
            $loans = Loans::where('company_id', $request['company_id'])->get(['name', 'id', 'loan_category']);
            $return_array = compact('loans');
            return json_encode($return_array);
        } else {
            $loans = Loans::where('loan_type', $request['loan_type'])->where('company_id', $request['company_id'])->get(['name', 'id', 'loan_category']);
            $return_array = compact('loans');
            return json_encode($return_array);
        }
    }
    public function loanTransaction(Request $request)
    {
        if (check_my_permission(Auth::user()->id, "265") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Loan Transactions Detail';
        return view('templates.admin.loan.loan-transactions', $data);
    }
    public function loanTransactionAjax(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = LoanDayBooks::with([
                    'loanDetail.loanMember:id,member_id,first_name,last_name,associate_code',
                    'loanBranch:id,name,branch_code,sector,regan,zone',
                    'member_loan:id,emi_option,emi_period,applicant_id,customer_id',
                    'member_loan.loanMemberCompany:id,member_id',
                    'group_member_loan_via_id:id,emi_option,emi_period,applicant_id,member_id,customer_id',
                    'group_member_loan_via_id.loanMemberCompanyid:id,member_id',
                    'group_member_loan_via_id.member:id,member_id,first_name,last_name',
                    'group_member_loan.loanMember:id,member_id,first_name,last_name,associate_code',
                    'company:id,name',
                    'loanMemberAssociate'
                    ])
                ->where('is_deleted', 0)
                ->whereHas('loan_plan', function ($q) use ($arrFormData) {
                    $q->where('loan_type', $arrFormData['transaction_loan_type'])->select('id', 'name', 'loan_type');
                })
                // ->with(['loan_plan' => function($q){ $q->select('id','name','loan_type'); }])
                ->where('status', 1)
                ->where('company_id', $arrFormData['company_id']);
                if (Auth::user()->branch_id > 0) {
                    $id = Auth::user()->branch_id;
                    $data = $data->where('branch_id', '=', $id);
                }
                if ($arrFormData['date_from'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));
                    if ($arrFormData['date_to'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));
                    } else {
                        $endDate = '';
                    }
                    if ($endDate) {
                        $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                    } else {
                        $data = $data->whereDate('created_at', '>=', $startDate);
                    }
                }
                if ($arrFormData['member_id'] != '') {
                    $meid = $arrFormData['member_id'];
                    $data = $data->whereHas('member_loan.loanMemberCompany', function ($query) use ($meid) {
                        $query->where('member_companies.member_id', 'LIKE', '%' . $meid . '%');
                    });
                }
                if ($arrFormData['customer_id'] != '') {
                    $meid = $arrFormData['customer_id'];
                    $data = $data->whereHas('loan_member', function ($query) use ($meid) {
                        $query->where('members.member_id', 'LIKE', '%' . $meid . '%');
                    });
                }
                if ($arrFormData['branch_id'] != '') {
                    $branch_id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', '=', $branch_id);
                }
                if ($arrFormData['application_number'] != '') {
                    $application_number = $arrFormData['application_number'];
                    $data = $data->where('account_number', '=', $application_number);
                }
                if ($arrFormData['transaction_loan_plan'] != '') {
                    $planId = $arrFormData['transaction_loan_plan'];
                    $data = $data->where('loan_type', '=', $planId);
                }
                if ($arrFormData['associate_code'] != '') {
                    $associateCode = $arrFormData['associate_code'];
                    $data = $data->whereHas('loanMemberAssociate', function ($query) use ($associateCode) {
                        $query->where('members.associate_no', 'LIKE', '%' . $associateCode . '%');
                    });
                }
                if ($arrFormData['payment_mode'] != '') {
                    $payment_mode = $arrFormData['payment_mode'];
                    $data = $data->where('payment_mode', '=', $payment_mode);
                }
                // $loant=$arrFormData['loan_type'];
                // $data = $data->whereHas('loan_plan', function ($query) use ($loant)
                //     {
                //         $query->where('loans.loan_type', '=',$loant);
                //     });
                $count = $data->count('id');
                $dataExport = $data->orderby('id', 'DESC')->get();
                $totalCount  = $count;
                $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $i => $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['created_at'] =  date("d/m/Y", strtotime($row['created_at']));
                    $val['company_name'] = $row['company']->name;
                    $val['branch'] = $row['loanBranch']->name;
                    $url = URL::to("admin/loan/emi-transactions/" . $row->loan_id . "/" . $row->loan_type . "");
                    $btn = '<a class=" " href="' . $url . '" title="Edit Member" target="_blank">' . $row->account_number . '</a>';
                    $val['account_number'] = $btn;
                    // pd($row['group_member_loan_via_id']->toArray());
                    if ($row['loan_plan']->loan_type == 'G') {
                        if (isset($row['group_member_loan'])) {
                            $val['customer_id'] = $row['group_member_loan']['loanMember']->member_id;
                            $val['member_name'] = $row['group_member_loan']['loanMember']->first_name . ' ' . $row['group_member_loan']['loanMember']->last_name;
                            $val['member_id'] = ($row['group_member_loan']['loanMemberCompanyid']->member_id) ?? "N/A";
                        }
                    } else {
                        if (isset($row['loanDetail']['loanMember'])) {
                            $val['customer_id'] = $row['loanDetail']['loanMember']->member_id;
                            $val['member_name'] = $row['loanDetail']['loanMember']->first_name . ' ' . $row['loanDetail']['loanMember']->last_name;
                            $val['member_id'] = ($row['member_loan']['loanMemberCompany']->member_id) ?? "N/A";
                        }
                    }
                    if (isset($row['loanMemberAssociate'])) {
                        $val['associate_code'] = $row['loanMemberAssociate']->associate_no;
                    } else {
                        $val['associate_code'] = 'N/A';
                    }
                    $plan_name = 'N/A';
                    $plan_name = $row['loan_plan']->name;;
                    $val['plan_name'] = $plan_name;
                    $emi_tenure = 'N/A';
                    // pd($row->toArray());
                    if ($row['loan_plan']->loan_type == 'G') {
                        if (isset($row['group_member_loan_via_id']->emi_option) && $row['group_member_loan_via_id']->emi_option == 1) {
                            $emi_tenure = $row['group_member_loan_via_id']->emi_period . " Months";
                        } elseif (isset($row['group_member_loan_via_id']->emi_option) && $row['group_member_loan_via_id']->emi_option == 2) {
                            $emi_tenure = $row['group_member_loan_via_id']->emi_period . " Weeks";
                        } elseif (isset($row['group_member_loan_via_id']->emi_option) && $row['group_member_loan_via_id']->emi_option == 3) {
                            $emi_tenure = $row['group_member_loan_via_id']->emi_period . " Days";
                        }
                    } else {
                        if (isset($row['member_loan']->emi_option) && $row['member_loan']->emi_option == 1) {
                            $emi_tenure = $row['member_loan']->emi_period . " Months";
                        } elseif (isset($row['member_loan']->emi_option) && $row['member_loan']->emi_option == 2) {
                            $emi_tenure = $row['member_loan']->emi_period . " Weeks";
                        } elseif (isset($row['member_loan']->emi_option) && $row['member_loan']->emi_option == 3) {
                            $emi_tenure = $row['member_loan']->emi_period . " Days";
                        }
                    }
                    $val['tenure'] = $emi_tenure;
                    $val['emi_amount'] = $row->deposit;
                    $loan_sub_type = $row->loan_sub_type;
                    if ($loan_sub_type == 0) {
                        $loan_sub_type    =    'EMI';
                    } else {
                        $loan_sub_type    =    'Late Penalty';
                    }
                    $val['loan_sub_type'] =     $loan_sub_type;
                    // $member =Member::where('id',$row->associate_id)->first(['id','first_name','last_name']);
                    if (isset($row['loanMemberAssociate'])) {
                        $val['associate_name'] = $row['loanMemberAssociate']->first_name . ' ' . $row['loanMemberAssociate']->last_name;
                    } else {
                        $val['associate_name'] = 'N/A';
                    }
                    $payment_mode = '';
                    switch ($row['payment_mode']) {
                        case 0:
                            $payment_mode = 'Cash';
                            break;
                        case 1:
                            $payment_mode = 'Cheque';
                            break;
                        case 2:
                            $payment_mode = 'DD';
                            break;
                        case 3:
                            $payment_mode = 'Online Transaction';
                            break;
                        case 4:
                            $payment_mode = 'By Saving Account';
                            break;
                        default:
                            $payment_mode = 'Cash';
                            break;
                    }
                    $val['payment_mode'] = $payment_mode;
                    $rowReturn[] = $val;
                }
                Cache::put('loanTransactionDatalist', $dataExport);
                Cache::put('loanTransactionDatacount', $totalCount);
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
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
    public function loanTransactionExportList(Request $request)
    {
        $data = Cache::get('loanTransactionDatalist');
        $count = Cache::get('loanTransactionDatacount');
        if ($request['loan_transaction_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/LoanTransactionsList.csv";
            $fileName = env('APP_EXPORTURL') . "asset/LoanTransactionsList.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
            $totalResults = $count;
            $results = $data;
            //dd($results);
            $result = 'next';
            if (($start + $limit) >= $totalResults) {
                $result = 'finished';
            }
            // if its a fist run truncate the file. else append the file
            if ($start == 0) {
                $handle = fopen($fileName, 'w');
            } else {
                $handle = fopen($fileName, 'a');
            }
            if ($start == 0) {
                $headerDisplayed = false;
            } else {
                $headerDisplayed = true;
            }
            $sno = $_POST['start'];
            foreach ( $data->slice($start,$limit) as $i => $row) {
                $sno++;
                // p($row->toArray());
                $val['S/N'] = $sno;
                $val['CREATED AT'] =  date("d/m/Y", strtotime($row->created_at));
                $val['COMPANY'] = $row['company']->name;
                $val['BR Name'] = $row['loanBranch']->name;
                $val['BR Code'] = $row['loanBranch']->branch_code;
                $val['SO Name'] = $row['loanBranch']->sector;
                $val['RO Name'] = $row['loanBranch']->regan;
                $val['ZO Name'] = $row['loanBranch']->zone;
                //	$val['Member Id']=$row['loan_member']->member_id;
                $val['ACCOUNT NO'] = $row->account_number;
                // if (isset($row['loanMemberAssociate'])) {
                //     $val['associate_name'] = $row['loanMemberAssociate']->first_name . ' ' . $row['loanMemberAssociate']->last_name;
                // } else {
                //     $val['associate_name'] = 'N/A';
                // }
                //dd($row['payment_mode']);
                $plan_name = '';
                // if ($row->loan_type == 1) {
                //     $plan_name = 'Personal Loan';
                // } elseif ($row->loan_type == 2) {
                //     $plan_name = 'Staff Loan(SL)';
                // } elseif ($row->loan_type == 3) {
                //     $plan_name = 'Group Loan';
                // } elseif ($row->loan_type == 4) {
                //     $plan_name = 'Loan against Investment plan(DL) ';
                // }
                $val['LOAN TYPE'] =   $row['loan_plan']->name;
                $tenure = '';
                if (isset($row['member_loan']['emi_option']) && $row['member_loan']['emi_option'] == 1) {
                    $tenure =  $row['member_loan']['emi_period'] . ' Months';
                } elseif (isset($row['member_loan']['emi_option']) && $row['member_loan']['emi_option'] == 2) {
                    $tenure =  $row['member_loan']['emi_period'] . ' Weeks';
                } elseif (isset($row['member_loan']['emi_option']) && $row['member_loan']['emi_option'] == 3) {
                    $tenure =  $row['member_loan']['emi_period'] . ' Days';
                }
                $val['Tenure'] =  $tenure;
                switch ($row['payment_mode']) {
                    case '1':
                        $payment_mode    =    'Cheque';
                        break;
                    case '2':
                        $payment_mode    =    'DD';
                        break;
                    case '3':
                        $payment_mode    =    'Online Ttransaction';
                        break;
                    case '4':
                        $payment_mode    =    'By saving account';
                        break;
                    case '5':
                        $payment_mode    =    'From loan amount';
                        break;
                    default:
                        $payment_mode    =    'Cash';
                }
                if ($row['loan_plan']->loan_type == 'G') {
                    if (isset($row['loan_member'])) {
                        $val['Customer Id'] = $row['loan_member']->member_id;
                        $val['Member(Account Holder Name)'] = $row['loan_member']->first_name . ' ' . $row['loan_member']->last_name;
                    }
                } else {
                    if (isset($row['loan_member'])) {
                        $val['Customer Id'] = $row['loan_member']->member_id;
                        $val['Member(Account Holder Name)'] = $row['loan_member']->first_name . ' ' . $row['loan_member']->last_name;
                    }
                }
                if ($row['loan_plan']->loan_type == 'G') {
                    if (isset($row['group_member_loan'])) {
                        $val['Customer Id'] = $row['group_member_loan']['loanMember']->member_id;
                        $val['Member(Account Holder Name)'] = $row['group_member_loan']['loanMember']->first_name . ' ' . $row['group_member_loan']['loanMember']->last_name;
                    }
                } else {
                    if (isset($row['loanDetail']['loanMember'])) {
                        $val['Customer Id'] = $row['loanDetail']['loanMember']->member_id;
                        $val['Member(Account Holder Name)'] = $row['loanDetail']['loanMember']->first_name . ' ' . $row['loanDetail']['loanMember']->last_name;
                    }
                }
                // p($row['group_member_loan_via_id']);
                if ($row->loan_type == 3) {
                    $val['Member Id'] = $row['group_member_loan_via_id']['loanMemberCompanyid'] ? $row['group_member_loan_via_id']['loanMemberCompanyid']['member_id']: 'N/A';//($row['member_loan']['loanMemberCompany']->member_id) ?? "N/A";
                }else{
                    $val['Member Id'] = ($row['member_loan']['loanMemberCompany']->member_id) ?? "N/A";
                }
                $val['Emi Amount'] =  $row->deposit;
                $loan_sub_type = $row->loan_sub_type;
                if ($loan_sub_type == 0) {
                    $loan_sub_type    =    'EMI';
                } else {
                    $loan_sub_type    =    'Late Penalty';
                }
                $val['Transaction Type'] =  $loan_sub_type;
                if (isset($row['loanMemberAssociate'])) {
                    $val['Associate Code'] = $row['loanMemberAssociate']->associate_no;
                } else {
                    $val['Associate Code'] = 'N/A';
                }
                if (isset($row['loanMemberAssociate'])) {
                    $val['Associate Name'] = $row['loanMemberAssociate']->first_name . ' ' . $row['loanMemberAssociate']->last_name;
                } else {
                    $val['Associate Name'] = 'N/A';
                }
                $val['Payment Mode'] =  $payment_mode;
                if (!$headerDisplayed) {
                    // Use the keys from $data as the titles
                    fputcsv($handle, array_keys($val));
                    $headerDisplayed = true;
                }
                // Put the data into the stream
                fputcsv($handle, $val);
            }
            // Close the file
            fclose($handle);
            if ($totalResults == 0) {
                $percentage = 100;
            } else {
                $percentage = ($start + $limit) * 100 / $totalResults;
                $percentage = number_format((float)$percentage, 1, '.', '');
            }
            // Output some stuff for jquery to use
            $response = array(
                'result'        => $result,
                'start'         => $start,
                'limit'         => $limit,
                'totalResults'  => $totalResults,
                'fileName' => $returnURL,
                'percentage' => $percentage
            );
            echo json_encode($response);
        } elseif ($request['loan_transaction_export'] == 1) {
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.loan.export_loan_transactions_list', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('LoanTransactionsList.pdf');
        }
    }
    public function emiTransactionsView($id, $type)
    {
        if ($type != "G")
        {
            $data['title'] = 'Loan EMI Transactions';
            $data['loanDetails'] = Memberloans::select('loan_type', 'account_number')->whereHas('loans', function ($q) {
                $q->where('loan_type', 'L')->select('id', 'name', 'loan_type');
            })->where('id', $id)->first();
            $data['loanTitle'] = $data['loanDetails']->loan->name;
            // $data['loanTitle'] = "Loan";
        }
        else
        {
            $data['title'] = 'Group Loan EMI Transactions';
            $data['loanDetails'] = Grouploans::select('account_number','loan_type')->whereHas('loans', function ($q) {
                $q->where('loan_type', 'G')->select('id', 'name', 'loan_type');
            })->where('id', $id)->first();
            $data['loanTitle'] = $data['loanDetails']->loan->name;
            // $data['loanTitle'] = "Group Loan";
        }
        $data['id'] = $id;
        $data['type'] = $type;
        return view('templates.admin.loan.loan_emi_transaction', $data);
    }
    public function depositLoanTransaction(Request $request)
    {
        if ($request->ajax()) {
            if($request['loanType'] != 3){
                $loanRecord = Memberloans::Where('id',$request['loanId'])->first('account_number');
           }else{
                $loanRecord = Grouploans::Where('id',$request['loanId'])->first('account_number');
           }
           $data = LoanDayBooks::where('loan_type',$request['loanType'])->where('loan_sub_type','<>',2)->where('loan_id',$request['loanId'])->where('is_deleted',0);
           $data1=$data->get();
            $count=count($data1);
            $data=$data->offset($_POST['start'])->limit($_POST['length'])->orderBy('payment_date','asc')->get();
            $dataCount = LoanDayBooks::where('loan_id','=',$request->loanId);
			$totalCount =$dataCount->count();
            $sno=$_POST['start'];
            $rowReturn = array();
			if($_POST['pages'] == 1){
				$total  = 0;
			}else{
				$total  = $_POST['total'];
			}
            foreach($data as $row)
			{
				$sno++;
                $val['DT_RowIndex']=$sno;
                $val['transaction_id']=$row->id;
                $val['date']=date("d/m/Y", strtotime($row->payment_date));
				$paymentMode = '';
				  if($row->payment_mode == 0){
                    $paymentMode = 'Cash';
                }elseif($row->payment_mode == 1){
                    $paymentMode = 'Cheque';
                }elseif($row->payment_mode == 2){
                    $paymentMode = 'DD';
                }elseif($row->payment_mode == 3){
                    $paymentMode = 'Online Transaction';
                }elseif($row->payment_mode == 4){
                    $paymentMode = 'By Saving Account ';
                }
                elseif($row->payment_mode == 6){
                    $paymentMode = 'JV ';
                }
				$val['payment_mode']=$paymentMode;
				$val['description']= $row->description;
                $val['sanction_amount']=($row->loan_sub_type == 2)  ? $row->deposit : '0';
				if($row->loan_sub_type == 1){
                    $deposite =  $row->deposit;
                    $val['penalty'] =  $deposite;
                }else{
                     $val['penalty'] =  '0';
                }
				if($row->loan_sub_type == 0){
                     $deposite =  $row->deposit;;
                     $val['deposite'] =  $deposite;
                }else{
                     $val['deposite'] =  '0';
                }
                if($row->jv_journal_amount){
                     $jv_journal_amount =  $row->jv_journal_amount;;
                     $val['jv_amount'] =  number_format((float)$jv_journal_amount, 2, '.', '');
                }else{
                     $val['jv_amount'] =  '0';
                }
				 //$val['deposite'] =  $deposite;
                 if($row->igst_charge > 0 && isset($row->sgst_charge))
                 {
                     $val['igst_charge'] = number_format((float)$row->igst_charge, 2, '.', '');
                 }
                 else{
                     $val['igst_charge'] = '0';
                 }
                 if($row->cgst_charge > 0 && isset($row->sgst_charge))
                 {
                     $val['cgst_charge'] = number_format((float)$row->cgst_charge, 2, '.', '');
                 }
                 else{
                     $val['cgst_charge'] = '0';
                 }
                 if($row->sgst_charge > 0 && isset($row->sgst_charge))
                 {
                     $val['sgst_charge'] = number_format((float)$row->sgst_charge, 2, '.', '');
                 }
                 else{
                     $val['sgst_charge'] = '0';
                 }
				$total = $total+$row->deposit + $val['igst_charge'] + $val['sgst_charge'] + $val['cgst_charge'] ;
				$val['balance'] = number_format((float)$total, 2, '.', '');
				$rowReturn[] = $val;
			}
        }
        $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn,'total'=>$total);
          return json_encode($output);
    }
    public function emiTransactionsList(Request $request)
    {
        if ($request->ajax())
        {
            if ($request['loanType'] != "G")
            {
                /*$data = LoanDayBooks::where('loan_type', $request['loanType'])->where('is_deleted', 0)
                    ->where('loan_id', $request['loanId']);*/
                $data = \App\Models\LoanEmisNew::with(['loanEmiDetails','loanDetails'])->where('loan_id', $request['loanId'])->whereHas('loans', function ($q) {
                    $q->where('loan_type', 'L')->select('id', 'name', 'loan_type');
                })->where('is_deleted','0');
            }
            else
            {
                $data = \App\Models\LoanEmisNew::with(['loanEmiDetails','loanDetailsg'])->where('loan_id', $request['loanId'])->whereHas('loans', function ($q) {
                    $q->where('loan_type', 'G')->select('id', 'name', 'loan_type');
                })->where('is_deleted','0');
            }
            // $data1 = $data->where('is_deleted', '0')
            //     ->get();
            $data1 = $data->count();
            $count = $data1;
            $data = $data->offset($_POST['start'])->limit($_POST['length'])/*->orderby('emi_date', 'asc')*/
                ->get();
            $dataCount = $count;
            $totalCount = $dataCount;
            $sno = $_POST['start'];
            if($_POST['pages'] == 1)
			{
				$total  = 0;
			}
			else{
				$total  = $_POST['total'];
			}
            if($_POST['pages'] == "1"){
				$length = ($_POST['pages']) * $_POST['length'];
			} else {
				$length = ($_POST['pages']-1) * $_POST['length'];
			}
            $rowReturn = array();
            // pd($data);
            foreach ($data as $row)
            {
                $sno++;
                $val['deposite'] = $row->deposit;
                $val['DT_RowIndex'] = $sno;
                if(isset($row->emi_received_date))
                {
                    $val['date'] = date("d/m/Y", strtotime($row->emi_received_date));
                }
                else if(isset($row->emi_date)){
                    $val['date'] = date("d/m/Y", strtotime($row->emi_date));
                }
                else{
                    $val['date'] = date("d/m/Y", strtotime($row->created_at));
                }
                $paymentMode = 'N/A';
                if(isset($row['loanEmiDetails']->payment_mode))
                {
                    if ($row['loanEmiDetails']->payment_mode == 0)
                    {
                        $paymentMode = 'Cash';
                    }
                    elseif ($row['loanEmiDetails']->payment_mode == 1)
                    {
                        $paymentMode = 'Cheque';
                    }
                    elseif ($row['loanEmiDetails']->payment_mode == 2)
                    {
                        $paymentMode = 'DD';
                    }
                    elseif ($row['loanEmiDetails']->payment_mode == 3)
                    {
                        $paymentMode = 'Online Transaction';
                    }
                    elseif ($row['loanEmiDetails']->payment_mode == 4)
                    {
                        $paymentMode = 'By Saving Account ';
                    }
                    elseif ($row['loanEmiDetails']->payment_mode == 6)
                    {
                        $paymentMode = 'JV ';
                    }
                }
                else{
                    if ($row->payment_mode == 0)
                    {
                        $paymentMode = 'Cash';
                    }
                    elseif ($row->payment_mode == 1)
                    {
                        $paymentMode = 'Cheque';
                    }
                    elseif ($row->payment_mode == 2)
                    {
                        $paymentMode = 'DD';
                    }
                    elseif ($row->payment_mode == 3)
                    {
                        $paymentMode = 'Online Transaction';
                    }
                    elseif ($row->payment_mode == 4)
                    {
                        $paymentMode = 'By Saving Account ';
                    }
                    elseif ($row->payment_mode == 6)
                    {
                        $paymentMode = 'JV ';
                    }
                }
                $val['payment_mode'] = $paymentMode;
                if (isset($row['loanEmiDetails']->jv_journal_amount))
                {
                    $val['jv_amount'] = number_format((float)$row->jv_journal_amount, 2, '.', '');
                }
                else
                {
                    $val['jv_amount'] = 0;
                }
                if(isset($row['loanDetails']->transfer_amount))
                {
                    $val['sanction_amount'] = $row['loanDetails']->transfer_amount;
                }
                if(isset($row['loanDetailsg']->transfer_amount))
                {
                    $val['sanction_amount'] = $row['loanDetailsg']->transfer_amount;
                }
                if(isset($row['loanEmiDetails']->description))
                {
                    $val['description'] = $row['loanEmiDetails']->description;
                }
                else{
                    $val['description'] = "Loan EMI Deposit";
                }
                if(isset($row->penalty))
                {
                        $deposite = $row->penalty;
                        $val['penalty'] = $deposite;
                }
                else
                    {
                        $val['penalty'] = 0;
                    }
                if(isset($row['loanEmiDetails']->loan_sub_type))
                {
                    if ($row['loanEmiDetails']->loan_sub_type == 0)
                    {
                        $roi_amount = $row->roi_amount;
                        $val['roi_amount'] = $roi_amount  +  $row->daily_wise_interest;
                    }
                    else
                    {
                        $val['roi_amount'] = '0';
                    }
                }
                else{
                    $val['roi_amount'] =  $row->roi_amount;
                }
                if(isset($row['loanEmiDetails']->loan_sub_type))
                {
                    if ($row['loanEmiDetails']->loan_sub_type == 0)
                    {
                        $principal_amount = $row->principal_amount;
                        $val['principal_amount'] = $principal_amount;
                    }
                    else
                    {
                        $val['principal_amount'] = $row->principal_amount;
                    }
                }
                else{
                    $val['principal_amount'] =  $row->principal_amount;
                }
                if($request['loanType'] != 3)
                {
                    $val['opening_balance'] =  $row->opening_balance;
                }
                if(isset($row['loanEmiDetails']->loan_sub_type))
                {
                    $opening_balance = $row->out_standing_amount;
                    $val['opening_balance'] = $opening_balance;
                }
                else{
                    $val['opening_balance'] =  $row->out_standing_amount;
                }
                $penalty = 0;
                if(isset($row->penalty))
                {
                    $penalty = $row->penalty;
                }
                if (isset($row['loanEmiDetails']->jv_journal_amount))
                {
                    $total = $total + $row['loanEmiDetails']->jv_journal_amount;
                }
                else if(isset($row->deposit))
                {
                    $total = $total + $row->deposit + $penalty;
                }
                else{
                    $total = $total +0;
                }
                $val['balance'] = number_format((float)$total, 2, '.', '') . ' <i class="fas fa-rupee-sign"></i>';
                $rowReturn[] = $val;
            }
            /*return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('transaction_id', function($row){
                $transaction_id = $row->id;
                return $transaction_id;
            })
            ->rawColumns(['transaction_id'])
            ->addColumn('date', function($row){
                $date = date("d/m/Y", strtotime($row->payment_date));
                return $date;
            })
            ->rawColumns(['date'])
            ->addColumn('payment_mode', function($row){
                if($row->payment_mode == 0){
                    $paymentMode = 'Cash';
                }elseif($row->payment_mode == 1){
                    $paymentMode = 'Cheque';
                }elseif($row->payment_mode == 2){
                    $paymentMode = 'DD';
                }elseif($row->payment_mode == 3){
                    $paymentMode = 'Online Transaction';
                }elseif($row->payment_mode == 4){
                    $paymentMode = 'By Saving Account ';
                }
                return $paymentMode;
            })
            ->rawColumns(['payment_mode'])
            ->addColumn('description', function($row){
                $description =  $row->description;
                return $description;
            })
            ->rawColumns(['description'])
            ->addColumn('penalty', function($row){
                if($row->loan_sub_type == 1){
                    $deposite =  $row->deposit.' <i class="fas fa-rupee-sign"></i>';
                    return $deposite;
                }else{
                    return 'N/A';
                }
            })
            ->escapeColumns(['description'])
            ->addColumn('deposite', function($row){
                if($row->loan_sub_type == 0){
                    $deposite =  $row->deposit.' <i class="fas fa-rupee-sign"></i>';
                    return $deposite;
                }else{
                    return 'N/A';
                }
            })
            ->escapeColumns(['description'])
            ->addColumn('roi_amount', function($row){
                if($row->loan_sub_type == 0){
                    $roi_amount =  $row->roi_amount.' <i class="fas fa-rupee-sign"></i>';
                    return $roi_amount;
                }else{
                    return 'N/A';
                }
            })
            ->escapeColumns(['description'])
            ->addColumn('principal_amount', function($row){
                if($row->loan_sub_type == 0){
                    $principal_amount =  $row->principal_amount.' <i class="fas fa-rupee-sign"></i>';
                    return $principal_amount;
                }else{
                    return 'N/A';
                }
            })
            ->escapeColumns(['description'])
            ->addColumn('opening_balance', function($row){
                if($row->loan_sub_type == 0){
                    $opening_balance =  $row->opening_balance;
                    return $opening_balance;
                }else{
                    return 'N/A';
                }
            })
            ->rawColumns(['opening_balance'])
            ->make(true);*/
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $rowReturn,
            'totalAmount' =>$total,
        );
        return json_encode($output);
    }
}
