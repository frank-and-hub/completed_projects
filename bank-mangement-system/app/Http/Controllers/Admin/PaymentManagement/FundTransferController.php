<?php
namespace App\Http\Controllers\Admin\PaymentManagement;

use App\Http\Controllers\Admin\CommanController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use URL;
use Image;
use DB;
use Session;
use DateTime;
use DateInterval;
use Illuminate\Support\Facades\Cache;
use App\Models\Branch;
use App\Models\AccountHeads;
use App\Models\SamraddhCheque;
use App\Models\SamraddhBank;
use App\Models\FundTransfer;
use App\Models\SamraddhChequeIssue;
use App\Models\SamraddhBankClosing;
use App\Models\SamraddhBankAccount;
use App\Models\Files;
use App\Services\ImageUpload;
use App\Models\BranchCash;
use Spatie\Permission\Models\Permission;
use App\Models\CompanyBranch;
use App\Models\BankBalance;
use App\Models\Companies;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use App\Http\Traits\DeleteFundTransferTrait;
use App\Http\Traits\SamraddhChequeLogTrait;

class FundTransferController extends Controller
{
    use DeleteFundTransferTrait, SamraddhChequeLogTrait;
    /**
     * 
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Amount withdrawal view.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (check_my_permission(Auth::user()->id, "32") != "1") {
            return redirect()
                ->route('admin.dashboard');
        }
        $data['title'] = 'Bank To Bank Transfer';
        $data['BankToBankTransfer'] = check_my_permission(Auth::user()->id, "135");
        return view('templates.admin.payment-management.fund-transfer.index', $data);
    }
    /**
     * Get members list according to branch.
     * Route: ajax call from - /branch/member
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    // Call Form for BankToBank Transfer //
    public function createBankToBank()
    {
        if (check_my_permission(Auth::user()->id, "135") != "1") {
            return redirect()
                ->route('admin.dashboard');
        }
        $getBranchId = getUserBranchId(Auth::user()->id);
        $data['branch_id'] = $getBranchId->id;
        $data['title'] = 'Bank To Bank Transfer';
        $data['branches'] = Branch::select('id', 'name', 'branch_code')->get();
        $data['banks'] = SamraddhBank::select('id', 'bank_name', 'status')->with([
            'bankAccount' => function ($q) {
                $q->select('id', 'bank_id', 'account_no');
            }
        ])->whereStatus(1)
            ->get();
        $data['cheques'] = SamraddhCheque::select('id', 'cheque_no', 'account_id')->whereStatus(1)
            ->get();
        return view('templates.admin.payment-management.fund-transfer.createbanktobank', $data);
    }
    public function fundTransferListing(Request $request)
    {
        if ($request->ajax()) {
            if (!empty($_POST['searchIndex'])) {
                foreach ($_POST['searchIndex'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $companyId = '';
            $status = 0;
            $getBranchId = getUserBranchId(Auth::user()->id);
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['company_id'] != '') {
                    $companyId = $arrFormData['company_id'];
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                }
            }
            $branch_id = $getBranchId->id;
            $data = FundTransfer::has('company')->when($companyId != '0', function ($q) use ($companyId) {
                $q->whereCompanyId($companyId);
            })
                ->where('transfer_type', 1)
                ->where(function ($q) use ($status) {
                    $q->whereStatus($status);
                });
            if (isset($arrFormData['start_date']) && $arrFormData['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                if ($arrFormData['end_date'] != '') {
                    $endDate = date("Y-m-d", strtotime(convertDate($arrFormData['end_date'])));
                } else {
                    $endDate = '';
                }
                $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
            if (isset($arrFormData['bank']) && $arrFormData['bank'] != '') {
                $bank = $arrFormData['bank'];
                $data = $data->where('from_bank_id', $bank);
            }
            if (isset($arrFormData['bank_ac']) && $arrFormData['bank_ac'] < 0) {
                $bank_ac = $arrFormData['bank_ac'];
                $data = $data->where('from_bank_account_number', $bank_ac);
            }
            $data = $data->where('is_deleted', '0')->orderby('id', 'DESC');
            return Datatables::of($data)
                ->addColumn('date', function ($row) {
                    return date('d/m/Y', strtotime($row->transfer_date_time));
                })
                ->addColumn('from_bank', function ($row) {
                    if (getSamraddhBank($row->from_bank_id)) {
                        $bankDetails = getSamraddhBank($row->from_bank_id);
                        return $bankDetails->bank_name;
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['from_bank'])
                ->addColumn('from_bank_account_number', function ($row) {
                    return $row->from_bank_account_number;
                })
                ->rawColumns(['from_bank_account_number'])
                ->addColumn('company', function ($row) {
                    $companyName = Companies::withoutGlobalScopes()->whereId($row->company_id)->first('name');
                    return ($companyName ? $companyName->name : 'N/A');
                })
                ->rawColumns(['company'])
                /*->addColumn('transfer_mode', function($row){
                  if( $row->transfer_mode == 0 ){
                   return 'Loan';
                  } elseif ( $row->transfer_mode == 1 ) {
                   return 'Miro';
                  } else {
                   return 'None';
                  }
              })
            ->rawColumns(['transfer_mode'])*/
                ->addColumn('to_bank', function ($row) {
                    if (getSamraddhBank($row->to_bank_id)) {
                        $bankDetails = getSamraddhBank($row->to_bank_id);
                        return $bankDetails->bank_name;
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['to_bank'])
                ->addColumn('bank_account_number', function ($row) {
                    return $row->to_bank_account_number;
                })
                ->rawColumns(['bank_account_number'])
                ->addColumn('transfer_amount', function ($row) {
                    return $row->transfer_amount . ' &#8377';
                })
                ->escapeColumns(['bank_account_number'])
                ->addColumn('transfer_mode', function ($row) {
                    if ($row->btb_tranfer_mode == 0) {
                        return 'Cheque';
                    } elseif ($row->btb_tranfer_mode == 1) {
                        return 'Online Transfer';
                    }
                })
                ->rawColumns(['transfer_mode'])
                ->addColumn('cheque_no', function ($row) {
                    if ($row->btb_tranfer_mode == 0) {
                        return $row->from_cheque_utr_no;
                    } elseif ($row->btb_tranfer_mode == 1) {
                        return 'N/A';
                    }
                })
                ->rawColumns(['cheque_no'])
                ->addColumn('utr_no', function ($row) {
                    if ($row->btb_tranfer_mode == 0) {
                        return 'N/A';
                    } elseif ($row->btb_tranfer_mode == 1) {
                        return $row->from_cheque_utr_no;
                    }
                })
                ->rawColumns(['utr_no'])
                ->addColumn('rtgs_neft_charges', function ($row) {
                    if ($row->rtgs_neft_charge) {
                        return $row->rtgs_neft_charge;
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['rtgs_neft_charges'])
                ->addColumn('remark', function ($row) {
                    return $row->remark;
                })
                ->rawColumns(['remark'])
                ->addColumn('status', function ($row) {
                    if ($row->status == 0) {
                        $status = 'Pending';
                    } elseif ($row->status == 1) {
                        $status = 'Approved';
                    } /*elseif( $row->status == 2 ) {
    $status = 'Admin Updated';
   } elseif( $row->status == 3 ) {
    $status = 'Rejected';
   } */ else {
                        $status = '';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->addColumn('action', function ($row) {
                    /*
                    if ($row->status != 1) {
                        $url = URL::to("admin/fund-transfer-detail/" . $row->id . '/' . $row->from_cheque_utr_no);
                        // $statusUrl = URL::to("admin/fund-transfer/update-status/" . $row->id . "/1/0/" . $row->company_id);
                        $statusUrl = URL::to("admin/fund-transfer/update-status/" . $row->id . "/1/" . $row->branch_id . "/" . $row->company_id);
                        $url3 = URL::to("admin/delete/branch-to-ho/" . $row->id . "");
                        $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                        $btn .= '<a class="dropdown-item" href="' . $url . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                        if (($row->status == 0 || $row->status == 2) && $row->status != 1) {
                            // $btn .= '<a class="dropdown-item approve-requestt" href="' . $statusUrl . '"><i class="fa fa-check" aria-hidden="true"></i>Approve</a>';
                            $btn .= '<a class="dropdown-item approve-requestt" onclick="statusUpdateBtoB(' . $row->id . ', 1, ' . $row->branch_id . ', ' . $row->company_id . ')"><i class="fa fa-check" aria-hidden="true"></i>Approve</a>';
                        }
                        if (($row->status == 0 || $row->status == 2) && ($row->status != 1 && $row->status != 3)) {
                            $btn .= '<a class="dropdown-item  delete-bank-to-bank-transfer" href="' . $url3 . '"><i class="fa fa-trash-o" aria-hidden="true"></i>Delete</a>';
                        }
                        $btn .= '</div></div></div>';
                    } else {
                        $btn = 'No Action';
                    }
                    */
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    if ($row->status != 1) {
                        $url = URL::to("admin/fund-transfer-detail/" . base64_encode($row->id) . '/' . base64_encode($row->from_cheque_utr_no));
                        // $statusUrl = URL::to("admin/fund-transfer/update-status/" . $row->id . "/1/0/" . $row->company_id);
                        $statusUrl = URL::to("admin/fund-transfer/update-status/" . $row->id . "/1/" . $row->branch_id . "/" . $row->company_id);
                        $url3 = URL::to("admin/delete/branch-to-ho/" . $row->id . "");
                        $btn .= '<a class="dropdown-item" href="' . $url . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                        if (($row->status == 0 || $row->status == 2) && $row->status != 1) {
                            // $btn .= '<a class="dropdown-item approve-requestnew" href="' . $statusUrl . '"><i class="fa fa-check" aria-hidden="true"></i>Approve</a>';
                            $btn .= '<a class="dropdown-item approve-requestnew" onclick="statusUpdateBtoB(' . $row->id . ', 1, ' . $row->branch_id . ', ' . $row->company_id . ')"><i class="fa fa-check" aria-hidden="true"></i>Approve</a>';
                        }
                        if (($row->status == 0 || $row->status == 2) && ($row->status != 1 && $row->status != 3)) {
                            if (check_my_permission(Auth::user()->id, "353") == "1") {
                                $btn .= '<a class="dropdown-item delete-transfer" data-title="request" data-id="' . $row->id . '" href="javascript:void(0)" title="Fund transfer bank to bank delete" ><i class="fa fa-trash-o" aria-hidden="true"></i>Delete</a>';
                            }
                        }
                    } else {
                        $url3 = URL::to("admin/delete/approved/branch-to-ho/" . $row->id . "");
                        if ($row->is_deleted == '0') {
                            if (check_my_permission(Auth::user()->id, "355") == "1") {
                                $btn .= '<a class="dropdown-item delete-transfer" data-title="payment" data-id="' . $row->id . '" href="javascript:void(0)" title="Fund transfer bank to bank un-approve" ><i class="fa fa-trash-o" aria-hidden="true"></i>Un-approve</a>';
                            }
                        }
                    }
                    $logData = \App\Models\FundTransferLog::whereFundsTransferId($row->id)->exists();
                    if ($logData) {
                        $logUrl = url("admin/fund-transfer/logs/" . base64_encode($row->id));
                        $btn .= '<a class="dropdown-item" target="_blank" href="' . $logUrl . '" title="Fund Transfer Logs"><i class="fa fa-list-alt" aria-hidden="true"></i>Logs </a> ';
                    }
                    $btn .= '</div></div></div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    public function edit($i, $c)
    {
        $data['chequeId'] = base64_decode($c);
        $id = base64_decode($i);
        $data['data'] = $d = FundTransfer::whereId($id)->with('file')->first();
        //$data->banks = AccountHeads::select('id','title','account_number')->where('account_type',2)->get()->toArray();
        $data['banks'] = SamraddhBank::whereCompanyId($d->company_id)->whereStatus(1)->get();
        $data['toBankno'] = SamraddhBankAccount::select('account_no', 'id')->where('account_no', $d->to_bank_account_number)->whereStatus(1)->first();
        $data['fromBankno'] = SamraddhBankAccount::select('account_no', 'id')->where('account_no', $d->from_bank_account_number)->whereStatus(1)->first();
        $data['toBank'] = SamraddhBank::select('bank_name', 'id')->whereStatus(1)->whereId($d->to_bank_id)->first();
        $data['fromBank'] = SamraddhBank::select('bank_name', 'id')->whereStatus(1)->whereId($d->from_bank_id)->first();
        // $branches = Branch::select('id', 'name', 'branch_code')->get()->toArray();
        // $cheques = SamraddhCheque::select('id', 'cheque_no', 'account_id')->whereStatus( 1)->get();
        // $selectedcheque = SamraddhCheque::select('cheque_no', 'account_id')->where('cheque_no', $cheque)->first();
        // if ($data->btb_tranfer_mode == 0) {
        //     $cheques = $cheques->push($selectedcheque);
        // }
        if (!$data['fromBank']) {
            return back()->with('alert', 'Fund Transfer From Bank is Inactive or not found please try again !');
        }
        $data['title'] = 'Bank To Bank Transfer | Edit';
        return view('templates.admin.payment-management.fund-transfer.bank-to-bank', $data);
    }
    public function updateStatus($id, $status, $branchid, $companyId)
    {
        $branchName = getBranchDetail($branchid);
        $branchMember = $branchName->member_id;
        DB::beginTransaction();
        try {
            $globalDateTime = checkMonthAvailability(date('d'), date('m'), date('Y'), $branchName->state_id);
            $ftDetails = FundTransfer::whereId($id)->first();            
            if ($ftDetails->status == 0) {
                Session::put('created_at', $ftDetails->transfer_date_time);
                $response = FundTransfer::whereId($id)->update(['status' => $status]);
                $dayBookRef = CommanController::createBranchDayBookReference($ftDetails->amount);
                $branchDetail = \App\Models\Branch::whereId($ftDetails->branch_id)->first();
                if ($ftDetails->transfer_type == 0) {
                    $transferAmount = ($branchDetail->day_closing_amount - $branchDetail->cash_in_hand);

                    $branchDayBook = CommanController::createBranchDayBookModify($dayBookRef, $ftDetails->branch_id, 7, 70, $ftDetails->id, $id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $ftDetails->branch_id, $ftDetails->amount, 'From Branch ' . getBranchDetail($ftDetails->branch_id)->name . ' ' . $ftDetails->amount . ' A/C Dr To Bank ' . getSamraddhBank($ftDetails->head_office_bank_id)->bank_name . ' A/C Cr', 'Cash A/c Dr ' . $ftDetails->amount . ' From Branch ' . getBranchDetail($ftDetails->branch_id)->name . '', 'Cash A/c Cr ' . $ftDetails->amount . ' To Bank ' . getSamraddhBank($ftDetails->head_office_bank_id)->bank_name . '', 'DR', 0, 'INR', $v_no = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $transction_no = NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $companyId);

                    $allHeadTransaction = CommanController::createAllHeadTransactionModify($dayBookRef, $ftDetails->branch_id, $ftDetails->head_office_bank_id, getSamraddhBankAccount($ftDetails->head_office_bank_account_number)->id, 28, 7, 70, $ftDetails->id, $id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $ftDetails->branch_id, $ftDetails->amount, 'From Branch ' . getBranchDetail($ftDetails->branch_id)->name . ' ' . $ftDetails->amount . ' A/C Dr To Bank ' . getSamraddhBank($ftDetails->head_office_bank_id)->bank_name . ' A/C Cr', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no = NULL, $ssb_account_id_from = NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $companyId);

                    $allHeadTransaction = CommanController::createAllHeadTransactionModify($dayBookRef, $ftDetails->branch_id, $ftDetails->head_office_bank_id, getSamraddhBankAccount($ftDetails->head_office_bank_account_number)->id, getSamraddhBankAccount($ftDetails->head_office_bank_account_number)->account_head_id, 7, 70, $ftDetails->id, $id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $ftDetails->branch_id, $ftDetails->amount, 'From Branch ' . getBranchDetail($ftDetails->branch_id)->name . ' ' . $ftDetails->amount . ' A/C Dr To Bank ' . getSamraddhBank($ftDetails->head_office_bank_id)->bank_name . ' A/C Cr', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no = NULL, $ssb_account_id_from = NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $companyId);

                    $samraddhBankDaybook = CommanController::createSamraddhBankDaybookModify($dayBookRef, $ftDetails->head_office_bank_id, getSamraddhBankAccount($ftDetails->head_office_bank_account_number)->id, 7, 70, $ftDetails->id, $id, $associate_id = NULL, $member_id = NULL, $ftDetails->branch_id, $ftDetails->amount, $ftDetails->amount, $ftDetails->amount, 'From Branch ' . getBranchDetail($ftDetails->branch_id)->name . ' ' . $ftDetails->amount . ' A/C Dr To Bank ' . getSamraddhBank($ftDetails->head_office_bank_id)->bank_name . ' A/C Cr', 'Cash A/c Dr ' . $ftDetails->amount . ' From Branch ' . getBranchDetail($ftDetails->branch_id)->name . '', 'Cash A/c Cr ' . $ftDetails->amount . ' To Bank ' . getSamraddhBank($ftDetails->head_office_bank_id)->bank_name . '', 'CR', 0, 'INR', $ftDetails->head_office_bank_id, getSamraddhBank($ftDetails->head_office_bank_id)->bank_name, $ftDetails->branch_id, getBranchDetail($ftDetails->branch_id)->name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $ftDetails->head_office_bank_id, $ftDetails->head_office_bank_account_number, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $ftDetails->head_office_bank_id, getSamraddhBank($ftDetails->head_office_bank_id)->bank_name, getSamraddhBank($ftDetails->head_office_bank_id)->branch_name, getSamraddhBank($ftDetails->head_office_bank_id)->ifsc_code, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $companyId);

                    // $updateBranchBankBalance = CommanController::updateBackDateBranchBankBalance($dayBookRef, $ftDetails->amount, $ftDetails->branch_id, $ftDetails->transfer_mode, $ftDetails->transfer_type, $ftDetails->head_office_bank_id, getSamraddhBankAccount($ftDetails->head_office_bank_account_number)->id, $from_bank_id = NULL, $from_account_id = NULL, $to_bank_id = NULL, $to_account_id = NULL, $ftDetails->transfer_date_time, 0);
                                       
                    if (isset($branchDetail->first_login) && $branchDetail->first_login == 0) {
                        if ($transferAmount <= $ftDetails->amount) {
                            $branchDetail->update(['first_login' => '1']);
                        }
                        if ($branchDetail->day_closing_amount < $branchDetail->cash_in_hand) {
                            $branchDetail->update(['day_closing_amount' => $branchDetail->day_closing_amount]);
                        } else {
                            $branchDetail->update(['day_closing_amount' => ($branchDetail->day_closing_amount - $ftDetails->amount)]);
                        }
                    }
                } elseif ($ftDetails->transfer_type == 1) {
                    if ($ftDetails->btb_tranfer_mode == 0) {
                        $cheque_type = 1;
                        $cheque_id = getSamraddhChequeData($ftDetails->from_cheque_utr_no)->id;
                        $cheque_no = getSamraddhChequeData($ftDetails->from_cheque_utr_no)->cheque_no;
                        $cheque_date = getSamraddhChequeData($ftDetails->from_cheque_utr_no)->cheque_create_date;
                        $cheque_bank_from = $ftDetails->from_bank_id;
                        $transction_bank_from = $ftDetails->from_bank_id;
                        $cheque_bank_ac_from = $ftDetails->from_bank_account_number;
                        $transction_bank_ac_from = $ftDetails->from_bank_account_number;
                        $cheque_bank_ifsc_from = getSamraddhBankAccount($ftDetails->from_bank_account_number)->ifsc_code;
                        $transction_bank_ifsc_from = getSamraddhBankAccount($ftDetails->from_bank_account_number)->ifsc_code;
                        $transction_bank_from_ac_id = getSamraddhBankAccount($ftDetails->from_bank_account_number)->id;
                        $cheque_bank_branch_from = NULL;
                        $cheque_bank_to = $ftDetails->to_bank_id;
                        $transction_bank_to = $ftDetails->to_bank_id;
                        $cheque_bank_to_name = getSamraddhBank($ftDetails->to_bank_id)->bank_name;
                        $cheque_bank_ac_to = $ftDetails->to_bank_account_number;
                        $transction_bank_ac_to = $ftDetails->to_bank_account_number;
                        $cheque_bank_to_ifsc = getSamraddhBankAccount($ftDetails->to_bank_account_number)->ifsc_code;
                        SamraddhCheque::where('cheque_no', getSamraddhChequeData($ftDetails->from_cheque_utr_no)
                            ->cheque_no)
                            ->update(['status' => 2, 'is_use' => 1]);
                        // $created_at = date("Y-m-d", strtotime(convertDate($ftDetails->transfer_date_time)));
                        // $updatechequeId = $this->chequeLog($dayBookRef, null, $created_at, getSamraddhChequeData($ftDetails->from_cheque_utr_no)->cheque_no, "Fund Transfer", null, $ftDetails->to_bank_account_number);
                        // if($updatechequeId){
                        //     \App\Models\ChequeLog::find($updatechequeId)->update(['day_ref_id' => $dayBookRef]);
                        // }
                        SamraddhChequeIssue::create([
                            'cheque_id' => getSamraddhChequeData($ftDetails->from_cheque_utr_no)->id,
                            'type' => 1,
                            'sub_type' => 11,
                            'type_id' => $ftDetails->id,
                            'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($ftDetails->transfer_date_time))),
                            'status' => 1,
                        ]);
                        $sub_type = 81;
                        $payment_mode = 1;
                        $transferAmount = $ftDetails->transfer_amount;
                        $transction_no = NULL;
                    } elseif ($ftDetails->btb_tranfer_mode == 1) {
                        $transction_bank_from = $ftDetails->from_bank_id;
                        $transction_bank_ac_from = $ftDetails->from_bank_account_number;
                        $transction_bank_ifsc_from = getSamraddhBankAccount($ftDetails->from_bank_account_number)->ifsc_code;
                        $transction_bank_from_ac_id = getSamraddhBankAccount($ftDetails->from_bank_account_number)->id;
                        $transction_bank_to = $ftDetails->to_bank_id;
                        $transction_bank_ac_to = $ftDetails->to_bank_account_number;
                        $cheque_type = NULL;
                        $cheque_id = NULL;
                        $cheque_no = NULL;
                        $cheque_date = NULL;
                        $cheque_bank_from = NULL;
                        $cheque_bank_ac_from = NULL;
                        $cheque_bank_ifsc_from = NULL;
                        $cheque_bank_to_name = NULL;
                        $cheque_bank_branch_from = NULL;
                        $cheque_bank_to = NULL;
                        $cheque_bank_ac_to = NULL;
                        $sub_type = 82;
                        $payment_mode = 2;
                        $cheque_bank_to_ifsc = NULL;
                        $transction_no = $ftDetails->from_cheque_utr_no;
                        $transferAmount = ($ftDetails->transfer_amount + $ftDetails->rtgs_neft_charge);
                    }
                    $pAmount = $ftDetails->transfer_amount;

                    $allHeadTransaction = CommanController::createAllHeadTransactionModify($dayBookRef, $ftDetails->branch_id, $ftDetails->from_bank_id, getSamraddhBankAccount($ftDetails->from_bank_account_number)->id, getSamraddhBankAccount($ftDetails->from_bank_account_number)->account_head_id, 8, $sub_type, $ftDetails->from_bank_id, $id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $transferAmount, 'From Bank ' . getSamraddhBank($ftDetails->from_bank_id)->bank_name . ' A/C Dr ' . $transferAmount . ' To Bank ' . getSamraddhBank($ftDetails->to_bank_id)->bank_name . ' A/C Cr ', 'CR', $payment_mode, 'INR', $jv_unique_id = NULL, $v_no = NULL, $ssb_account_id_from = NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type, $cheque_id, $cheque_no, $transction_no, 1, Auth::user()->id, $companyId);

                    $allHeadTransaction = CommanController::createAllHeadTransactionModify($dayBookRef, $ftDetails->branch_id, $ftDetails->to_bank_id, getSamraddhBankAccount($ftDetails->to_bank_account_number)->id, getSamraddhBankAccount($ftDetails->to_bank_account_number)->account_head_id, 8, $sub_type, $ftDetails->from_bank_id, $id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $pAmount, 'From Bank ' . getSamraddhBank($ftDetails->from_bank_id)->bank_name . ' A/C Dr ' . $pAmount . ' To Bank ' . getSamraddhBank($ftDetails->to_bank_id)->bank_name . ' A/C Cr ', 'DR', $payment_mode, 'INR', $jv_unique_id = NULL, $v_no = NULL, $ssb_account_id_from = NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type, $cheque_id, $cheque_no, $transction_no, 1, Auth::user()->id, $companyId);

                    /*
                    $allTransaction = CommanController::createAllTransaction($dayBookRef,$ftDetails->branch_id,$ftDetails->from_bank_id,getSamraddhBankAccount($ftDetails->to_bank_account_number)->id,2,10,27,getSamraddhBankAccount($ftDetails->to_bank_account_number)->account_head_id,$head5=NULL,8,$sub_type,$ftDetails->from_bank_id,$id,$associate_id=NULL,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$pAmount,$pAmount,$pAmount,'From Bank '.getSamraddhBank($ftDetails->from_bank_id)->bank_name.' A/C Dr '.$pAmount.' To Bank '.getSamraddhBank($ftDetails->to_bank_id)->bank_name.' A/C Cr ','CR',$payment_mode,'INR',$ftDetails->to_bank_id,getSamraddhBank($ftDetails->to_bank_id)->bank_name,$ftDetails->from_bank_id,getSamraddhBank($ftDetails->from_bank_id)->bank_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$ftDetails->from_bank_id,getSamraddhBankAccount($ftDetails->to_bank_account_number)->id,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$ftDetails->to_bank_id,getSamraddhBankAccount($ftDetails->to_bank_account_number)->id,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,Auth::user()->id,$created_at=NULL);
                    */

                    $samraddhBankDaybook = CommanController::createSamraddhBankDaybookModify($dayBookRef, $ftDetails->from_bank_id, getSamraddhBankAccount($ftDetails->from_bank_account_number)->id, 8, $sub_type, $ftDetails->from_bank_id, $id, $associate_id = NULL, $member_id = NULL, $branch_id = 29, $pAmount, $pAmount, $pAmount, 'From Bank ' . getSamraddhBank($ftDetails->from_bank_id)->bank_name . ' A/C Dr ' . $pAmount . ' To Bank ' . getSamraddhBank($ftDetails->to_bank_id)->bank_name . ' A/C Cr', 'Cash A/c Dr ' . $pAmount . ' From Bank ' . getSamraddhBank($ftDetails->from_bank_id)->bank_name . '', 'Cash A/c Cr ' . $pAmount . ' To Bank ' . getSamraddhBank($ftDetails->to_bank_id)->bank_name . '', 'DR', $payment_mode, 'INR', $ftDetails->to_bank_id, getSamraddhBank($ftDetails->to_bank_id)->bank_name, $ftDetails->from_bank_id, getSamraddhBank($ftDetails->from_bank_id)->bank_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $ftDetails->from_bank_id, getSamraddhBankAccount($ftDetails->from_bank_account_number)->id, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $ftDetails->to_bank_id, getSamraddhBankAccount($ftDetails->to_bank_account_number)->id, $ftDetails->to_bank_id, getSamraddhBank($ftDetails->to_bank_id)->bank_name, getSamraddhBank($ftDetails->to_bank_id)->branch_name, getSamraddhBank($ftDetails->to_bank_id)->ifsc_code, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $companyId);

                    $samraddhBankDaybook = CommanController::createSamraddhBankDaybookModify($dayBookRef, $ftDetails->to_bank_id, getSamraddhBankAccount($ftDetails->to_bank_account_number)->id, 8, $sub_type, $ftDetails->to_bank_id, $id, $associate_id = NULL, $member_id = NULL, $branch_id = 29, $pAmount, $pAmount, $pAmount, 'From Bank ' . getSamraddhBank($ftDetails->from_bank_id)->bank_name . ' A/C Dr ' . $pAmount . ' To Bank ' . getSamraddhBank($ftDetails->to_bank_id)->bank_name . ' A/C Cr', 'Cash A/c Dr ' . $pAmount . ' From Bank ' . getSamraddhBank($ftDetails->from_bank_id)->bank_name . '', 'Cash A/c Cr ' . $pAmount . ' To Bank ' . getSamraddhBank($ftDetails->to_bank_id)->bank_name . '', 'CR', $payment_mode, 'INR', $ftDetails->to_bank_id, getSamraddhBank($ftDetails->to_bank_id)->bank_name, $ftDetails->from_bank_id, getSamraddhBank($ftDetails->from_bank_id)->bank_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $ftDetails->from_bank_id, getSamraddhBankAccount($ftDetails->from_bank_account_number)->id, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $ftDetails->to_bank_id, getSamraddhBankAccount($ftDetails->to_bank_account_number)->id, $ftDetails->to_bank_id, getSamraddhBank($ftDetails->to_bank_id)->bank_name, getSamraddhBank($ftDetails->to_bank_id)->branch_name, getSamraddhBank($ftDetails->to_bank_id)->ifsc_code, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $companyId);

                    if ($ftDetails->rtgs_neft_charge) {
                        $samraddhBankDaybook = CommanController::createSamraddhBankDaybookModify($dayBookRef, $ftDetails->from_bank_id, getSamraddhBankAccount($ftDetails->from_bank_account_number)->id, 8, $sub_type, $ftDetails->from_bank_id, $id, $associate_id = NULL, $member_id = NULL, $branch_id = 29, $ftDetails->rtgs_neft_charge, $ftDetails->rtgs_neft_charge, $ftDetails->rtgs_neft_charge, 'NEFT CHARGE', 'Cash A/c Dr ' . $pAmount . ' From Bank ' . getSamraddhBank($ftDetails->from_bank_id)->bank_name . '', 'Cash A/c Cr ' . $pAmount . ' To Bank ' . getSamraddhBank($ftDetails->to_bank_id)->bank_name . '', 'DR', $payment_mode, 'INR', $ftDetails->to_bank_id, getSamraddhBank($ftDetails->to_bank_id)->bank_name, $ftDetails->from_bank_id, getSamraddhBank($ftDetails->from_bank_id)->bank_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $ftDetails->from_bank_id, getSamraddhBankAccount($ftDetails->from_bank_account_number)->id, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $ftDetails->to_bank_id, getSamraddhBankAccount($ftDetails->to_bank_account_number)->id, $ftDetails->to_bank_id, getSamraddhBank($ftDetails->to_bank_id)->bank_name, getSamraddhBank($ftDetails->to_bank_id)->branch_name, getSamraddhBank($ftDetails->to_bank_id)->ifsc_code, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $companyId);
                    }
                    if ($ftDetails->btb_tranfer_mode == 1) {

                        $allHeadTransaction = CommanController::createAllHeadTransactionModify($dayBookRef, $ftDetails->branch_id, $ftDetails->from_bank_id, getSamraddhBankAccount($ftDetails->from_bank_account_number)->id, 92, 8, 82, $ftDetails->from_bank_id, $id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $ftDetails->rtgs_neft_charge, 'NEFT Charge A/c Cr ' . $ftDetails->rtgs_neft_charge . '', 'DR', 2, 'INR', $jv_unique_id = NULL, $v_no = NULL, $ssb_account_id_from = NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type, $cheque_id, $cheque_no, $transction_no, 1, Auth::user()->id, $companyId);

                        /*
                        $allTransaction = CommanController::createAllTransaction($dayBookRef,$ftDetails->branch_id,$ftDetails->from_bank_id,getSamraddhBankAccount($ftDetails->to_bank_account_number)->id,4,86,92,$head4=NULL,$head5=NULL,8,82,$ftDetails->from_bank_id,$id,$associate_id=NULL,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$ftDetails->rtgs_neft_charge,$ftDetails->rtgs_neft_charge,$ftDetails->rtgs_neft_charge,'NEFT Charge A/c Cr '.$ftDetails->rtgs_neft_charge.'','CR',2,'INR',$ftDetails->to_bank_id,getSamraddhBank($ftDetails->to_bank_id)->bank_name,$ftDetails->from_bank_id,getSamraddhBank($ftDetails->from_bank_id)->bank_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no,$ftDetails->from_bank_id,getSamraddhBankAccount($ftDetails->to_bank_account_number)->id,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$ftDetails->to_bank_id,getSamraddhBankAccount($ftDetails->to_bank_account_number)->id,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,Auth::user()->id,$created_at=NULL);
                        $updateBranchBankBalance = CommanController::updateBackDateBranchBankBalance($dayBookRef, $ftDetails->transfer_amount, $branch_id = NULL, 1, $ftDetails->transfer_type, $bank_id = NULL, $account_id = NULL, $ftDetails->from_bank_id, getSamraddhBankAccount($ftDetails->from_bank_account_number)->id, $ftDetails->to_bank_id, getSamraddhBankAccount($ftDetails->to_bank_account_number)->id, $ftDetails->transfer_date_time, $ftDetails->rtgs_neft_charge);
                        } else {
                            $updateBranchBankBalance = CommanController::updateBackDateBranchBankBalance($dayBookRef, $ftDetails->transfer_amount, $branch_id = NULL, 1, $ftDetails->transfer_type, $bank_id = NULL, $account_id = NULL, $ftDetails->from_bank_id, getSamraddhBankAccount($ftDetails->from_bank_account_number)->id, $ftDetails->to_bank_id, getSamraddhBankAccount($ftDetails->to_bank_account_number)->id, $ftDetails->transfer_date_time, 0);
                        }
                        $updateBranchBankBalance = CommanController::updateBranchBankBalance($dayBookRef,$ftDetails->transfer_amount,$branch_id=NULL,$ftDetails->transfer_mode,$ftDetails->transfer_type,$bank_id=NULL,$ftDetails->from_bank_id,$ftDetails->to_bank_id);
                        */

                        $type = FundTransfer::whereId($id)->first(['id', 'transfer_type', 'to_cheque_utr_no']);
                        if ($type->transfer_type === 1) {
                            SamraddhCheque::where('cheque_no', $type->to_cheque_utr_no)->update(['status' => 3]);
                            // $created_at = date("Y-m-d", strtotime(convertDate($globalDateTime)));
                            // $updatechequeId = $this->chequeLog($dayBookRef, null, $created_at, $type->to_cheque_utr_no, "Fund Transfer ", null, $ftDetails->to_bank_account_number);
                            // if($updatechequeId){
                            //     \App\Models\ChequeLog::find($updatechequeId)->update(['day_ref_id' => $dayBookRef]);
                            // }
                        }
                    }
                }

                $log = [
                    'funds_transfer_id' => $ftDetails->id,
                    'type' => $ftDetails->transfer_type, // 0 : branch to ho , 1 : bank to bank
                    'old_value' => json_encode(['id' => $ftDetails->id, 'status' => $ftDetails->status]),
                    'new_value' => json_encode(['id' => $ftDetails->id, 'status' => '1']),
                    'amount' => $ftDetails->amount,
                    'title' => 'Approved ' . ($ftDetails->transfer_type == 1 ? "Bank To Bank" : "Branch To Head Office") . ' Payment Request',
                    'remark' => '',
                    'created_by' => auth()->user()->role_id == 3 ? '2' : '1',
                    'created_by_id' => auth()->user()->id,
                    'created_at' => date("Y-m-d H:i:s", strtotime(convertDate($globalDateTime))),
                ];
                \App\Models\FundTransferLog::create($log);
                if ($branchDetail->first_login == '1') {
                    $branchDetail->update(['first_login' => '0']);
                }
                DB::commit();
                return response()->json(['data'=>true]);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json(['error'=> "" . $ex->getMessage() .' - '. $ex->getLine() .' - '. $ex->getMessage() .' - '. $ex->getFile() . ""]);
        }
    }
    public function fundTransferHeadOffice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
            'company_id' => 'required',
            'branch_code' => 'required',
            'transfer_mode' => 'required',
            'transfer_amount' => 'required',
        ]);
        if ($validator->fails()) {
            return back()
                ->with('alert', 'Please fill the correct values!');
        }
        $entry_date = date("Y-m-d", strtotime(convertDate($request->date)));
        $getBranchAmount = \App\Models\Branch::whereId($request->branch_id)->value('total_amount');
        $branch_total_amount = $request->company_id == 1 ? $getBranchAmount : 0;
        $startDate = ($request->company_id == 1) ? date("Y-m-d", strtotime(convertDate('2021-08-05'))) : '';
        // $balance = \App\Models\BranchCurrentBalance::where('branch_id', $request->branch_id)->whereCompanyId($request->company_id)->whereDate('entry_date', '<=', $entry_date)->sum('totalAmount');
        $bankBla = \App\Models\BranchCurrentBalance::where('branch_id', $request->branch_id)->whereCompanyId($request->company_id)->when($startDate != '', function ($q) use ($startDate) {
            $q->whereDate('entry_date', '>=', $startDate);
        })->where('entry_date', '<=', $entry_date);
        if ($request->company_id != '') {
            $bankBla = $bankBla->whereCompanyId($request->company_id);
        }
        $bankBla = $bankBla->sum('totalAmount');
        $balance = number_format((float) ($bankBla ?? 0.00), 2, '.', '');
        if (($balance + $branch_total_amount) < $request->transfer_amount) {
            // if ($balance  <   $request->transfer_amount) {
            return back()
                ->with('alert', 'Not sufficiant balance!');
        }
        $bankSlipId = NULL;
        if ($request->hasFile('bank_slip')) {
            $validator = Validator::make($request->all(), [
                'bank_slip' => 'mimes:jpeg,jpg,png,gif|required',
            ]);
            if ($validator->fails()) {
                return back()
                    ->with('alert', 'Please upload only Jpeg,jpg,png file!');
            }
            $bankSlipImage = $request->file('bank_slip');
            $bankSlipName = time() . '.' . $bankSlipImage->getClientOriginalExtension();
            $bankSlipLocation = 'fund_transfer';
            ImageUpload::upload($bankSlipImage, $bankSlipLocation, $bankSlipName);
            $bankSlip = Files::create([
                'file_name' => $bankSlipName,
                'file_path' => $bankSlipLocation,
                'file_extension' => $bankSlipImage->getClientOriginalExtension()
            ]);
            $bankSlipId = $bankSlip->id;
        }
        $branch = getBranchDetail($request->branch_id);
        $data = array();
        $t = date("H:i:s");
        $data['transfer_type'] = 0;
        $data['branch_id'] = $request->branch_id;
        $data['branch_code'] = $request->branch_code;
        $data['transfer_date_time'] = date("Y-m-d " . $t . "", strtotime(convertDate($request->date)));
        $data['transfer_mode'] = $request->transfer_mode;
        $data['amount'] = $request->transfer_amount;
        $data['loan_day_book_amount'] = $request->loan_daybook_amount;
        $data['head_office_bank_id'] = $request->bank;
        $data['company_id'] = $request->company_id;
        //$data['head_office_bank_account_number'] = AccountHeads::find($request->bank)->account_number;
        $data['head_office_bank_account_number'] = $request->from_Bank_account_no;
        $data['bank_slip_id'] = $bankSlipId;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($request->date)));
        $data['micro_day_book_amount'] = $balance;
        $response = FundTransfer::create($data);
        // $fund_transfer_id = DB::getPdo()->lastInsertId();
        $data['bank_slip_id'] = fileI($bankSlipId);
        $encodeDate = json_encode($data);
        \App\Models\FundTransferLog::create([
            'funds_transfer_id' => $response->id,
            'type' => $response->transfer_type, // 0 : branch to ho , 1 : bank to bank
            'old_value' => $encodeDate,
            'new_value' => $encodeDate,
            'amount' => $response->amount,
            'title' => 'Create Branch to Head Office Payment Request',
            'remark' => '',
            'created_by' => auth()->user()->role_id == 3 ? '2' : '1',
            'created_by_id' => auth()->user()->id,
            'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($request->date))),
        ]);
        if ($branch->first_login == '0') {
            $branch->update(['first_login' => '1']);
        }
        branchbalancecrone($branch->manager_id, Permission::all());
        // DB::table('user_log')->insert($arrs);
        if ($response) {
            return redirect()->route('admin.fund-transfer.branchToHo')->with('success', 'Fund transfer request created successfully!');
        } else {
            return back()->with('alert', 'Fund transfer request not created!');
        }
    }
    public function fundTransferBankToBank(Request $request)
    {
        $entry_date = date("Y-m-d", strtotime(convertDate($request->date)));
        $bank_id = $request->from_bankk;
        $account_no = $request->from_Bank_account_no;
        $company_id = $request->company_id;
        $balance = BankBalance::where('bank_id', $bank_id)->whereCompanyId($company_id)->where('account_id', $account_no)->whereDate('entry_date', '<=', $entry_date)->sum('totalAmount');
        if ($balance < $request->bank_transfer_amount) {
            return back()->with('alert', 'Unsufficant Balance!');
        }
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'from_bankk' => 'required',
                'from_Bank_account_no' => 'required',
                'bank_transfer_amount' => 'required',
                'to_bank' => 'required',
                'to_Bank_account_no' => 'required',
                'bank_receive_amount' => 'required',
                'remark' => 'required',
            ]);
            if ($validator->fails()) {
                return back()->with('alert', 'Please fill the correct values!');
            }
            $bank_id = $request->from_bankk;
            $company_id = $request->company_id;
            $account_id = $request->from_Bank_account_no;
            $accountNo = SamraddhBankAccount::select('account_no', 'id')->whereId($account_id)->get()->toArray();
            if ($balance) {
                if (($request->bank_transfer_amount + $request->rtgs_neft_charge) > $balance) {
                    return back()->with('alert', 'Sufficient amount not available in bank account!');
                }
            } else {
                return back()->with('alert', 'Sufficient amount not available in bank account!');
            }
            $data = array();
            $t = date("H:i:s");
            $data['transfer_type'] = 1;
            $data['transfer_date_time'] = date("Y-m-d " . $t . "", strtotime(convertDate($request->date)));
            $data['branch_id'] = 29;
            $data['branch_code'] = 1001;
            $data['from_bank_id'] = $request->from_bankk;
            $data['from_Bank_account_number'] = $accountNo[0]['account_no'];
            $data['btb_tranfer_mode'] = $request->transfer_mode;
            if ($request->transfer_mode == 0) {
                $data['from_cheque_utr_no'] = $request->from_cheque_number;
            } elseif ($request->transfer_mode == 1) {
                $data['from_cheque_utr_no'] = $request->from_utr_number;
            }
            $data['to_bank_id'] = $request->to_bank;
            $accountNo = SamraddhBankAccount::select('account_no', 'id')->whereId($request->to_Bank_account_no)->get()->toArray();
            $data['to_Bank_account_number'] = $accountNo[0]['account_no'];
            $data['to_cheque_utr_no'] = $request->from_cheque_number;
            $data['rtgs_neft_charge'] = $request->rtgs_neft_charge;
            $data['transfer_amount'] = $request->bank_transfer_amount;
            $data['receive_amount'] = $request->bank_receive_amount;
            $data['company_id'] = $request->company_id;
            $data['micro_day_book_amount'] = $balance;
            $data['remark'] = $request->remark;
            $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($request->date)));
            $response = FundTransfer::create($data);
            $fund_transfer_id = DB::getPdo()->lastInsertId();
            // $response = FundTransfer::create($data);
            $encodeDate = json_encode($data);
            \App\Models\FundTransferLog::create([
                'funds_transfer_id' => $response->id,
                'type' => $response->transfer_type, // 0 : branch to ho , 1 : bank to bank
                'old_value' => $encodeDate,
                'new_value' => $encodeDate,
                'amount' => $response->amount,
                'title' => 'Create Bank to Bank Payment Request',
                'remark' => '',
                'created_by' => auth()->user()->role_id == 3 ? '2' : '1',
                'created_by_id' => auth()->user()->id,
                'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($request->date))),
            ]);
            $chequeDetail = SamraddhCheque::where('cheque_no', $request->from_cheque_number)->first('id');
            if ($request->transfer_mode == 0) {
                SamraddhCheque::where('cheque_no', $request->from_cheque_number)->update(['status' => 2, 'is_use' => 1]);
                SamraddhChequeIssue::create([
                    'cheque_id' => $chequeDetail->id,
                    'type' => 1,
                    'sub_type' => 11,
                    'type_id' => $response->id,
                    'cheque_issue_date' => $request->created_at,
                    'status' => 1,
                ]);
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        if ($response) {
            return redirect()->route('admin.fund.transfer')->with('success', 'Fund transfer request created successfully!');
        } else {
            return back()->with('alert', 'Fund transfer request not created!');
        }
    }
    public function bankTobankListing(Request $request)
    {
        if ($request->ajax()) {
            $formData = array();
            $getBranchId = getUserBranchId(Auth::id());
            $branch_id = $getBranchId->id;
            $data = FundTransfer::where('branch_id', $branch_id)->where('transfer_type', 1)
                ->whereStatus(0)
                ->whereIsDeleted('0')
                ->orderby('id', 'DESC');
            return Datatables::of($data)
                ->addColumn('transfer_type', function ($row) {
                    if ($row->transfer_type == 0) {
                        return "Branch to Head Office";
                    } elseif ($row->transfer_type == 1) {
                        return "Bank To Bank";
                    } else {
                        return "None";
                    }
                })
                ->rawColumns(['transfer_type'])
                ->addColumn('transfer_date_time', function ($row) {
                    return date("d/m/Y", strtotime(convertDate($row->transfer_date_time)));
                })
                ->rawColumns(['transfer_date_time'])
                ->addColumn('transfer_mode', function ($row) {
                    if ($row->transfer_mode === 0) {
                        return "Cash";
                    } elseif ($row->transfer_mode === 1) {
                        return "Cash";
                    } elseif (empty ($row->transfer_mode)) {
                        return 'Cheque';
                    } else {
                        return "None";
                    }
                })
                ->rawColumns(['transfer_mode'])
                ->addColumn('status', function ($row) {
                    if ($row->status == 0) {
                        return $status = 'Pending';
                    } elseif ($row->status == 1) {
                        return $status = "Approved";
                    } elseif ($row->status == 2) {
                        return $status = "Admin Updated";
                    } else {
                        $status = "";
                    }
                })
                ->rawColumns(['status'])
                ->make(true);
        }
    }
    public function fundTransferReport(Request $request)
    {
        if (check_my_permission(Auth::user()->id, "34") != "1") {
            return redirect()
                ->route('admin.dashboard');
        }
        $data['title'] = "Fund Transfer Report";
        $data['branches'] = Branch::select('id', 'name', 'branch_code')->get();
        return view('templates.admin.payment-management.Report.index', $data);
    }
    // Call Listing to HO Page
    public function BranchToHo(Request $request)
    {
        if (check_my_permission(Auth::user()->id, "31") != "1") {
            return redirect()
                ->route('admin.dashboard');
        }
        $getBranchId = getUserBranchId(Auth::user()->id);
        $data['branch_id'] = $getBranchId->id;
        $data['title'] = 'Branch to Bank Deposit Transfer';
        $data['branches'] = Branch::select('id', 'name', 'branch_code')
            ->get();
        $data['cheques'] = SamraddhCheque::select('cheque_no')
            ->whereStatus(1)
            ->get();
        $data['banks'] = array();
        $data['BranchToHoTransfer'] = check_my_permission(Auth::user()->id, "134");
        return view('templates.admin.payment-management.fund-transfer.branchToHoListing', $data);
    }
    public function createBranchToHo(Request $request)
    {
        if (check_my_permission(Auth::user()->id, "134") != "1") {
            return redirect()
                ->route('admin.dashboard');
        }
        $getBranchId = getUserBranchId(Auth::user()->id);
        $data['branch_id'] = $getBranchId->id;
        $data['title'] = 'Branch to Bank Deposit Transfer';
        if (Auth::user()->branch_id > 0) {
            $id = Auth::user()->branch_id;
            $data['branches'] = Branch::select('id', 'name', 'branch_code')->whereId($id)->get();
        } else {
            $data['branches'] = Branch::select('id', 'name', 'branch_code')->get();
        }
        $data['cheques'] = SamraddhCheque::select('cheque_no')->whereStatus(1)
            ->get();
        $data['banks'] = SamraddhBank::with('bankAccount')->whereStatus(1)
            ->get();
        return view('templates.admin.payment-management.fund-transfer.createBranchToHo', $data);
    }
    public function branchToHoListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchBranchToHo'])) {
                foreach ($_POST['searchBranchToHo'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = FundTransfer::has('company')->select('id', 'transfer_date_time', 'branch_id', 'branch_code', 'transfer_mode', 'amount', 'head_office_bank_id', 'head_office_bank_account_number', 'status', 'bank_slip_id', 'transfer_type', 'company_id', 'is_deleted')
                ->with([
                    'BranchNameByBrachAutoCustom:id,name,branch_code',
                    'getSamraddhBankCustom:id,bank_name',
                    'company:id,name'
                ])
                ->where('transfer_type', 0)
                // ->whereStatus(0)
                ->whereIsDeleted('0')
                ->orderby('id', 'DESC');
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['company_id'] > 0) {
                    $companyId = $arrFormData['company_id'];
                    $data = $data->where('company_id', $companyId);
                }

                if ($arrFormData['branch_id'] != '' && $arrFormData['branch_id'] > 0) {
                    $branchId = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', $branchId);
                }
                if ($arrFormData['bank'] != '') {
                    $head_office_bank_id = $arrFormData['bank'];
                    $data = $data->where('head_office_bank_id', $head_office_bank_id);
                }
                if ($arrFormData['bank_ac'] < 0) {
                    $head_office_bank_account_number1 = $arrFormData['bank_ac'];
                    $data = $data->where('head_office_bank_account_number', $head_office_bank_account_number1);
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', $status);
                }
                if (isset($arrFormData['start_date']) && $arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
            }
            // $data = $data->orderby('id', 'DESC')->get();

            return Datatables::of($data)
                ->rawColumns(['company'])
                ->addColumn('company', function ($row) {
                    return $row->company->name;
                })
                ->rawColumns(['company'])
                ->addColumn('date', function ($row) {
                    return date('d/m/Y', strtotime($row->transfer_date_time));
                })
                ->addColumn('branch', function ($row) {
                    if ($row->branch_id) {
                        $branch = $row['BranchNameByBrachAutoCustom']->name;
                        return "$branch  ( $row->branch_code ) ";
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['branch'])
                // ->addColumn('company', function ($row) {
                //     $companyName = Companies::withoutGlobalScopes()->whereId($row->company_id)->get('name');
                //     return $companyName[0]->name;
                // })
                ->addColumn('created_at', function ($row) {
                    return date("d/m/Y", strtotime(convertDate($row->transfer_date_time)));
                })
                ->rawColumns(['created_at'])
                ->addColumn('transfer_mode', function ($row) {
                    if ($row->transfer_mode == 0) {
                        return 'Cash';
                    } else {
                        return 'Cash';
                    }
                })
                ->rawColumns(['transfer_mode'])
                ->addColumn('transfer_amount', function ($row) {
                    return $row->amount . ' &#8377';
                })
                ->escapeColumns(['transfer_mode'])
                ->addColumn('bank', function ($row) {
                    if (isset ($row->head_office_bank_id) && $row->head_office_bank_id != '') {
                        $bankDetails = $row['getSamraddhBankCustom'];
                        return $bankDetails->bank_name;
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['bank'])
                ->addColumn('bank_account_number', function ($row) {
                    return $row->head_office_bank_account_number;
                })
                ->rawColumns(['bank_account_number'])
                ->addColumn('bank_slip', function ($row) {
                    if (isset ($row->bank_slip_id)) {
                        return fileI($row->bank_slip_id);
                    } else {
                        return 'N/A';
                    }
                })
                ->escapeColumns(['bank_account_number'])
                ->addColumn('status', function ($row) {
                    if ($row->status == 0) {
                        $status = 'Pending';
                    } elseif ($row->status == 1) {
                        $status = 'Approved';
                    } elseif ($row->status == 2) {
                        $status = 'Admin Updated';
                    } else {
                        $status = '';
                    }
                    return $status . '';
                })
                ->rawColumns(['status'])
                ->addColumn('action', function ($row) {
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    if ($row->status != 1) {
                        $url1 = URL::to("admin/fund-transfer/update-status/{$row->id}/1/{$row->branch_id}/{$row->company_id}");
                        $url2 = URL::to("admin/edit/branch_to_ho/" . base64_encode($row->id) . "");
                        $url3 = URL::to("admin/delete/branch-to-ho/{$row->id}");
                        $btn .= '<a class="dropdown-item" href="' . $url2 . '" title="Member Edit"><i class="icon-pencil5  mr-2"></i>Edit</a>  ';
                        $btn .= '<a class="dropdown-item approve-requestnew"  onclick="statusUpdate(' . $row->id . ', 1, ' . $row->branch_id . ', ' . $row->company_id . ')" title="Loans"><i class="fa fa-check" aria-hidden="true"></i>Approve</a>';
                        if (check_my_permission(Auth::user()->id, "354") == "1") {
                            $btn .= '<a class="dropdown-item delete-transfer" data-title="request" data-id="' . $row->id . '" href="javascript:void(0)"  title="Fund Transfer bank to ho delete"><i class="fa fa-trash-o" aria-hidden="true"></i>Delete </a> ';
                        }
                    } else {
                        $url3 = URL::to("admin/delete/approved/branch-to-ho/{$row->id}");
                        if (check_my_permission(Auth::user()->id, "356") == "1") {
                            $btn .= '<a class="dropdown-item delete-transfer" data-title="payment" data-id="' . $row->id . '" href="javascript:void(0)" title="Fund Transfer bank to ho Un-approve"><i class="fa fa-trash-o" aria-hidden="true"></i>Un-approve </a> ';
                        }
                    }
                    $logData = \App\Models\FundTransferLog::whereFundsTransferId($row->id)->exists();
                    if ($logData) {
                        $logUrl = url("admin/fund-transfer/logs/" . base64_encode($row->id));
                        $btn .= '<a class="dropdown-item" target="_blank"  href="' . $logUrl . '" title="Fund Transfer Logs"><i class="fa fa-list-alt" aria-hidden="true"></i>Logs </a> ';
                    }
                    $btn .= '</div></div></div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    public function fundTransferReportListing(Request $request)
    {
        if ($request->ajax() && check_my_permission(Auth::user()->id, "34") == "1") {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = FundTransfer::has('company')
                    ->with([
                        'BranchNameByBrachAutoCustom:id,name,branch_code',
                        'getSamraddhBankCustom:id,bank_name',
                        'samraddhBankCustom:id,bank_name',
                        'getSamraddhBankCustomZeroMode',
                        'file:id,file_name',
                    ])
                    // ->whereIsDeleted(0)
                ;
                if (Auth::user()->branch_id > 0) {
                    $id = Auth::user()->branch_id;
                    $data = $data->where('branch_id', '=', $id);
                }
                if (isset($arrFormData['branch_name']) && $arrFormData['branch_name'] != '') {
                    $id = $arrFormData['branch_name'];
                    if ($id != '0') {
                        $data = $data->whereBranchId($id);
                    }
                }
                if ($arrFormData['branch_code'] != '') {
                    $branch_code = $arrFormData['branch_code'];
                    $data = $data->where('branch_code', '=', $branch_code);
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', '=', $status);
                }
                if ($arrFormData['transfer_type'] != '') {
                    $transfer_type = $arrFormData['transfer_type'];
                    $data = $data->where('transfer_type', '=', $transfer_type);
                }
                if ($arrFormData['company_id'] != '') {
                    $company_id = $arrFormData['company_id'];
                    if ($company_id != '0') {
                        $data = $data->where('company_id', '=', $company_id);
                    }
                }
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
                $count = $data->count("id");
                $exportData = $data->orderby('id', 'DESC')
                    ->get()
                    ->toArray();
                $token = session()->get('_token');
                Cache::put('fund_transfer_report_admin_' . $token, $exportData);
                $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                $totalCount = $count;
                Cache::put('fund_transfer_report_admin_count_' . $token, $totalCount);
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    if ($row->transfer_type == 0) {
                        $val['request_type'] = 'Branch to Bank Deposit';
                    } else {
                        $val['request_type'] = 'Bank To Bank';
                    }
                    if (isset($row->company_id)) {
                        $companyName = Companies::withoutGlobalScopes()->whereId($row->company_id)->first('name');
                        $val['company'] = $companyName ? $companyName->name : 'N/A';
                    } else {
                        $val['company'] = 'N/A';
                    }
                    if (isset($row->branch_id)) {
                        if ($row['BranchNameByBrachAutoCustom']->name) {
                            $val['branch_name'] = $row['BranchNameByBrachAutoCustom']->name;
                        } else {
                            $val['branch_name'] = 'N/A';
                        }
                    } else {
                        $val['branch_name'] = 'N/A';
                    }
                    if ($row->branch_code) {
                        $val['branch_code'] = $row->branch_code;
                    } else {
                        $val['branch_code'] = 'N/A';
                    }
                    $val['loan_day_book_amount'] = $row->loan_day_book_amount . ' &#8377';
                    $val['micro_day_book_amount'] = $row->micro_day_book_amount . ' &#8377';
                    if ($row->transfer_type == 0) {
                        $val['transfer_amount'] = $row->amount . ' &#8377';
                    } else {
                        $val['transfer_amount'] = $row->transfer_amount . ' &#8377';
                    }
                    $val['transfer_date_time'] = date("d/m/Y", strtotime(convertDate($row->transfer_date_time)));
                    if (isset($row['samraddhBankCustom']) && $row->from_bank_id != '') {
                        $val['transaction_no'] = $row['samraddhBankCustom']->bank_name;
                    } else {
                        $val['transaction_no'] = 'N/A';
                    }
                    if ($row->transfer_type == 0) {
                        $bank = $row['getSamraddhBankCustom']; //SamraddhBank::whereId($row->head_office_bank_id)->first('bank_name');
                        if ($bank) {
                            $val['from_bank_name'] = $bank->bank_name;
                        } else {
                            $val['from_bank_name'] = 'N/A';
                        }
                    } elseif ($row->transfer_type == 1) {
                        $bank = $row['getSamraddhBankCustomZeroMode']; //SamraddhBank::whereId($row->to_bank_id)->first('bank_name');
                        if ($bank) {
                            $val['from_bank_name'] = $bank->bank_name;
                        } else {
                            $val['from_bank_name'] = 'N/A';
                        }
                    } else {
                        $val['from_bank_name'] = 'N/A';
                    }
                    if ($row->from_bank_account_number) {
                        $val['from_bank_account_number'] = $row->from_bank_account_number;
                    } else {
                        $val['from_bank_account_number'] = 'N/A';
                    }
                    if ($row->transfer_type == 0) {
                        if ($row->transfer_mode == 0) {
                            $val['transfer_mode'] = 'Cash';
                        } else {
                            $val['transfer_mode'] = 'Cash';
                        }
                    } else {
                        if ($row->btb_tranfer_mode == 0) {
                            $val['transfer_mode'] = 'Cheque';
                        } else {
                            $val['transfer_mode'] = 'Online Transfer';
                        }
                    }
                    if ($row->from_cheque_utr_no) {
                        $val['cheque_no'] = $row->from_cheque_utr_no;
                    } else {
                        $val['cheque_no'] = 'N/A';
                    }
                    if ($row->rtgs_neft_charge) {
                        $val['rtgs_neft_charge'] = $row->rtgs_neft_charge;
                    } else {
                        $val['rtgs_neft_charge'] = 'N/A';
                    }
                    if ($row->transfer_type == 1) {
                        if ($row->to_bank_account_number) {
                            $val['receive_bank_acc'] = $row->to_bank_account_number;
                        } else {
                            $val['receive_bank_acc'] = 'N/A';
                        }
                    } else {
                        if ($row->head_office_bank_account_number) {
                            $val['receive_bank_acc'] = $row->head_office_bank_account_number;
                        } else {
                            $val['receive_bank_acc'] = 'N/A';
                        }
                    }
                    if ($row->to_cheque_utr_no) {
                        $val['receive_cheque_no'] = $row->to_cheque_utr_no;
                    } else {
                        $val['receive_cheque_no'] = 'N/A';
                    }
                    if ($row->transfer_type == 0) {
                        $val['receive_amount'] = $row->amount;
                    } else {
                        $val['receive_amount'] = $row->receive_amount;
                    }
                    $val['request_date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
                    if (isset($row->bank_slip_id)) {
                        $file = fileI($row->bank_slip_id);
                    } else {
                        $file = 'N/A';
                    }
                    $val['bank_slip'] = $file;
                    if ($row->remark) {
                        $val['remark'] = $row->remark;
                    } else {
                        $val['remark'] = 'N/A';
                    }
                    if ($row->status == 0) {
                        $status = 'Pending';
                    } elseif ($row->status == 1) {
                        $status = 'Approved';
                    } elseif ($row->status == 3) {
                        $status = 'Deleted';
                    } else {
                        $status = '';
                    }
                    $val['status'] = $status;
                    $logData = \App\Models\FundTransferLog::whereFundsTransferId($row->id)->exists();
                    $btn = '';
                    if ($logData) {
                        $logUrl = url("admin/fund-transfer/logs/" . base64_encode($row->id));
                        $btn .= '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                        $btn .= '<a class="dropdown-item" target="_blank" href="' . $logUrl . '" title="Fund Transfer Logs"><i class="fa fa-list-alt" aria-hidden="true"></i>Logs </a> ';
                        $btn .= '</div></div></div>';
                    }
                    $val['action'] = $btn;
                    $rowReturn[] = $val;
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
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
    // Edit Branch to ho
    public function edit_branch_to_ho($i)
    {
        $id = base64_decode($i);
        $data['title'] = 'Branch to Bank Deposit | Edit';
        $getBranchId = getUserBranchId(Auth::user()->id);
        $data['branch_id'] = $getBranchId->id;
        $data['title'] = 'Branch to Bank Deposit Transfer | Edit';
        $data['branches'] = Branch::select('id', 'name', 'branch_code')->get();
        $data['cheques'] = SamraddhCheque::select('cheque_no')->whereStatus(1)->get();
        //$data['banks'] = AccountHeads::select('id','title','account_number')->where('account_type',2)->get();
        $data['detail'] = FundTransfer::whereId($id)->first();
        $data['banks'] = SamraddhBank::with('bankAccount')->whereStatus(1)->where('company_id', $data['detail']['company_id'])->get();
        $detailDate = $data['detail']->created_at;
        $detailDate = date("Y-m-d", strtotime(convertDate($detailDate)));
        // $microLoanRes = BranchCash::select('balance', 'loan_balance')->where('branch_id', $data['detail']->branch_id) ->where('entry_date', $detailDate)->first();
        $microPendingAmountRes = FundTransfer::where('id', '!=', $data['detail']->id)
            ->where('transfer_type', 0)
            ->where('transfer_mode', 1)
            ->whereStatus(0)
            ->get();
        $loanPendingAmountRes = FundTransfer::where('id', '!=', $data['detail']->id)
            ->where('transfer_type', 0)
            ->where('transfer_mode', 0)
            ->whereStatus(0)
            ->get();
        $microPendingAmount = 0;
        $data['cashInHandAmount'] = getbranchbankbalanceamounthelper($data['detail']['branch_id'], $data['detail']['company_id']);
        $loanPendingAmount = 0;
        foreach ($loanPendingAmountRes as $key => $value) {
            $loanPendingAmount = $loanPendingAmount + $value->amount;
        }
        return view('templates.admin.payment-management.fund-transfer.edit-branch-to-ho', $data);
    }
    public function update_bank_to_bank(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_bank' => 'required',
            'from_Bank_account_no' => 'required',
            'bank_transfer_amount' => 'required',
            'to_bank' => 'required',
            'to_Bank_account_no' => 'required',
            'bank_receive_amount' => 'required',
            'remark' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->with('alert', 'Please fill the correct values!');
        }
        $data = array();
        $t = date("H:i:s");
        $data['transfer_type'] = 1;
        $data['from_bank_id'] = $request->from_bank;
        $accountNo = SamraddhBankAccount::select('account_no', 'id')->whereId($request->from_Bank_account_no)->get()->toArray();
        $data['from_Bank_account_number'] = $accountNo[0]['account_no'];
        $data['btb_tranfer_mode'] = $request->transfer_mode;
        if ($request->transfer_mode == 0) {
            $data['from_cheque_utr_no'] = $request->from_cheque_number;
        } elseif ($request->transfer_mode == 1) {
            $data['from_cheque_utr_no'] = $request->from_utr_number;
        }
        $data['to_bank_id'] = $request->to_bank;
        $accountNo = SamraddhBankAccount::select('account_no', 'id')->whereId($request->to_Bank_account_no)->get()->toArray();
        $data['to_Bank_account_number'] = $accountNo[0]['account_no'];
        $data['to_cheque_utr_no'] = $request->to_cheque_number;
        $data['rtgs_neft_charge'] = $request->rtgs_neft_charge;
        $data['transfer_amount'] = $request->bank_transfer_amount;
        $data['receive_amount'] = $request->bank_receive_amount;
        $data['remark'] = $request->remark;
        $data['company_id'] = $request->company_id;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($request->date)));
        $encodeDateo = FundTransfer::whereId($request->id)->first()->toArray();
        $response = FundTransfer::whereId($request->id)->update($data);
        $encodeDaten = FundTransfer::whereId($request->id)->first()->toArray();
        $jsonData = jsonArrayConverion($encodeDateo, $encodeDaten);
        $d = [
            'funds_transfer_id' => $request->id,
            'type' => $encodeDateo['transfer_type'], // 0 : branch to ho , 1 : bank to bank
            'old_value' => $jsonData['o'],
            'new_value' => $jsonData['n'],
            'amount' => $encodeDaten['amount'],
            'title' => 'Edit Bank to Bank Payment Request',
            'remark' => '',
            'created_by' => auth()->user()->role_id == 3 ? '2' : '1',
            'created_by_id' => auth()->user()->id,
            'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($request->created_at))),
        ];
        \App\Models\FundTransferLog::create($d);
        if ($response) {
            return redirect()->route('admin.fund.transfer')
                ->with('success', 'Bank To Bank Fund transfer request updated successfully!');
        } else {
            return back()
                ->with('alert', 'Fund transfer request not updated!');
        }
    }
    public function update_branch_to_ho(Request $request)
    {
        $entry_date = date("Y-m-d", strtotime(convertDate($request->date)));
        $balance = \App\Models\BranchCurrentBalance::where('branch_id', $request->branch_id)->whereCompanyId($request->company_id)->whereDate('entry_date', '<=', $entry_date)->sum('totalAmount');
        if ($balance < $request->transfer_amount) {
            return back()->with('alert', 'Not sufficiant balance!');
        }
        $bankSlipId = null;
        $t = date("H:i:s");
        if ($request->hasFile('bank_slip') && $request->hidden_bank_slip == '') {
            $validator = Validator::make($request->all(), [
                'bank_slip' => 'mimes:jpeg,jpg,png,gif|required',
            ]);
            if ($validator->fails()) {
                return back()->with('alert', 'Please upload only Jpeg,jpg,png file!');
            }
            $bankSlipImage = $request->file('bank_slip');
            $bankSlipName = time() . '.' . $bankSlipImage->getClientOriginalExtension();
            $bankSlipLocation = 'fund_transfer';
            ImageUpload::upload($bankSlipImage, $bankSlipLocation, $bankSlipName);
            $bankSlip = Files::create(['file_name' => $bankSlipName, 'file_path' => $bankSlipLocation, 'file_extension' => $bankSlipImage->getClientOriginalExtension()]);
            $bankSlipId = $bankSlip->id;
        } elseif ($request->hasFile('bank_slip') == '' && $request->hidden_bank_slip) {
            $bankSlipId = $request->hidden_bank_slip;
        } elseif ($request->hasFile('bank_slip') && $request->hidden_bank_slip != '') {
            $validator = Validator::make($request->all(), [
                'bank_slip' => 'mimes:jpeg,jpg,png,gif|required',
            ]);
            if ($validator->fails()) {
                return back()->with('alert', 'Please upload only Jpeg,jpg,png file!');
            }
            $bankSlipImage = $request->file('bank_slip');
            $bankSlipName = time() . '.' . $bankSlipImage->getClientOriginalExtension();
            $bankSlipLocation = 'fund_transfer';
            ImageUpload::upload($bankSlipImage, $bankSlipLocation, $bankSlipName);
            $file = [
                'file_name' => $bankSlipName,
                'file_path' => $bankSlipLocation,
                'file_extension' => $bankSlipImage->getClientOriginalExtension(),
            ];
            $fileRes = Files::create($file);
            $bankSlipId = $fileRes->id;
        }
        $data = array();
        $data['transfer_type'] = 0;
        $data['branch_id'] = $request->branch_id;
        $data['branch_code'] = $request->branch_code;
        $data['transfer_date_time'] = date("Y-m-d " . $t . "", strtotime(convertDate($request->date)));
        $data['transfer_mode'] = $request->transfer_mode;
        $data['amount'] = $request->transfer_amount;
        $data['loan_day_book_amount'] = $request->loan_daybook_amount;
        $data['micro_day_book_amount'] = $balance;
        $data['head_office_bank_id'] = $request->bank;
        $data['company_id'] = $request->company_id;
        //$data['head_office_bank_account_number'] = AccountHeads::find($request->bank)->account_number;
        $data['head_office_bank_account_number'] = $request->from_Bank_account_no;
        $data['bank_slip_id'] = $bankSlipId ?? '';
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($request->date)));
        $encodeDateo = FundTransfer::where('id', $request->id)->first()->toArray();
        $encodeDateo['bank_slip_id'] = fileI(FundTransfer::where('id', $request->id)->value('bank_slip_id'));
        $response = FundTransfer::where('id', $request->id)->update($data);
        $data['bank_slip_id'] = fileI($bankSlipId);
        $encodeDaten = $data;
        $jsonData = jsonArrayConverion($encodeDateo, $encodeDaten);
        $d = [
            'funds_transfer_id' => $request->id,
            'type' => $encodeDateo['transfer_type'], // 0 : branch to ho , 1 : bank to bank
            'old_value' => $jsonData['o'],
            'new_value' => $jsonData['n'],
            'amount' => $encodeDaten['amount'],
            'title' => 'Edit Bank to Head Office Payment Request',
            'remark' => '',
            'created_by' => auth()->user()->role_id == 3 ? '2' : '1',
            'created_by_id' => auth()->user()->id,
            'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($request->date))),
        ];
        \App\Models\FundTransferLog::create($d);
        if ($response) {
            return redirect()->route('admin.fund-transfer.branchToHo')->with('success', 'Request Updated successfully!');
        } else {
            return back()->with('alert', 'Record not updated!');
        }
    }
    // dELETE bRANCH tO hO
    public function deleteBranchToHo($id)
    {
        $type = FundTransfer::whereId($id)->first(['transfer_type', 'from_cheque_utr_no']);
        //$chequeDetail = SamraddhCheque::where('cheque_no',$type->from_cheque_number)->first('id');
        if ($type->transfer_type === 1) {
            SamraddhCheque::where('cheque_no', $type->from_cheque_utr_no)
                ->update(['status' => 1]);
            FundTransfer::whereId($id)->delete();
        } else {
            FundTransfer::whereId($id)->delete();
        }
        return back()
            ->with('success', 'Record deleted Successfully!');
    }
    public function getDayBookAmount(Request $request)
    {
        $branchId = $request->branchId;
        $editId = $request->editId;
        $date = date("Y-m-d", strtotime(convertDate($request->date)));
        $microLoanRes = BranchCash::select('balance', 'loan_balance')->where('branch_id', $branchId)->whereDate('entry_date', $date)->orderBy('entry_date', 'desc')
            ->first();
        if ($editId > 0) {
            $microPendingAmountRes = FundTransfer::where('id', '!=', $editId)->where('branch_id', $branchId)->where('transfer_type', 0)
                ->where('transfer_mode', 1)
                ->whereStatus(0)
                ->get();
            $loanPendingAmountRes = FundTransfer::where('id', '!=', $editId)->where('branch_id', $branchId)->where('transfer_type', 0)
                ->where('transfer_mode', 0)
                ->whereStatus(0)
                ->get();
        } else {
            $microPendingAmountRes = FundTransfer::where('transfer_type', 0)->where('branch_id', $branchId)->where('transfer_mode', 1)
                ->whereStatus(0)
                ->get();
            $loanPendingAmountRes = FundTransfer::where('transfer_type', 0)->where('branch_id', $branchId)->where('transfer_mode', 0)
                ->whereStatus(0)
                ->get();
        }
        $microPendingAmount = 0;
        $loanPendingAmount = 0;
        foreach ($loanPendingAmountRes as $key => $value) {
            $loanPendingAmount = $loanPendingAmount + $value->amount;
        }
        if ($microLoanRes) {
            $mBalance = $microLoanRes->balance - $microPendingAmount;
            $lBalance = $microLoanRes->loan_balance - $loanPendingAmount;
            $microDayBookAmount = (int) $mBalance;
            $loanDayBookAmount = (int) $lBalance;
        } else {
            $microLoan = BranchCash::select('balance', 'loan_balance')->where('branch_id', $branchId)->whereDate('entry_date', '<', $date)->orderby('entry_date', 'DESC')
                ->first();
            if ($microLoan) {
                $mBalance = $microLoan->balance - $microPendingAmount;
                $lBalance = $microLoan->loan_balance - $loanPendingAmount;
            } else {
                $mBalance = 0;
                $lBalance = 0;
            }
            $microDayBookAmount = (int) $mBalance;
            $loanDayBookAmount = (int) $lBalance;
        }
        $return_array = compact('loanDayBookAmount', 'microDayBookAmount');
        return json_encode($return_array);
    }
    public function getBankDayBookAmount(Request $request)
    {
        $fromBankId = $request->fromBankId;
        $date = date("Y-m-d", strtotime(convertDate($request->date)));
        $bankRes = SamraddhBankClosing::where('bank_id', $fromBankId)->whereDate('entry_date', $date)->orderBy('entry_date', 'desc')->first();
        if ($bankRes) {
            $bankDayBookAmount = $bankRes->balance;
            $bankDayBookLoanAmount = $bankRes->loan_balance;
        } else {
            $bankRes = SamraddhBankClosing::where('bank_id', $fromBankId)->whereDate('entry_date', '<', $date)->orderby('entry_date', 'DESC')->first();
            $bankDayBookAmount = $bankRes->balance;
            $bankDayBookAmount = $bankRes->balance;
            $bankDayBookLoanAmount = $bankRes->loan_balance;
        }
        $return_array = compact('bankDayBookAmount', 'bankDayBookLoanAmount');
        return json_encode($return_array);
    }
    public function getToBankRecord(Request $request)
    {
        $toBankId = $request->toBankId;
        $toBankAccountId = (getSamraddhBankAccount($request->accountNumber)) ? getSamraddhBankAccount($request->accountNumber)->id : '';
        $date = date("Y-m-d", strtotime(convertDate($request->date)));
        $bankRes = SamraddhBankClosing::where('bank_id', $toBankId)->where('account_id', $toBankAccountId)->whereDate('entry_date', $date)->orderBy('entry_date', 'desc')->count();
        if ($bankRes) {
            $resCount = $bankRes;
        } else {
            $bankRes = SamraddhBankClosing::where('bank_id', $toBankId)->where('account_id', $toBankAccountId)->whereDate('entry_date', '<', $date)->orderby('entry_date', 'DESC')->count();
            $resCount = ($bankRes) ? $bankRes : 0;
        }
        $return_array = compact('resCount');
        return json_encode($return_array);
    }
    public function branch_bank_balance_update()
    {
        $fundTransferRequest = \App\Models\FundTransfer::whereStatus(1)->limit(1)
            ->orderby('created_at', 'ASC')
            ->get();
        foreach ($fundTransferRequest as $key => $ftDetails) {
            Session::put('created_at', $ftDetails->transfer_date_time);
            $dayBookRef = CommanController::createBranchDayBookReference($ftDetails->amount);
            if ($ftDetails->transfer_type == 0) {
                $branchDayBook = CommanController::createBranchDayBook($dayBookRef, $ftDetails->branch_id, 7, 70, $ftDetails->id, $ftDetails->id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $ftDetails->branch_id, $ftDetails->amount, $ftDetails->amount, $ftDetails->amount, 'From Branch ' . getBranchDetail($ftDetails->branch_id)->name . ' ' . $ftDetails->amount . ' A/C Dr To Bank ' . getSamraddhBank($ftDetails->head_office_bank_id)->bank_name . ' A/C Cr', 'Cash A/c Dr ' . $ftDetails->amount . ' From Branch ' . getBranchDetail($ftDetails->branch_id)->name . '', 'Cash A/c Cr ' . $ftDetails->amount . ' To Bank ' . getSamraddhBank($ftDetails->head_office_bank_id)->bank_name . '', 'DR', 0, 'INR', $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $ftDetails->head_office_bank_id, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $ftDetails->branch_id, $ftDetails->head_office_bank_id, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $is_contra = NULL, $contra_id = NULL, $created_at = NULL);
                $allTransaction = CommanController::createAllTransaction($dayBookRef, $ftDetails->branch_id, $ftDetails->head_office_bank_id, getSamraddhBankAccount($ftDetails->head_office_bank_account_number)->id, 2, 10, 28, 71, $head5 = NULL, 7, 70, $ftDetails->id, $ftDetails->id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $ftDetails->branch_id, $ftDetails->amount, $ftDetails->amount, $ftDetails->amount, 'From Branch ' . getBranchDetail($ftDetails->branch_id)->name . ' ' . $ftDetails->amount . ' A/C Dr To Bank ' . getSamraddhBank($ftDetails->head_office_bank_id)->bank_name . ' A/C Cr', 'DR', 0, 'INR', $ftDetails->head_office_bank_id, getSamraddhBank($ftDetails->head_office_bank_id)->bank_name, $ftDetails->branch_id, getBranchDetail($ftDetails->branch_id)->name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $ftDetails->head_office_bank_id, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $ftDetails->branch_id, $ftDetails->head_office_bank_id, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL);
                $allTransaction = CommanController::createAllTransaction($dayBookRef, $ftDetails->branch_id, $ftDetails->head_office_bank_id, getSamraddhBankAccount($ftDetails->head_office_bank_account_number)->id, 2, 10, 27, getSamraddhBankAccount($ftDetails->head_office_bank_account_number)->account_head_id, $head5 = NULL, 7, 70, $ftDetails->id, $ftDetails->id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $ftDetails->branch_id, $ftDetails->amount, $ftDetails->amount, $ftDetails->amount, 'From Branch ' . getBranchDetail($ftDetails->branch_id)->name . ' ' . $ftDetails->amount . ' A/C Dr To Bank ' . getSamraddhBank($ftDetails->head_office_bank_id)->bank_name . ' A/C Cr', 'CR', 0, 'INR', $ftDetails->head_office_bank_id, getSamraddhBank($ftDetails->head_office_bank_id)->bank_name, $ftDetails->branch_id, getBranchDetail($ftDetails->branch_id)->name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $ftDetails->head_office_bank_id, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $ftDetails->branch_id, $ftDetails->head_office_bank_id, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL);
                $samraddhBankDaybook = CommanController::createSamraddhBankDaybook($dayBookRef, $ftDetails->head_office_bank_id, getSamraddhBankAccount($ftDetails->head_office_bank_account_number)->id, 7, 70, $ftDetails->id, $ftDetails->id, $associate_id = NULL, $member_id = NULL, $ftDetails->branch_id, $ftDetails->amount, $ftDetails->amount, $ftDetails->amount, 'From Branch ' . getBranchDetail($ftDetails->branch_id)->name . ' ' . $ftDetails->amount . ' A/C Dr To Bank ' . getSamraddhBank($ftDetails->head_office_bank_id)->bank_name . ' A/C Cr', 'Cash A/c Dr ' . $ftDetails->amount . ' From Branch ' . getBranchDetail($ftDetails->branch_id)->name . '', 'Cash A/c Cr ' . $ftDetails->amount . ' To Bank ' . getSamraddhBank($ftDetails->head_office_bank_id)->bank_name . '', 'CR', 0, 'INR', $ftDetails->head_office_bank_id, getSamraddhBank($ftDetails->head_office_bank_id)->bank_name, $ftDetails->branch_id, getBranchDetail($ftDetails->branch_id)->name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $ftDetails->head_office_bank_id, $ftDetails->head_office_bank_account_number, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL);
                // $samraddhBankDaybook = CommanController::createSamraddhBankDaybook($dayBookRef,$ftDetails->head_office_bank_id,getSamraddhBankAccount($ftDetails->head_office_bank_account_number)->id,7,70,$ftDetails->id,$ftDetails->id,$associate_id=NULL,$member_id=NULL,$ftDetails->branch_id,$ftDetails->amount,$ftDetails->amount,$ftDetails->amount,'From Branch '.getBranchDetail($ftDetails->branch_id)->name.' '.$ftDetails->amount.' A/C Dr To Bank '.getSamraddhBank($ftDetails->head_office_bank_id)->bank_name.' A/C Cr','Cash A/c Dr '.$ftDetails->amount.' From Branch '.getBranchDetail($ftDetails->branch_id)->name.'','Cash A/c Cr '.$ftDetails->amount.' To Bank '.getSamraddhBank($ftDetails->head_office_bank_id)->bank_name.'','CR',0,'INR',$ftDetails->head_office_bank_id,getSamraddhBank($ftDetails->head_office_bank_id)->bank_name,$ftDetails->branch_id,getBranchDetail($ftDetails->branch_id)->name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$ftDetails->head_office_bank_id,$ftDetails->head_office_bank_account_number,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,Auth::user()->id,$created_at=NULL);
                $updateBranchBankBalance = CommanController::updateBackDateBranchBankBalance($dayBookRef, $ftDetails->amount, $ftDetails->branch_id, $ftDetails->transfer_mode, $ftDetails->transfer_type, $ftDetails->head_office_bank_id, $from_bank_id = NULL, $to_bank_id = NULL, $ftDetails->transfer_date_time, 0);
            } elseif ($ftDetails->transfer_type == 1) {
                if ($ftDetails->btb_tranfer_mode == 0) {
                    $cheque_no = getSamraddhChequeData($ftDetails->from_cheque_utr_no)->cheque_no;
                    $cheque_date = getSamraddhChequeData($ftDetails->from_cheque_utr_no)->cheque_create_date;
                    $cheque_bank_from = $ftDetails->from_bank_id;
                    $cheque_bank_ac_from = $ftDetails->from_bank_account_number;
                    $cheque_bank_ifsc_from = getSamraddhBankAccount($ftDetails->from_bank_account_number)->ifsc_code;
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = $ftDetails->to_bank_id;
                    $cheque_bank_ac_to = $ftDetails->to_bank_account_number;
                    $cheque_bank_ac_to = $ftDetails->to_bank_account_number;
                    SamraddhCheque::where('cheque_no', getSamraddhChequeData($ftDetails->from_cheque_utr_no)
                        ->cheque_no)
                        ->update(['status' => 2, 'is_use' => 1]);
                    SamraddhChequeIssue::create([
                        'cheque_id' => $ftDetails->from_cheque_utr_no,
                        'type' => 1,
                        'sub_type' => 11,
                        'type_id' => $ftDetails->id,
                        'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($ftDetails->transfer_date_time))),
                        'status' => 1,
                    ]);
                    $sub_type = 81;
                    $payment_mode = 1;
                    $transferAmount = $ftDetails->transfer_amount;
                } elseif ($ftDetails->btb_tranfer_mode == 1) {
                    $cheque_no = NULL;
                    $cheque_date = NULL;
                    $cheque_bank_from = NULL;
                    $cheque_bank_ac_from = NULL;
                    $cheque_bank_ifsc_from = NULL;
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = NULL;
                    $cheque_bank_ac_to = NULL;
                    $sub_type = 82;
                    $payment_mode = 2;
                    $transferAmount = $ftDetails->transfer_amount + $ftDetails->rtgs_neft_charge;
                }
                $pAmount = $ftDetails->transfer_amount;
                $allTransaction = CommanController::createAllTransaction($dayBookRef, $ftDetails->branch_id, $ftDetails->from_bank_id, getSamraddhBankAccount($ftDetails->from_bank_account_number)->id, 2, 10, 27, getSamraddhBankAccount($ftDetails->from_bank_account_number)->account_head_id, $head5 = NULL, 8, $sub_type, $ftDetails->from_bank_id, $ftDetails->id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $transferAmount, $transferAmount, $transferAmount, 'From Bank ' . getSamraddhBank($ftDetails->from_bank_id)->bank_name . ' A/C Dr ' . $transferAmount . ' To Bank ' . getSamraddhBank($ftDetails->to_bank_id)->bank_name . ' A/C Cr ', 'DR', $payment_mode, 'INR', $ftDetails->to_bank_id, getSamraddhBank($ftDetails->to_bank_id)->bank_name, $ftDetails->from_bank_id, getSamraddhBank($ftDetails->from_bank_id)->bank_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no = NULL, $ftDetails->from_bank_id, getSamraddhBankAccount($ftDetails->from_bank_account_number)->id, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $ftDetails->to_bank_id, getSamraddhBankAccount($ftDetails->to_bank_account_number)->id, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL);
                $allTransaction = CommanController::createAllTransaction($dayBookRef, $ftDetails->branch_id, $ftDetails->from_bank_id, getSamraddhBankAccount($ftDetails->to_bank_account_number)->id, 2, 10, 27, getSamraddhBankAccount($ftDetails->to_bank_account_number)->account_head_id, $head5 = NULL, 8, $sub_type, $ftDetails->from_bank_id, $ftDetails->id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $pAmount, $pAmount, $pAmount, 'From Bank ' . getSamraddhBank($ftDetails->from_bank_id)->bank_name . ' A/C Dr ' . $pAmount . ' To Bank ' . getSamraddhBank($ftDetails->to_bank_id)->bank_name . ' A/C Cr ', 'CR', $payment_mode, 'INR', $ftDetails->to_bank_id, getSamraddhBank($ftDetails->to_bank_id)->bank_name, $ftDetails->from_bank_id, getSamraddhBank($ftDetails->from_bank_id)->bank_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no = NULL, $ftDetails->from_bank_id, getSamraddhBankAccount($ftDetails->to_bank_account_number)->id, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $ftDetails->to_bank_id, getSamraddhBankAccount($ftDetails->to_bank_account_number)->id, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL);
                $samraddhBankDaybook = CommanController::createSamraddhBankDaybook($dayBookRef, $ftDetails->from_bank_id, getSamraddhBankAccount($ftDetails->from_bank_account_number)->id, 8, $sub_type, $ftDetails->from_bank_id, $ftDetails->id, $associate_id = NULL, $member_id = NULL, $branch_id = NULL, $pAmount, $pAmount, $pAmount, 'From Bank ' . getSamraddhBank($ftDetails->from_bank_id)->bank_name . ' A/C Dr ' . $pAmount . ' To Bank ' . getSamraddhBank($ftDetails->to_bank_id)->bank_name . ' A/C Cr', 'Cash A/c Dr ' . $pAmount . ' From Bank ' . getSamraddhBank($ftDetails->from_bank_id)->bank_name . '', 'Cash A/c Cr ' . $pAmount . ' To Bank ' . getSamraddhBank($ftDetails->to_bank_id)->bank_name . '', 'CR', $payment_mode, 'INR', $ftDetails->to_bank_id, getSamraddhBank($ftDetails->to_bank_id)->bank_name, $ftDetails->from_bank_id, getSamraddhBank($ftDetails->from_bank_id)->bank_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no = NULL, $ftDetails->from_bank_id, getSamraddhBankAccount($ftDetails->from_bank_account_number)->id, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $ftDetails->to_bank_id, getSamraddhBankAccount($ftDetails->to_bank_account_number)->id, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL);
                // $samraddhBankDaybook = CommanController::createSamraddhBankDaybook($dayBookRef,$ftDetails->head_office_bank_id,getSamraddhBankAccount($ftDetails->head_office_bank_account_number)->id,7,70,$ftDetails->id,$ftDetails->id,$associate_id=NULL,$member_id=NULL,$ftDetails->branch_id,$ftDetails->amount,$ftDetails->amount,$ftDetails->amount,'From Branch '.getBranchDetail($ftDetails->branch_id)->name.' '.$ftDetails->amount.' A/C Dr To Bank '.getSamraddhBank($ftDetails->head_office_bank_id)->bank_name.' A/C Cr','Cash A/c Dr '.$ftDetails->amount.' From Branch '.getBranchDetail($ftDetails->branch_id)->name.'','Cash A/c Cr '.$ftDetails->amount.' To Bank '.getSamraddhBank($ftDetails->head_office_bank_id)->bank_name.'','CR',0,'INR',$ftDetails->head_office_bank_id,getSamraddhBank($ftDetails->head_office_bank_id)->bank_name,$ftDetails->branch_id,getBranchDetail($ftDetails->branch_id)->name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$ftDetails->head_office_bank_id,$ftDetails->head_office_bank_account_number,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,Auth::user()->id,$created_at=NULL);
                if ($ftDetails->btb_tranfer_mode == 1) {
                    $allTransaction = CommanController::createAllTransaction($dayBookRef, $ftDetails->branch_id, $ftDetails->from_bank_id, getSamraddhBankAccount($ftDetails->to_bank_account_number)->id, 4, 86, 92, $head4 = NULL, $head5 = NULL, 8, 82, $ftDetails->from_bank_id, $ftDetails->id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $ftDetails->rtgs_neft_charge, $ftDetails->rtgs_neft_charge, $ftDetails->rtgs_neft_charge, 'NEFT Charge A/c Cr ' . $ftDetails->rtgs_neft_charge . '', 'DR', 2, 'INR', $ftDetails->to_bank_id, getSamraddhBank($ftDetails->to_bank_id)->bank_name, $ftDetails->from_bank_id, getSamraddhBank($ftDetails->from_bank_id)->bank_name, $v_no = NULL, $v_date = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $ftDetails->from_bank_id, getSamraddhBankAccount($ftDetails->to_bank_account_number)->id, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $ftDetails->to_bank_id, getSamraddhBankAccount($ftDetails->to_bank_account_number)->id, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL);
                    $updateBranchBankBalance = CommanController::updateBackDateBranchBankBalance($dayBookRef, $ftDetails->transfer_amount, $branch_id = NULL, $ftDetails->transfer_mode, $ftDetails->transfer_type, $bank_id = NULL, $ftDetails->from_bank_id, $ftDetails->to_bank_id, $ftDetails->transfer_date_time, $ftDetails->rtgs_neft_charge);
                } else {
                    $updateBranchBankBalance = CommanController::updateBackDateBranchBankBalance($dayBookRef, $ftDetails->transfer_amount, $branch_id = NULL, $ftDetails->transfer_mode, $ftDetails->transfer_type, $bank_id = NULL, $ftDetails->from_bank_id, $ftDetails->to_bank_id, $ftDetails->transfer_date_time, 0);
                }
                $type = FundTransfer::whereId($ftDetails->id)->first(['transfer_type', 'from_cheque_utr_no']);
                if ($type->transfer_type === 1) {
                    // SamraddhCheque::where('cheque_no', $type->from_cheque_utr_no)->update(['status' => 3]);
                    $created_at = date("Y-m-d", strtotime(convertDate($ftDetails->transfer_date_time)));
                    $updatechequeId = $this->chequeLog($dayBookRef, null, $created_at, $type->from_cheque_utr_no, "Fund Transfer Branch to Bank", null, $ftDetails->to_bank_account_number);
                    if ($updatechequeId) {
                        \App\Models\ChequeLog::find($updatechequeId)->update(['day_ref_id' => $dayBookRef]);
                    }
                }
            }
        }
        return back()->with('success', 'Fund Transfer approved successfully!');
    }
    public function fetchbranchbycompanyid(Request $request)
    {
        $data = array();
        $data['branch'] = Branch::with([
            'companies_branch' => function ($query) use ($request) {
                $query->has('company')->with('company:id,name')
                    ->when($request->company_id != '0', function ($q) use ($request) {
                        $q->whereCompanyId($request->company_id);
                    })
                    ->whereStatus('1')
                    ->get(['id', 'company_id', 'branch_id', 'status']);
            }
        ])
            ->whereStatus(1)
            ->get(['id', 'name', 'branch_code']);
        if ($request->bank == 'true') {
            $data['bank'] = SamraddhBank::when($request->company_id != '0', function ($q) use ($request) {
                $q->whereCompanyId($request->company_id);
            })->whereStatus(1)->get(['id', 'bank_name', 'status', 'company_id']);
        }
        return json_encode($data);
    }
    public function fetchbranchbycompanyidinactive(Request $request)
    {
        $data = array();
        $data['branch'] = Branch::with([
            'companies_branch' => function ($query) use ($request) {
                $query->has('company')->with('company:id,name')
                    ->when($request->company_id > 0, function ($q) use ($request) {
                        $q->whereCompanyId($request->company_id);
                    })
                    ->whereStatus('1')
                    ->get(['id', 'company_id', 'branch_id', 'status']);
            }
        ])
            ->whereStatus(1)
            ->get(['id', 'name', 'branch_code']);
        if ($request->bank == 'true') {
            $data['bank'] = SamraddhBank::when($request->company_id > 0, function ($q) use ($request) {
                $q->whereCompanyId($request->company_id);
            })->get(['id', 'bank_name', 'status', 'company_id']);
        }
        return json_encode($data);
    }
    public function getbranchbankbalanceamount(Request $request)
    {
        $globaldate = $request->entrydate;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $branch_id = $request->branch_id;
        $company_id = $request->company_id;
        $getBranchAmount = \App\Models\Branch::whereId($branch_id)->first();
        $Amount = $company_id == 1 ? $getBranchAmount->total_amount : 0;
        $startDate = ($company_id == 1) ? date("Y-m-d", strtotime(convertDate('2021-08-05'))) : '';
        $balance = \App\Models\BranchCurrentBalance::where('branch_id', $branch_id)->when($startDate != '', function ($q) use ($startDate) {
            $q->whereDate('entry_date', '>=', $startDate);
        })->where('entry_date', '<=', $entry_date);
        if ($company_id != '') {
            $balance = $balance->whereCompanyId($company_id);
        }
        $balance = $balance->sum('totalAmount');
        $return_array = ['balance' => number_format(($balance + $Amount), 2, '.', '')];
        return json_encode($return_array);
    }
    public function getBankAccountNo(Request $request)
    {
        $bank_id = $request->bank_id;
        $accountNo = \App\Models\SamraddhBankAccount::select('account_no')->where('bank_id', $bank_id)->whereStatus(1)->first();
        return $accountNo;
    }
    public function getBankAccountNos(Request $request)
    {
        $bank_id = $request->bank_id;
        $accountNo = \App\Models\SamraddhBankAccount::select('account_no', 'id')->where('bank_id', $bank_id)->whereStatus(1)->get();
        return json_encode($accountNo);
    }
    public function banktobankbalance(Request $request)
    {
        $entry_date = date("Y-m-d", strtotime(convertDate($request->date)));
        $bank_id = $request->bank_id;
        $account_no = $request->account_no;
        $company_id = $request->company_id;
        $balance = BankBalance::where('bank_id', $bank_id)->whereCompanyId($company_id)->where('account_id', $account_no)->whereDate('entry_date', '<=', $entry_date)->sum('totalAmount');
        return $balance;
    }
    public function fetchbranchbycompanyidd(Request $request)
    {
        $branchId = CompanyBranch::where('company_id', $request->company_id)->whereStatus(1)->get(['company_id', 'branch_id', 'status']);
        $data['branch'] = [];
        foreach ($branchId as $row) {
            $data['branch'][] = Branch::whereId($row->branch_id)->whereStatus(1)->get(['id', 'name', 'branch_code']);
        }
        return json_encode($data);
    }
    public function getchecqsByBankIdAndAccountno(Request $request)
    {
        $cheques = SamraddhCheque::where('bank_id', $request->bank_id)->where('account_id', $request->account_no)->whereStatus(1)->get(['cheque_no', 'id']);
        return json_encode($cheques);
    }
    public function fetchbranchbycompanyBank(Request $request)
    {
        $data = array();
        $branchId = CompanyBranch::where('company_id', $request->company_id)->get(['company_id', 'branch_id', 'status']);
        if ($request->branch == 'true') {
            $data['branch'] = [];
            foreach ($branchId as $row) {
                $daaaaa = Branch::whereId($row->branch_id)->get(['id', 'name', 'branch_code']);
                if (!($daaaaa->isEmpty())) {
                    $data['branch'][] = $daaaaa;
                }
            }
        }
        if ($request->bank == 'true') {
            $data['bank'] = SamraddhBank::has('company')->with('company:id,name,short_name')->when($request->company_id != '0', function ($q) use ($request) {
                $q->whereCompanyId($request->company_id);
            })
                ->whereStatus(1)
                ->get(['id', 'bank_name', 'status', 'company_id']);
        }
        return json_encode($data);
    }
    public function getBankAccountNumber(Request $request)
    {
        $bank_id = $request->bank_id;
        $account = \App\Models\SamraddhBankAccount::whereBankId($bank_id)->get(['id', 'account_no']);
        $return_array = compact('account');
        return json_encode($return_array);
    }
    public function getBankAccountNumberinactive(Request $request)
    {
        $account = accountListAllBank($request->bank_id);
        if ($request->company_id != '0') {
            $banks = \App\Models\SamraddhBank::where('company_id', $request->company_id)->get();
        }
        $return_array = compact('account', 'banks');
        return json_encode($return_array);
    }
    public function deleteFundTransfer(Request $request)
    {
        $response = $this->delete($request);
        return response($response);
    }
    public function fundtransferlogs($id)
    {
        $i = base64_decode($id);
        $data['log'] = \App\Models\FundTransferLog::whereFundsTransferId($i)->get();
        if (count($data['log']) == 0) {
            return redirect()->back()->with('alert', 'Record Not Found !');
        }
        $data['title'] = "Fund Transfer log for " . ($data['log'][0]['type'] == 1 ? "Bank To Bank" : "Branch To Head Office");
        return view('templates.admin.payment-management.fund-transfer.logs', $data);
    }
    public function fundtransferlogslisting(Request $request)
    {
        $id = $request->id;
        $log = \App\Models\FundTransferLog::whereId($id)->first();
        return $log;
    }
    public function base64($code)
    {
        return base64_encode($code);
    }
    public function exportFundTransfer(Request $request)
    {
        $token = session()->get('_token');
        $results = Cache::get('fund_transfer_report_admin_' . $token);
        $count = Cache::get('fund_transfer_report_admin_count_' . $token);

        $input = $request->all();
        $getBranchId = getUserBranchId(Auth::user()->id);
        $BranchId = $getBranchId->id;
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/fund_transfer_report.csv";
        $fileName = env('APP_EXPORTURL') . "asset/fund_transfer_report.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");

        if ($request['report_export'] == 0) {
            $totalResults = $count;
            $result = 'next';
            if (($start + $limit) >= $totalResults) {
                $result = 'finished';
            }
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
            $sno = $_POST['start'];
            $statusLabels = [
                0 => 'Pending',
                1 => 'Approved',
                2 => 'Admin Updated',
                3 => 'Deleted',
            ];
            $records = array_slice($results, $start, $limit);
            foreach ($records as $row) {
                $sno++;
                // pd($row);
                $val['S/N'] = $sno;
                $val['REQUEST TYPE'] = ($row['transfer_type'] == 0) ? 'Branch to Bank Deposit' : 'Bank To Bank';
                $val['BR NAME'] = isset($row['branch_id']) ? ($row['branch_name_by_brach_auto_custom']['name'] ?? 'N/A') : 'N/A';

                $val['COMPANY NAME'] = isset($row['company_id']) ? (Companies::withoutGlobalScopes()->where('id', $row['company_id'])->value('name') ?? 'N/A') : 'N/A';

                $val['BR CODE'] = $row['branch_code'];
                //$val['LOAN DAY BOOK AMOUNT']=$row['loan_day_book_amount'].' &#8377';
                $val['CASH IN HAND AMOUNT'] = $row['micro_day_book_amount'];

                $val['TRANSFER AMOUNT'] = $row['transfer_type'] == 0 ? $row['amount'] : $row['transfer_amount'];

                $val['TRANSFER DATE'] = date("d/m/Y", strtotime(convertDate($row['transfer_date_time'])));
                $val['FROM BANK'] = ($row['samraddh_bank_custom']) ? $row['samraddh_bank_custom']['bank_name'] : 'N/A';

                $val['FROM BANK A/C NO'] = $row['from_bank_account_number'] ?? 'N/A';

                $receive_bank_name = '';
                if ($row['transfer_type'] == 1) {
                    $bank = $row['get_samraddh_bank_custom_zero_mode'];
                    if ($bank) {
                        $receive_bank_name = $bank['bank_name'];
                    } else {
                        $receive_bank_name = 'N/A';
                    }
                } else {
                    $bank = $row['get_samraddh_bank_custom'];
                    if ($bank) {
                        $receive_bank_name = $bank['bank_name'];
                    } else {
                        $receive_bank_name = 'N/A';
                    }
                }
                $val['TO BANK'] = $receive_bank_name;
                $bank = (($row['transfer_type'] == 1) ? ($row['get_samraddh_bank_custom_zero_mode'] ?? null) : ($row['get_samraddh_bank_custom'] ?? null));
                $val['TO BANK'] = $bank ? $bank['bank_name'] : 'N/A';

                $transfer_mode = '';
                if ($row['transfer_type'] == 0) {
                    if ($row['transfer_mode'] == 0) {
                        $transfer_mode = 'Cash';
                    } else {
                        $transfer_mode = 'Cash';
                    }
                } else {
                    if ($row['btb_tranfer_mode'] == 0) {
                        $transfer_mode = 'Cheque';
                    } else {
                        $transfer_mode = 'Online Transfer';
                    }
                }
                $val['TRANSFER MODE'] = $transfer_mode;
                $val['CHEQUE NO'] = $row['from_cheque_utr_no'] ?? 'N/A';
                $val['RTGS/NEFT CHARGE'] = $row['rtgs_neft_charge'] ?? 'N/A';

                $receive_bank_acc = '';
                if ($row['transfer_type'] == 1) {
                    if ($row['to_bank_account_number']) {
                        $receive_bank_acc = $row['to_bank_account_number'];
                    } else {
                        $receive_bank_acc = 'N/A';
                    }
                } else {
                    if ($row['head_office_bank_account_number']) {
                        $receive_bank_acc = $row['head_office_bank_account_number'];
                    } else {
                        $receive_bank_acc = 'N/A';
                    }
                }
                $val['RECEIVED BANK A/C'] = $receive_bank_acc;
                $val['RECEIVED CHEQUE NO/UTR NO'] = $row['to_cheque_utr_no'] ?? 'N/A';

                $val['RECEIVED AMOUNT'] = $row['transfer_type'] == 0 ? $row['amount'] : $row['receive_amount'];

                $val['REQUEST DATE'] = date("d/m/Y", strtotime(convertDate($row['created_at'])));
                $val['BANK SLIP'] = $row['file'] ? $row['file']['file_name'] : 'N/A';

                $val['REMARK'] = $row['remark'] ?? 'N/A';

                $val['STATUS'] = $statusLabels[$row['status']] ?? '';

                if (!$headerDisplayed) {

                    fputcsv($handle, array_keys($val));
                    $headerDisplayed = true;
                }
                fputcsv($handle, $val);
            }
            // Close the file
            fclose($handle);
            $percentage = ($start + $limit) * 100 / $totalResults;
            $percentage = number_format((float) $percentage, 1, '.', '');
            // Output some stuff for jquery to use
            $response = array(
                'result' => $result,
                'start' => $start,
                'limit' => $limit,
                'totalResults' => $totalResults,
                'fileName' => $returnURL,
                'percentage' => $percentage
            );
            echo json_encode($response);
            // Make sure nothing else is sent, our file is done
            //return Excel::download(new AssociateCommissionBranchExport($data,$startDate,$endDate), 'branch_associatecommission.xlsx');
        }
    }
}