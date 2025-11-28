<?php
namespace App\Http\Controllers\Branch\DemandAdvice;
use App\Http\Controllers\Branch\CommanTransactionsController;
use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DateTime;
use App\Models\Files;
use App\Models\AllHeadTransaction;
use App\Models\AccountHeads;
use App\Models\InvestmentBalance;
use App\Models\DemandAdviceExpense;
use App\Models\DemandAdvice;
use App\Models\SavingAccount;
use App\Models\Employee;
use App\Models\RentLiability;
use App\Models\Memberinvestments;
use App\Models\RedemandDemandAdvice;
use App\Models\Branch;
use App\Models\Daybook;
use App\Models\SamraddhBank;
use App\Models\SamraddhCheque;
use App\Models\SamraddhBankClosing;
use App\Models\BranchCash;
use App\Models\Member;
use App\Models\SavingAccountTranscation;
use App\Models\TransactionReferences;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use URL;
use DB;
use Session;
use App\Services\ImageUpload;
use App\Http\Traits\IsLoanTrait;
use App\Models\Memberloans;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Demand Advice DemandAdviceController
    |--------------------------------------------------------------------------
    |
    | This controller handles demand advice all functionlity.
*/
class DemandAdviceController extends Controller
{
    /**
     * Create a new controller instance.
     * @return void
     */
    use IsLoanTrait;
    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }
    /**
     * Display a listing of the account heads.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['title'] = 'Demand Advice Listing';
        $getBranchId = getUserBranchId(Auth::user()->id);
        $data['branch_id'] = $getBranchId->id;
        return view('templates.branch.demand-advice.demand-advice-listing', $data);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function demandAdviceListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $getBranchId = getUserBranchId(Auth::user()->id);
            $branch_id = $getBranchId->id;
            $data = DemandAdvice::with('expenses', 'branch')->where('branch_id', $branch_id);
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['date_from'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));
                    if ($arrFormData['date_to'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereHas('expenses', function ($query) use ($startDate, $endDate) {
                        $query->whereBetween(\DB::raw('DATE(date)'), [$startDate, $endDate]);
                    });
                }
                if ($arrFormData['filter_branch'] != '') {
                    $branchId = $arrFormData['filter_branch'];
                    $data = $data->where('branch_id', '=', $branchId);
                }
                if ($arrFormData['advice_type'] != '') {
                    $advice_id = $arrFormData['advice_type'];
                    $data = $data->where('payment_type', '=', $advice_id);
                }
            }
            $data = $data->orderby('id', 'DESC')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('payment_type', function ($row) {
                    if ($row->payment_type == 0) {
                        $payment_type = 'Expenses';
                    } elseif ($row->payment_type == 1) {
                        $payment_type = 'Maturity';
                    } elseif ($row->payment_type == 2) {
                        $payment_type = 'Prematurity';
                    } elseif ($row->payment_type == 3) {
                        $payment_type = 'Death Help';
                    } else {
                        $payment_type = '';
                    }
                    return $payment_type;
                })
                ->rawColumns(['payment_type'])
                ->addColumn('sub_payment_type', function ($row) {
                    if ($row->sub_payment_type == 0 && $row->sub_payment_type != '') {
                        $sub_payment_type = 'Fresh Expense';
                    } elseif ($row->sub_payment_type == 1 && $row->sub_payment_type != '') {
                        $sub_payment_type = 'TA advance / Imprest';
                    } elseif ($row->sub_payment_type == 2 && $row->sub_payment_type != '') {
                        $sub_payment_type = 'Advanced Salary';
                    } elseif ($row->sub_payment_type == 3 && $row->sub_payment_type != '') {
                        $sub_payment_type = 'Advanced Rent';
                    } else {
                        $sub_payment_type = '';
                    }
                    return $sub_payment_type;
                })
                ->rawColumns(['sub_payment_type'])
                ->addColumn('branch', function ($row) {
                    $branch = $row['branch']->name;
                    return $branch;
                })
                ->rawColumns(['branch'])
                ->addColumn('status', function ($row) {
                    if ($row->status == 0) {
                        $status = 'Pending';
                    } else {
                        $status = 'Approved';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->addColumn('created_at', function ($row) {
                    return date("d/m/Y", strtotime($row->created_at));
                })
                ->rawColumns(['created_at'])
                ->addColumn('action', function ($row) {
                    $vurl = URL::to("branch/demand-advice/view/" . $row->id . "");
                    $url = URL::to("branch/demand-advice/edit-demand-advice/" . $row->id . "");
                    $deleteurl = URL::to("branch/delete-demand-advice/" . $row->id . "");
                    /*$approveurl = URL::to("admin/approve-demand-advice/".$row->id."");*/
                    $btn = '';
                    $btn .= '<a href="' . $vurl . '"><i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . $url . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                    /*$btn .= '<a href="'.$approveurl.'"><i class="fas fa-thumbs-up"></i>Approve</a>';*/
                    $btn .= '<a class="dropdown-item delete-demand-advice" href="' . $deleteurl . '"><i class="fas fa-trash-alt mr-2"></i>Delete</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    /**
     * Display a listing of the account heads.
     *
     * @return \Illuminate\Http\Response
     */
    public function report()
    {
        if (!in_array('Demand Advice Report', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $data['title'] = 'Demand Advice Report';
        $getBranchId = getUserBranchId(Auth::user()->id);
        $data['branch_id'] = $getBranchId->id;
        return view('templates.branch.demand-advice.report', $data);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function reportListing(Request $request)
    {
        $arrFormData = array();
        if (!empty($_POST['searchform'])) {
            foreach ($_POST['searchform'] as $frm_data) {
                $arrFormData[$frm_data['name']] = $frm_data['value'];
            }
        }
        if ($request->ajax() && $arrFormData['company_id']) {
            /*
            $getBranchId = getUserBranchId(Auth::user()->id);
            $branch_id = $getBranchId->id;
            $data = DemandAdvice::with('expenses', 'branch', 'demandReason')->where('branch_id', $branch_id)->with(['company' => function ($query) {
                $query->select('id', 'name');
            }])->where('payment_type','!=',0)->where('is_deleted', 0);

            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['date_from'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));
                    if ($arrFormData['date_to'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(date)'), [$startDate, $endDate]);
                }
                if (isset($arrFormData['account_number']) && $arrFormData['account_number'] != '') {
                    $account_number = $arrFormData['account_number'];
                    $data = $data->where('account_number', '=', $account_number);
                }
                // if ($arrFormData['filter_branch'] != '') {
                //     $branchId = $arrFormData['filter_branch'];
                //     $data = $data->where('branch_id', '=', $branchId);
                // }
                if ($arrFormData['company_id'] != '') {
                    $company_id = $arrFormData['company_id'];
                    $data = $data->where('company_id', '=', $company_id);
                }
                if ($arrFormData['advice_type'] != '') {
                    $advice_id = $arrFormData['advice_type'];
                    $advice_type_id = $arrFormData['expense_type'];
                    if ($advice_id == 0 || $advice_id == 1 || $advice_id == 2) {
                        if ($advice_type_id != '') {
                            $data = $data->where('payment_type', '=', $advice_id)->where('sub_payment_type', $advice_type_id);
                        } else {
                            $data = $data->where('payment_type', '=', $advice_id);
                        }
                    } elseif ($advice_id == 3) {
                        $data = $data->where('payment_type', '=', 3)->where('death_help_catgeory', '=', 0);
                    } elseif ($advice_id == 4) {
                        $data = $data->where('payment_type', '=', 3)->where('death_help_catgeory', '=', 1);
                    } elseif ($advice_id == 5) {
                        $data = $data->where('payment_type', '=', 5);
                    }
                }
                if ($arrFormData['voucher_number'] != '') {
                    $voucher_number = $arrFormData['voucher_number'];
                    $data = $data->where('voucher_number', '=', $voucher_number);
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    if ($status == "2") {
                        $data = $data->where('is_reject', '1');
                    } else {
                        $data = $data->where('status', '=', $status);
                    }
                }
            }

            // $data = $data->orderby('created_at','DESC')->get();
            $member_id = '';
            $data1 = $data->get();
            $count = $data1->count();
            $data = $data->offset($_POST['start'])->limit($_POST['length'])->orderBy('created_at', 'DESC')->get();
            $totalCount = count($data);
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                if (isset($row['branch']->name)) {
                    $val['branch_name'] = $row['branch']->name;
                } else {
                    $val['branch_name'] = 'N/A';
                }
                if (isset($row['company']->name)) {
                    $val['company_name'] = $row['company']->name;
                } else {
                    $val['company_name'] = 'N/A';
                }
                if (isset($row['maturity_payment_mode'])) {
                    $val['maturity_payment_mode'] = $row['maturity_payment_mode'];
                } else {
                    $val['maturity_payment_mode'] = 'N/A';
                }
                if (isset($row['branch']->branch_code)) {
                    $val['branch_code'] = $row['branch']->branch_code;
                } else {
                    $val['branch_code'] = 'N/A';
                }
                if (isset($row['branch']->sector)) {
                    $val['sector'] = $row['branch']->sector;
                } else {
                    $val['sector'] = 'N/A';
                }
                if (isset($row['branch']->regan)) {
                    $val['regan'] = $row['branch']->regan;
                } else {
                    $val['regan'] = 'N/A';
                }
                if (isset($row['branch']->zone)) {
                    $val['zone'] = $row['branch']->zone;
                } else {
                    $val['zone'] = 'N/A';
                }
                if (isset($row->investment_id)) {
                    $member_id = getInvestmentDetails($row->investment_id)->member_id;
                    $member_name = $row->investment->member->first_name.' '.$row->investment->member->last_name??'';
                    $val['name'] = $member_name;
                } else {
                    $val['name'] = 'N/A';
                }
                $loanDetail =  $this->getData(new Memberloans(), $member_id);
                $val['is_loan'] = $loanDetail;
                if ($row->investment_id) {
                    $val['associate_code'] = $row->investment->associateMember->associate_no;
                } else {
                    $val['associate_code'] = "N/A";
                }
                // if (isset($row->investment_id)) {
                //     $associate_id = getInvestmentDetails($row->investment_id)->member_id;
                //     $associate_code = getMemberData($associate_id)->associate_code;
                //     $val['associate_code'] = $associate_code;
                // } else {
                //     $val['associate_code'] = 'N/A';
                // }
                if (isset($row->tds_amount)) {
                    $val['tds_amount'] = round($row->tds_amount);
                } else {
                    $val['tds_amount'] = 'N/A';
                }
                if ($row->id) {
                    if ($row->payment_mode == 2) {
                        $transaction = AllHeadTransaction::where('head_id', 92)->where('type_id', $row->id)->first();;
                        if ($transaction) {
                            $val['neft_charge'] = round($transaction->amount);
                        } else {
                            $val['neft_charge'] = 'N/A';
                        }
                    } else {
                        $val['neft_charge'] = 'N/A';
                    }
                } else {
                    $val['neft_charge'] = 'N/A';
                }
                    if ($row->investment_id) {
                    $associate_code = $row->investment->associateMember->associate_no;
                    $associate_name = Member::where('associate_no', $associate_code)->first();
                    if (isset($associate_name->first_name)  && isset($associate_name->last_name)) {
                        $associate_name = $associate_name->first_name . ' ' . $associate_name->last_name;
                    } else {
                        if (isset($associate_name->first_name)) {
                            $associate_name = $associate_name->first_name;
                        }
                    }
                } else {
                    $associate_name = 'N/A';
                }
                $val['associate_name'] = $associate_name;
                $val['payment_trf_amt'] = round($row->maturity_amount_payable - $row->tds_amount);
                $opening_date = 'N/A';
                if (isset($row->payment_type)) {
                    if ($row->investment_id) {
                        $date = getInvestmentDetails($row->investment_id);
                        if ($date) {
                            $opening_date = date("d/m/Y", strtotime($date->created_at));
                        } else {
                            $opening_date = 'None';
                        }
                    }
                    $val['ac_opening_date'] = $opening_date;
                } else {
                    if ($row->opening_date) {
                        $opening_date = date("d/m/Y", strtotime($row->opening_date));
                    } else {
                        $opening_date = "N/A";
                    }
                    $val['ac_opening_date'] = $opening_date;
                }
                $type = '';
                if ($row->payment_type == 0) {
                    $type =  'Expenses';
                } elseif ($row->payment_type == 1) {
                    $type =  'Maturity';
                } elseif ($row->payment_type == 2) {
                    $type =  'Prematurity';
                } elseif ($row->payment_type == 3) {
                    if ($row->sub_payment_type == '4') {
                        $type =  'Death Help';
                    } elseif ($row->sub_payment_type == '5') {
                        $type =  'Death Claim';
                    }
                } elseif ($row->payment_type == 4) {
                    $type =  "Emergency Maturity";
                }
                $val['advice_type'] = $type;
                $sub_type = '';
                if ($row->sub_payment_type == '0') {
                    $sub_type =  'Fresh Expense';
                } elseif ($row->sub_payment_type == '1') {
                    $sub_type =  'TA Advanced';
                } elseif ($row->sub_payment_type == '2') {
                    $sub_type =  'Advanced salary';
                } elseif ($row->sub_payment_type == '3') {
                    $sub_type =  'Advanced Rent';
                } elseif ($row->sub_payment_type == '4') {
                    $sub_type =  'N/A';
                } elseif ($row->sub_payment_type == '5') {
                    $sub_type =  'N/A';
                } else {
                    $sub_type =  'N/A';
                }
                $val['expense_type'] = $sub_type;
                $val['date'] = date("d/m/Y", strtotime($row->date));
                $val['voucher_number'] = $row->voucher_number;
                if (isset($row->payment_mode)) {
                    if ($row->payment_mode == 0) {
                        $mode = "Cash";
                    }
                    if ($row->payment_mode == 1) {
                        $mode = "Cheque";
                    }
                    if ($row->payment_mode == 2) {
                        $mode = "Online Transfer";
                    }
                    if ($row->payment_mode == 3) {
                        $mode = "SSB Transfer";
                    }
                } else {
                    $mode = "N/A";
                }
                $val['payment_mode'] = $mode;
                if (isset($row->investment_id)) {
                    $total_amount = Daybook::where('investment_id', $row->investment_id)->where('is_deleted',0)->whereIn('transaction_type', [2, 4])->sum('deposit');
                } else {
                    $total_amount = 'N/A';
                }
                $val['total_amount'] = $total_amount;
                if (isset($row->maturity_amount_payable)) {
                    $maturity_amount_payable = round($row->maturity_amount_payable);
                } else {
                    $maturity_amount_payable = 'N/A';
                }
                $val['total_payable_amount'] = round($maturity_amount_payable);
                if ($row->payment_type == 4) {
                    if ($row->investment_id) {
                        $data = getInvestmentDetails($row->investment_id);
                        $account =  $data->account_number;
                    }
                } else {
                    if ($row->account_number) {
                        $account = $row->account_number;
                    } else {
                        $account =  'N/A';
                    }
                }
                $val['account_number'] =  $account;
                if (isset($row->investment_id)) {
                    $member_id = getInvestmentDetails($row->investment_id)->member_id;
                    $ac = SavingAccount::where('member_id', $member_id)->first();
                    if ($ac) {
                        $val['ssb_account_number'] = $ac->account_no;
                    } else {
                        $val['ssb_account_number'] = 'N/A';
                    }
                } else {
                    $val['ssb_account_number'] = 'N/A';
                }
                if (isset($row->bank_account_number)) {
                    $val['bank_account_number'] =  $row->bank_account_number;
                } else {
                    $val['bank_account_number'] =  "N/A";
                }
                if (isset($row->bank_ifsc)) {
                    $val['ifsc_code'] =  $row->bank_ifsc;
                } else {
                    $val['ifsc_code'] =  "N/A";
                }
                if ($row->is_print == 0) {
                    $print = 'Yes';
                } else {
                    $print = 'No';
                }
                $val['print'] = $print;
                if ($row->status == 0 && $row->is_reject == 1) {
                    $status = 'Rejected';
                } else {
                    if ($row->status == 0) {
                        $status = 'Pending';
                    } else {
                        $status = 'Approved';
                    }
                }
                $val['status'] = $status;
                if (isset($row['demandReason'][0]->reason)) {
                    $reason = $row['demandReason'][0]->reason;
                } else {
                    $reason = 'N/A';
                }
                $val['reason'] = $reason;
                $vurl = URL::to("branch/demand-advice/view/" . $row->id . "");
                $url = URL::to("branch/demand-advice/edit-demand-advice/" . $row->id . "");
                $deleteurl = URL::to("branch/delete-demand-advice/" . $row->id . "");
                $btn = '';
                if ($row->is_reject  != 0) {
                    $btn .= '<a class="dropdown-item" href="' . $url . '"><i class="icon-pencil7"></i>Edit</a>';
                    $btn .= '<a class="dropdown-item delete-demand-advice" href="' . $deleteurl . '"><i class="fas fa-trash-alt"></i></a>';
                }
                if ($row->status == 1) {
                    $btn = '<a class="dropdown-item" href="' . $vurl . '"><i class="icon-pencil7 mr-2"></i>Print</a>';
                }
                $btn .= '</div></div></div>';
                $val['action'] = $btn;
                $rowReturn[] = $val;
            }
            */
            $getBranchId = getUserBranchId(Auth::user()->id);
            $branchId = $getBranchId->id;
            $fromDate = $arrFormData['date_from'];
            $toDate = $arrFormData['date_to'];
            $companyId = $arrFormData['company_id'];
            $account_number = $arrFormData['account_number'];
            $advice_id = $arrFormData['advice_type'];
            $advice_type_id = $arrFormData['expense_type'];
            $voucher_number = $arrFormData['voucher_number'];
            $start = $request->start;
            $length = $request->length;
            $status = $arrFormData['status'];
            if($arrFormData['is_search'] != 'no'){

                $data = \App\Models\DemandAdviceReport::where('payment_type', '!=', 0) 
                ->when($fromDate != '', function ($query) use ($fromDate, &$startDate, $toDate) {
                    $startDate = date("Y-m-d", strtotime(convertDate($fromDate)));
                    $query->when($toDate != '', function ($query) use ($toDate, &$endDate) {
                        $endDate = date("Y-m-d", strtotime(convertDate($toDate)));
                    });
                    $query->whereBetween('date', [$startDate, $endDate]);
                })
                ->when($branchId != '', function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->when($companyId != '', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                })
                ->when($account_number != '', function ($query) use ($account_number, $advice_id) {
                    if ($advice_id != '' && $advice_id == 5) {
                        $query->where('investmentAccountno', $account_number);
                    } else {
                        $query->where('account_number', '=', $account_number);
                    }
                }) 
                ->when($advice_id != '', function ($query) use ($advice_id, $advice_type_id) {
                    if ($advice_id == 3) {
                        $query->where('payment_type', '=', 3)->where('death_help_catgeory', '=', 0);
                    } elseif ($advice_id == 4) {
                        $query->where('payment_type', '=', 3)->where('death_help_catgeory', '=', 1);
                    } elseif ($advice_id == 5) {
                        $query->where('payment_type', '=', 4);
                    } else {
                        $query->where('payment_type', '=', $advice_id);
                    }                    
                })
                ->when($voucher_number != '', function ($query) use ($voucher_number) {
                    $query->where('voucher_number', $voucher_number);
                })
                ->when($status != '', function ($query) use ($status) {
                    $query->when(in_array($status,[0,1]),function($q) use($status){
                        $q->where('status', $status)->where('is_reject','0');
                    })->when($status == 2 ,function ($q) {                        
                        $q->where('is_reject','1');
                    });
                })
                ;
                // dd($data->get()->toArray());
                $data->orderBy('created_at', 'DESC');
                $totalResults = $data->count('id');
                $results = $data->get();  
                $sno = $_POST['start'];
                $paymentType = [
                    '0' => 'Expenses',
                    '1' => 'Maturity',
                    '2' => 'Pre-Maturity',
                    '3' => [
                        '4' => 'Death Help',
                        '5' => 'Death Claim',
                    ],
                    '4' => 'Emergency Maturity',
                ];
                $sub_types = [
                    '0' => 'Fresh Expense',
                    '1' => 'TA Advanced',
                    '2' => 'Advanced salary',
                    '3' => 'Advanced Rent',
                    '4' => 'Death Help',
                    '5' => 'Death Claim',
                ];
                $modes = [
                    0 => 'Cash',
                    1 => 'Cheque',
                    2 => 'Online Transfer',
                    3 => 'SSB Transfer',
                ];
                $rowReturn = [];
                $redemandDemandAdviceReason = RedemandDemandAdvice::pluck('reason','demand_id')->toArray();
                foreach ($results->slice($start,$length) as $row) {
                    // $row = $val[0];
                    $sno++;
                    $loanDetail = 'NO';
                    if((isset($row->loancount)&&($row->loancount>0)) || (isset($row->grouploancount)&&($row->grouploancount>0)))
                    {
                        $loanDetail = 'Yes';
                    }
                    $rtgs = 'N/A';
                    if ($row->id) {
                        if ($row->payment_mode == 2) {
                            $transaction = $row['demandAmountHead'];
                            if (isset($transaction->amount)) {
                                $rtgs = round($transaction->amount);
                            } else {
                                $rtgs =   'N/A';
                            }
                        } else {
                            $rtgs =   'N/A';
                        }
                    } else {
                        $rtgs = 'N/A';
                    }
                    // by table member_loan can get due amount upto system date                    
                    $account = ($row->payment_type == 4 && $row->investment_id) ? $row->investmentAccountno : ($row->account_number ?? 'N/A');
                    $vurl = URL::to("admin/demand-advice/view/" . $row->id . "");
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    if ($row->status == 1) {
                        if (Auth::user()->id != "13") {
                            $btn .= '<a class="dropdown-item" href="' . $vurl . '"><i class="icon-pencil7 mr-2"></i>Print</a>';
                        }
                    }
                    $btn .= '</div></div></div>';                   
                    $status = ($row->status == 0 && $row->is_reject == 1) ? 'Rejected' : (($row->status == 1 && $row->is_reject == 0) ? 'Approved' : 'Pending');
                    $val = [
                        'DT_RowIndex' => $sno,
                        'company_name' => isset($row->comname) ? $row->comname : 'N/A',
                        'customer_id' => isset($row->customerID) ? $row->customerID : 'N/A',
                        'member_id' =>isset($row->memberId) ? $row->memberId : 'N/A',
                        'branch_name' => isset($row->bname) ? $row->bname : 'N/A',
                        'branch_code' => isset($row->branch_code) ? $row->branch_code : 'N/A',
                        'name' => isset($row->memberfname) ? $row->memberfname . ' ' . $row->memberlname : 'N/A',
                        'nominee_name' => isset($row->memberNomineesName) ? $row->memberNomineesName : 'N/A',
                        'associate_code' => isset($row->associateCode) ? $row->associateCode : 'N/A',
                        'associate_name' => isset($row->assoFname) ? $row->assoFname . ' ' . $row->assoLname : 'N/A',
                        'ac_opening_date' => isset($row->m_created_at) ? (date("d/m/Y", strtotime($row->m_created_at))) : (isset($row->opening_date) ? (date("d/m/Y", strtotime($row->opening_date))) : 'N/A'),
                        'advice_type' => isset($row->payment_type) ? (($row->payment_type == 3) ? $paymentType[$row->payment_type][$row->sub_payment_type] : $paymentType[$row->payment_type]) : 'N/A',
                        'expense_type' => isset($sub_types[$row->sub_payment_type]) ? $sub_types[$row->sub_payment_type] : 'N/A',
                        'date' => date("d/m/Y", strtotime($row->date)),
                        'voucher_number' => $row->voucher_number ?? 'N/A',
                        'payment_mode' => isset($modes[$row->payment_mode]) ? $modes[$row->payment_mode] : 'N/A',
                        'payment_trf_amt' => ($row->payment_type == 2) ? round($row->final_amount) : round($row->final_amount),
                        'is_loan'=>$loanDetail,
                        'tds_amount' => round($row->tds_amount) ?? 'N/A',
                        'interest_amount' => round($row->interestAmount) ?? 'N/A',
                        'total_payable_amount' => round($row->maturity_amount_till_date) ?? 'N/A',
                        'neft_charge' => $rtgs,
                        'account_number' => $account,
                        'ssb_account_number' => ($row->ssb_account)??'N/A',
                        'total_amount' => isset($row->total_deposit_amount) ? $row->total_deposit_amount : 0, 
                        'bank_account_number' => isset($row->bank_account_number) ? $row->bank_account_number : "N/A",
                        'ifsc_code' => $row->bank_ifsc ?? 'N/A',
                        'print' => ($row->is_print == 0) ? 'Yes' : 'No',
                        'maturity_payment_mode' => $row->maturity_payment_mode ?? 'N/A',
                        'reason' =>( $row->is_reject == 1 ) ?  ($redemandDemandAdviceReason[$row->id]??'N/A') : 'N/A',
                        'status' => $status,
                        'action'=> ($row->is_print == 0) ? $btn : 'N/A',
                        // Rest of the key-value pairs go here...
                    ];
                    $rowReturn[] = $val;
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalResults, "recordsFiltered" => $totalResults, "data" => $rowReturn);
                return json_encode($output);
            } else {
                $output = array("draw" => $_POST['draw'], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
                return json_encode($output);
            }
        } else{
            return json_encode([]);
        }
        // }
        $output = array("draw" => $_POST['draw'], "recordsTotal" => $count, "recordsFiltered" => $count, "data" => $rowReturn);
        return json_encode($output);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function taAdvancedListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $getBranchId = getUserBranchId(Auth::user()->id);
            $branch_id = $getBranchId->id;
            $data = DemandAdvice::with('expenses', 'branch')->where('payment_type', 0)->where('sub_payment_type', 1)->where('status', 1)->where('ta_advanced_adjustment', 1)->where('branch_id', $branch_id);;
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['date_from'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));
                    if ($arrFormData['date_to'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween('date', [$startDate, $endDate]);
                }
                if ($arrFormData['employee_code'] != '') {
                    $employee_code = $arrFormData['employee_code'];
                    $data = $data->where('employee_code', '=', $employee_code);
                }
                if ($arrFormData['ta_advanced_employee_name'] != '') {
                    $employee_name = $arrFormData['ta_advanced_employee_name'];
                    $data = $data->where('employee_name', '=', $employee_name);
                }
            }
            // else{
            //     $data=$data->where('branch_id',0);
            // }
            $data = $data->orderby('id', 'DESC')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('payment_type', function ($row) {
                    if ($row->payment_type == 0) {
                        $payment_type = 'Expenses';
                    } elseif ($row->payment_type == 1) {
                        $payment_type = 'Maturity';
                    } elseif ($row->payment_type == 2) {
                        $payment_type = 'Prematurity';
                    } elseif ($row->payment_type == 3) {
                        $payment_type = 'Death Help';
                    } else {
                        $payment_type = '';
                    }
                    return $payment_type;
                })
                ->rawColumns(['payment_type'])
                ->addColumn('sub_payment_type', function ($row) {
                    if ($row->sub_payment_type == 0 && $row->sub_payment_type != '') {
                        $sub_payment_type = 'Fresh Expense';
                    } elseif ($row->sub_payment_type == 1 && $row->sub_payment_type != '') {
                        $sub_payment_type = 'TA advance / Imprest';
                    } elseif ($row->sub_payment_type == 2 && $row->sub_payment_type != '') {
                        $sub_payment_type = 'Advanced Salary';
                    } elseif ($row->sub_payment_type == 3 && $row->sub_payment_type != '') {
                        $sub_payment_type = 'Advanced Rent';
                    } else {
                        $sub_payment_type = '';
                    }
                    return $sub_payment_type;
                })
                ->rawColumns(['sub_payment_type'])
                ->addColumn('branch', function ($row) {
                    $branch = $row['branch']->name;
                    return $branch;
                })
                ->rawColumns(['branch'])
                ->addColumn('employee_code', function ($row) {
                    $employee_code = $row->employee_code;
                    return $employee_code;
                })
                ->rawColumns(['employee_code'])
                ->addColumn('employee_name', function ($row) {
                    $employee_name = $row->employee_name;
                    return $employee_name;
                })
                ->rawColumns(['employee_name'])
                ->addColumn('advanced_amount', function ($row) {
                    $advanced_amount = $row->advanced_amount;
                    return $advanced_amount;
                })
                ->rawColumns(['advanced_amount'])
                ->addColumn('status', function ($row) {
                    if ($row->status == 0) {
                        $status = 'Pending';
                    } else {
                        $status = 'Approved';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->addColumn('created_at', function ($row) {
                    return date("d/m/Y", strtotime($row->date));
                })
                ->rawColumns(['created_at'])
                ->addColumn('action', function ($row) {
                    $url = URL::to("branch/demand-advice/adjust-ta-advanced/" . $row->id . "");
                    $btn = '<a href="' . $url . '" title="Adjustment"><i class="fas fa-eye text-default mr-2 "></i></a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    /**
     * Add Rent Liability View.
     * Route: /member/passbook
     * Method: get
     * @return  array()  Response
     */
    public function addAdvice()
    {
        if (!in_array('Add Demand Advice', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }else{
            $data['title'] = 'Add Demand Advice';
            $getBranchId = getUserBranchId(Auth::user()->id);
            $data['branch_id'] = $getBranchId->id;
            $data['expenseCategories'] = AccountHeads::select('id', 'sub_head')->where('parent_id', 4)->where('status', 0)->get();
            $data['expenseSubCategories'] = AccountHeads::select('id', 'sub_head', 'parent_id')->whereIn('parent_id', array(14, 86))->whereNotIn('id', array(37, 40, 53, 87, 88, 92))->where('status', 0)->get();
            $data['liabilityHeads'] = array('');
            $data['rentOwners'] = RentLiability::select('id', 'owner_name')->where('status', 0)->get();
            return view('templates.branch.demand-advice.add-demand-advice', $data);
        }
    }
    /**
     * Save Demand Advice.
     * Route: /save-account-head
     * Method: get
     * @return  array()  Response
     */
    public function saveAdvice(Request $request)
    {
        $rules = [
            'paymentType' => 'required',
            'branch' => 'required',
            //'date' => 'required',
            //'expenseType' => 'required',
        ];
        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];
        $this->validate($request, $rules, $customMessages);
        //echo "<pre>"; print_r($request->all()); die;
        DB::beginTransaction();
        try {
            $voucherRecord = DemandAdvice::orderby('id', 'desc')->first('mi_code');
            if ($voucherRecord) {
                $miCodeAdd = $voucherRecord->mi_code + 1;
            } else {
                $miCodeAdd = 1;
            }
            $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
            $voucherNumber = '32' . date("Y") . '' . date('m') . '' . $miCode;
            if ($request->paymentType == 0 && $request->expenseType == 0) {
                $daData = [
                    'payment_type' => $request->paymentType,
                    'sub_payment_type' => $request->expenseType,
                    'branch_id' => $request->branch,
                    'mi_code' => $miCode,
                    'voucher_number' => $voucherNumber,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->date))),
                    'created_at' => $request->created_at,
                ];
                $demandAdvice = DemandAdvice::create($daData);
                $demandAdviceId = $demandAdvice->id;
                foreach ($request->fresh_expense as $key => $value) {
                    if (isset($value['bill_photo'])) {
                        $mainFolder = 'demand-advice/expense';
                        // $mainFolder = storage_path() . '/images/demand-advice/expense';
                        $file = $value['bill_photo'];
                        $uploadFile = $file->getClientOriginalName();
                        $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                        $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                        ImageUpload::upload($file, $mainFolder,$fname);
                        
                        $fData = [
                            'file_name' => $fname,
                            'file_path' => $mainFolder,
                            'file_extension' => $file->getClientOriginalExtension(),
                        ];
                        $res = Files::create($fData);
                        $file_id = $res->id;
                    } else {
                        $file_id = NULL;
                    }
                    $feData = [
                        'demand_advice_id' => $demandAdviceId,
                        'category' => $value['expenseCategory'],
                        //'subcategory' => $value['expenseSubCategory'],
                        'subcategory1' => $value['expenseSubCategory1'],
                        'subcategory2' => $value['expenseSubCategory2'],
                        'subcategory3' => $value['expenseSubCategory3'],
                        'party_name' => $value['party_name'],
                        'particular' => $value['particular'],
                        'mobile_number' => $value['mobile_number'],
                        'amount' => $value['amount'],
                        'bill_number' => $value['billNumber'],
                        'bill_file_id' => $file_id,
                        'status' => 0,
                        'created_at' => $request->created_at,
                        'company_id' => $request->company_id,
                    ];
                    $demandAdvice = DemandAdviceExpense::create($feData);
                }
            } elseif ($request->paymentType == 0 && $request->expenseType == 1) {
                $daData = [
                    'payment_type' => $request->paymentType,
                    'sub_payment_type' => $request->expenseType,
                    'branch_id' => $request->branch,
                    'mi_code' => $miCode,
                    'voucher_number' => $voucherNumber,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->date))),
                    'employee_code' => $request->ta_employee_code,
                    'employee_id' => $request->ta_employee_id,
                    'employee_name' => $request->ta_employee_name,
                    'particular' => $request->ta_particular,
                    'advanced_amount' => $request->ta_advance_amount,
                    'created_at' => $request->created_at,
                    'company_id' => $request->company_id,
                ];
                $demandAdvice = DemandAdvice::create($daData);
                $demandAdviceId = $demandAdvice->id;
            } elseif ($request->paymentType == 0 && $request->expenseType == 2) {
                if (isset($request->advanced_salary_letter_photo)) {
                    // $mainFolder = storage_path() . '/images/demand-advice/advancedsalary';
                    $mainFolder = 'demand-advice/advancedsalary';
                    $file = $request->advanced_salary_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    ImageUpload::upload($file, $mainFolder,$fname);
                    
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $res = Files::create($fData);
                    $file_id = $res->id;
                } else {
                    $file_id = NULL;
                }
                $daData = [
                    'payment_type' => $request->paymentType,
                    'sub_payment_type' => $request->expenseType,
                    'branch_id' => $request->branch,
                    'mi_code' => $miCode,
                    'voucher_number' => $voucherNumber,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->date))),
                    'employee_code' => $request->advanced_salary_employee_code,
                    'employee_id' => $request->advanced_salary_employee_id,
                    'employee_name' => $request->advanced_salary_employee_name,
                    'mobile_number' => $request->advanced_salary_mobile_number,
                    'amount' => $request->advanced_salary_amount,
                    'letter_photo_id' => $file_id,
                    'narration' => $request->advanced_salary_narration,
                    'ssb_account' => $request->advanced_salary_ssb_account,
                    'bank_name' => $request->advanced_salary_bank_name,
                    'bank_account_number' => $request->advanced_salary_bank_account_number,
                    'bank_ifsc' => $request->advanced_salary_ifsc_code,
                    'created_at' => $request->created_at,
                    'company_id' => $request->company_id,
                ];
                $demandAdvice = DemandAdvice::create($daData);
                $demandAdviceId = $demandAdvice->id;
            } elseif ($request->paymentType == 0 && $request->expenseType == 3) {
                $ownersName = RentLiability::where('id', $request->advanced_rent_party_name)->first('owner_name');
                $daData = [
                    'payment_type' => $request->paymentType,
                    'sub_payment_type' => $request->expenseType,
                    'branch_id' => $request->branch,
                    'mi_code' => $miCode,
                    'voucher_number' => $voucherNumber,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->date))),
                    'employee_code' => $request->advanced_salary_employee_code,
                    'employee_id' => $request->advanced_rent_employee_id,
                    'employee_name' => $request->advanced_rent_employee_name,
                    'owner_id' => $request->advanced_rent_party_name,
                    'owner_name' => $ownersName->owner_name,
                    'mobile_number' => $request->advanced_rent_mobile_number,
                    'amount' => $request->advanced_rent_amount,
                    'narration' => $request->advanced_rent_narration,
                    'ssb_account' => $request->advanced_rent_ssb_account,
                    'bank_name' => $request->advanced_rent_bank_name,
                    'bank_account_number' => $request->advanced_rent_bank_account_number,
                    'bank_ifsc' => $request->advanced_rent_ifsc_code,
                    'created_at' => $request->created_at,
                    'company_id' => $request->company_id,
                ];
                $demandAdvice = DemandAdvice::create($daData);
                $demandAdviceId = $demandAdvice->id;
            } elseif ($request->paymentType == 2 && $request->maturity_prematurity_type == 0) {
                if (isset($request->maturity_letter_photo)) {
                    // $mainFolder = storage_path() . '/images/demand-advice/maturity-prematurity';
                    $mainFolder = 'demand-advice/maturity-prematurity';
                    $file = $request->maturity_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    ImageUpload::upload($file, $mainFolder,$fname);
                    
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $res = Files::create($fData);
                    $file_id = $res->id;
                } else {
                    $file_id = NULL;
                }
                $daData = [
                    'payment_type' => 1,
                    'branch_id' => $request->branch,
                    'mi_code' => $miCode,
                    'voucher_number' => $voucherNumber,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->maturity_prematurity_date))),
                    'letter_photo_id' => $file_id,
                    'investment_id' => $request->maturity_investmnet_id,
                    'account_number' => $request->maturity_account_number,
                    'opening_date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->maturity_opening_date))),
                    'plan_name' => $request->maturity_plan_name,
                    'tenure' => $request->maturity_tenure,
                    'account_holder_name' => $request->maturity_account_holder_name,
                    'father_name' => $request->maturity_father_name,
                    'maturity_prematurity_category' => $request->maturity_category,
                    'maturity_prematurity_amount' => $request->maturity_amount,
                    'mobile_number' => $request->maturity_mobile_number,
                    'ssb_account' => $request->maturity_ssb_account,
                    'bank_account_number' => $request->maturity_bank_account_number,
                    'bank_ifsc' => $request->maturity_ifsc_code,
                    'created_at' => $request->created_at,
                    'company_id' => $request->company_id,
                ];
                if (isset($request->payment_mode) && $request->payment_mode != "") {
                    $daData["maturity_payment_mode"] = $request->payment_mode;
                }
                $demandAdvice = DemandAdvice::create($daData);
                $demandAdviceId = $demandAdvice->id;
            } elseif ($request->paymentType == 2 && $request->maturity_prematurity_type == 1) {
                if (isset($request->prematurity_letter_photo)) {
                    // $mainFolder = storage_path() . '/images/demand-advice/maturity-prematurity';
                    $mainFolder = 'demand-advice/maturity-prematurity';
                    $file = $request->prematurity_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    ImageUpload::upload($file, $mainFolder,$fname);
                    
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $res = Files::create($fData);
                    $file_id = $res->id;
                } else {
                    $file_id = NULL;
                }
                $daData = [
                    'payment_type' => 2,
                    'branch_id' => $request->branch,
                    'mi_code' => $miCode,
                    'voucher_number' => $voucherNumber,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->maturity_prematurity_date))),
                    'letter_photo_id' => $file_id,
                    'investment_id' => $request->prematurity_investmnet_id,
                    'account_number' => $request->prematurity_account_number,
                    'opening_date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->prematurity_opening_date))),
                    'plan_name' => $request->prematurity_plan_name,
                    'tenure' => $request->prematurity_tenure,
                    'account_holder_name' => $request->prematurity_account_holder_name,
                    'father_name' => $request->prematurity_father_name,
                    'maturity_prematurity_category' => $request->prematurity_category,
                    'maturity_prematurity_amount' => $request->prematurity_amount,
                    'mobile_number' => $request->prematurity_mobile_number,
                    'ssb_account' => $request->prematurity_ssb_account,
                    'bank_account_number' => $request->prematurity_bank_account_number,
                    'bank_ifsc' => $request->prematurity_ifsc_code,
                    'created_at' => $request->created_at,
                    'company_id' => $request->company_id,
                ];
                if (isset($request->payment_mode) && $request->payment_mode != "") {
                    $daData["maturity_payment_mode"] = $request->payment_mode;
                }
                $demandAdvice = DemandAdvice::create($daData);
                $demandAdviceId = $demandAdvice->id;
            } elseif ($request->paymentType == 4) {
                if (isset($request->death_help_letter_photo)) {
                    // $mainFolder = storage_path() . '/images/demand-advice/death-help';
                    $mainFolder = 'demand-advice/death-help';
                    $file = $request->death_help_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    ImageUpload::upload($file, $mainFolder,$fname);
                    
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $res = Files::create($fData);
                    $file_id = $res->id;
                } else {
                    $file_id = NULL;
                }
                if ($request->death_help_category == 0) {
                    $subType = 4;
                } elseif ($request->death_help_category == 1) {
                    $subType = 5;
                }
                $daData = [
                    'payment_type' => 3,
                    'sub_payment_type' => $subType,
                    'branch_id' => $request->branch,
                    'mi_code' => $miCode,
                    'voucher_number' => $voucherNumber,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->death_help_date))),
                    'death_certificate_id' => $file_id,
                    'investment_id' => $request->death_help_investmnet_id,
                    'account_number' => $request->death_help_account_number,
                    'opening_date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->death_help_opening_date))),
                    'plan_name' => $request->death_help_plan_name,
                    'tenure' => $request->death_help_tenure,
                    'account_holder_name' => $request->death_help_account_holder_name,
                    'death_help_catgeory' => $request->death_help_category,
                    'deno' => $request->death_help_deno,
                    'deposited_amount' => $request->death_help_deposited_amount,
                    //'death_claim_amount' => $request->death_help_death_claim_amount,
                    // 'is_mature' => 0,
                    'nominee_name' => $request->death_help_nominee_name,
                    'naominee_member_id' => $request->death_help_nominee_member_id,
                    'mobile_number' => $request->death_help_mobile_number,
                    'ssb_account' => $request->death_help_ssb_account,
                    'created_at' => $request->created_at,
                    'company_id' => $request->company_id,
                ];
                if (isset($request->payment_mode) && $request->payment_mode != "") {
                    $daData["maturity_payment_mode"] = $request->payment_mode;
                }
                $demandAdvice = DemandAdvice::create($daData);
                $demandAdviceId = $demandAdvice->id;
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        if ($demandAdviceId) {
            return redirect()->route('branch.demand.application')->with('success', 'Demand Advice Added Successfully!');
        } else {
            return back()->with('alert', 'Problem With Creating Demand Advice');
        }
    }
    /**
     * Demand Advice View.
     * Route: /member/passbook
     * Method: get sss
     * @return  array()  Response
     */
    public function viewAdvice($id)
    {
        $data['title'] = 'View Demand Advice';
        $data['row'] = DemandAdvice::with('investment', 'expenses', 'branch')->where('id', $id)->first();
        return view('templates.branch.demand-advice.print-demand-advice', $data);
    }
    /**
     * Demand Advice View.
     * Route: /member/passbook
     * Method: get sss
     * @return  array()  Response
     */
    public function viewTaAdvanced()
    {
        if (!in_array('View TA advance and Imprest Advice', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $data['title'] = 'View TA advance and Imprest Advice';
        return view('templates.branch.demand-advice.view_ta_advanced', $data);
    }
    /**
     * Edit user View.
     * Route: /member/passbook
     * Method: get sss
     * @return  array()  Response
     */
    public function editAdvice($id)
    {
        $data['title'] = 'Edit Demand Advice';
        $data['expenseCategories'] = AccountHeads::select('id', 'sub_head')->where('parent_id', 4)->where('status', 0)->get();
        $getBranchId = getUserBranchId(Auth::user()->id);
        $data['expenseSubCategories'] = AccountHeads::select('id', 'sub_head', 'parent_id')->whereIn('parent_id', array(14, 86))->whereNotIn('id', array(37, 40, 53, 87, 88, 92))->where('status', 0)->get();
        $data['liabilityHeads'] = array('');
        $data['company']  = \App\Models\CompanyBranch::select('company_id', 'branch_id')->where('branch_id',  $getBranchId->id)->get();
        $data['rentOwners'] = RentLiability::select('id', 'owner_name')->where('status', 0)->get();
        $data['subCategory1'] = AccountHeads::where('parent_id', 86)->get();
        $data['branch_id'] = $getBranchId->id;
        $subCategory2 = array();
        $i = 0;
        foreach ($data['subCategory1'] as $value1) {
            $record1 = AccountHeads::where('parent_id', $value1->head_id)->get();
            foreach ($record1 as $value2) {
                $record2 = AccountHeads::where('head_id', $value2->head_id)->first();
                if ($record2) {
                    $subCategory2[$i] = $record2;
                    $i++;
                }
            }
        }
        $data['subCategory2'] = $subCategory2;
        $subCategory3 = array();
        foreach ($subCategory2 as $value3) {
            $record3 = AccountHeads::where('parent_id', $value3->head_id)->get();
            foreach ($record3 as $value4) {
                $record4 = AccountHeads::where('head_id', $value4->head_id)->first();
                if ($record4) {
                    $subCategory3[$i] = $record4;
                    $i++;
                }
            }
        }
        $data['subCategory3'] = $subCategory3;
        $data['demandAdvice'] = DemandAdvice::with('expenses', 'branch')->where('id', $id)->first();
        $data['investmentDetails'] = Memberinvestments::with('plan', 'member', 'ssb', 'memberBankDetail')->where('id', $data['demandAdvice']->investment_id)->first();
        return view('templates.branch.demand-advice.edit-demand-advice', $data);
    }
    /**
     * Edit user View.
     * Route: /member/passbook
     * Method: get sss
     * @return  array()  Response
     */
    public function adjustTaAdvanced($id)
    {
        $data['title'] = 'Edit Demand Advice';
        $data['expenseCategories'] = AccountHeads::select('id', 'sub_head')->where('id', 86)->where('status', 0)->get();
        $data['expenseSubCategories'] = AccountHeads::select('id', 'sub_head', 'parent_id')->whereIn('parent_id', array(86))->whereNotIn('id', array(37, 40, 53, 87, 88, 92))->where('status', 0)->get();
        $data['demandAdvice'] = DemandAdvice::with('expenses', 'branch')->where('id', $id)->first();
        $data['cBanks'] = SamraddhBank::with('bankAccount')->where("status", "1")->get();
        $data['cheques'] = SamraddhCheque::select('cheque_no', 'account_id')->where('status', 1)->get();
        $getBranchId = getUserBranchId(Auth::user()->id);
        $data['branch_id'] = $getBranchId->id;
        return view('templates.branch.demand-advice.adjust_ta_advanced', $data);
    }
    /**
     * Update the specified demand advice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateAdvice(Request $request)
    {
        $rules = [
            'paymentType' => 'required',
            // 'branch' => 'required',
            //'date' => 'required',
            //'expenseType' => 'required',
        ];
        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];
        $this->validate($request, $rules, $customMessages);
        //echo "<pre>"; print_r($request->all()); die;
        DB::beginTransaction();
        try {
            if ($request->paymentType == 0 && $request->expenseType == 0) {
                $daData = [
                    'payment_type' => $request->paymentType,
                    'sub_payment_type' => $request->expenseType,
                    'branch_id' => $request->branch,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->date))),
                    'updated_at' => $request->created_at,
                ];
                $demandAdvice = DemandAdvice::find($request->demand_advice_id);
                $demandAdvice->update($daData);
                foreach ($request->fresh_expense as $key => $value) {
                    if ($value['id'] == '') {
                        if (isset($value['bill_photo'])) {
                            // $mainFolder = storage_path() . '/images/demand-advice/expense';
                            $mainFolder = 'demand-advice/expense';
                            $file = $value['bill_photo'];
                            $uploadFile = $file->getClientOriginalName();
                            $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                            $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                            ImageUpload::upload($file, $mainFolder,$fname);
                            
                            $fData = [
                                'file_name' => $fname,
                                'file_path' => $mainFolder,
                                'file_extension' => $file->getClientOriginalExtension(),
                            ];
                            $res = Files::create($fData);
                            $file_id = $res->id;
                        } else {
                            $file_id = NULL;
                        }
                        $feData = [
                            'demand_advice_id' => $request->demand_advice_id,
                            'category' => $value['expenseCategory'],
                            //'subcategory' => $value['expenseSubCategory'],
                            'subcategory1' => $value['expenseSubCategory1'],
                            'subcategory2' => $value['expenseSubCategory2'],
                            'subcategory3' => $value['expenseSubCategory3'],
                            'party_name' => $value['party_name'],
                            'particular' => $value['particular'],
                            'mobile_number' => $value['mobile_number'],
                            'amount' => $value['amount'],
                            'bill_number' => $value['billNumber'],
                            'bill_file_id' => $file_id,
                            'created_at' => $request->created_at,
                            'company_id' => $request->company_id,
                        ];
                        $demandAdvice = DemandAdviceExpense::create($feData);
                    } else {
                        if (isset($value['bill_photo']) && isset($value['file_id'])) {
                            $hiddenFileId = $value['file_id'];
                            // $mainFolder = storage_path() . '/images/demand-advice/expense';
                            $mainFolder = 'demand-advice/expense';
                            $file = $value['bill_photo'];
                            $uploadFile = $file->getClientOriginalName();
                            $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                            $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                            ImageUpload::upload($file, $mainFolder,$fname);
                            
                            $data = [
                                'file_name' => $fname,
                                'file_path' => $mainFolder,
                                'file_extension' => $file->getClientOriginalExtension(),
                            ];
                            $fileRes = Files::find($hiddenFileId);
                            $fileRes->update($data);
                            $feData = [
                                'category' => $value['expenseCategory'],
                                //'subcategory' => $value['expenseSubCategory'],
                                'subcategory1' => $value['expenseSubCategory1'],
                                'subcategory2' => $value['expenseSubCategory2'],
                                'subcategory3' => $value['expenseSubCategory3'],
                                'party_name' => $value['party_name'],
                                'particular' => $value['particular'],
                                'mobile_number' => $value['mobile_number'],
                                'amount' => $value['amount'],
                                'bill_number' => $value['billNumber'],
                            ];
                        } elseif (isset($value['bill_photo'])) {
                            $mainFolder = 'demand-advice/expense';
                            $file = $value['bill_photo'];
                            $uploadFile = $file->getClientOriginalName();
                            $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                            $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                            ImageUpload::upload($file, $mainFolder,$fname);
                            
                            $fData = [
                                'file_name' => $fname,
                                'file_path' => $mainFolder,
                                'file_extension' => $file->getClientOriginalExtension(),
                            ];
                            $res = Files::create($fData);
                            $file_id = $res->id;
                            $feData = [
                                'category' => $value['expenseCategory'],
                                //'subcategory' => $value['expenseSubCategory'],
                                'subcategory1' => $value['expenseSubCategory1'],
                                'subcategory2' => $value['expenseSubCategory2'],
                                'subcategory3' => $value['expenseSubCategory3'],
                                'party_name' => $value['party_name'],
                                'particular' => $value['particular'],
                                'mobile_number' => $value['mobile_number'],
                                'amount' => $value['amount'],
                                'bill_file_id' => $file_id,
                                'bill_number' => $value['billNumber'],
                            ];
                        } else {
                            $feData = [
                                'category' => $value['expenseCategory'],
                                //'subcategory' => $value['expenseSubCategory'],
                                'subcategory1' => $value['expenseSubCategory1'],
                                'subcategory2' => $value['expenseSubCategory2'],
                                'subcategory3' => $value['expenseSubCategory3'],
                                'party_name' => $value['party_name'],
                                'particular' => $value['particular'],
                                'mobile_number' => $value['mobile_number'],
                                'amount' => $value['amount'],
                                'bill_number' => $value['billNumber'],
                            ];
                        }
                        $DemandAdviceExpense = DemandAdviceExpense::find($value['id']);
                        $DemandAdviceExpense->update($feData);
                    }
                }
            } elseif ($request->paymentType == 0 && $request->expenseType == 1) {
                $daData = [
                    'payment_type' => $request->paymentType,
                    'sub_payment_type' => $request->expenseType,
                    'branch_id' => $request->branch,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->date))),
                    'employee_id' => $request->ta_employee_id,
                    'employee_name' => $request->ta_employee_name,
                    'particular' => $request->ta_particular,
                    'advanced_amount' => $request->ta_advance_amount,
                    'updated_at' => $request->created_at,
                    'company_id' => $request->company_id,
                ];
                $demandAdvice = DemandAdvice::find($request->demand_advice_id);
                $demandAdvice->update($daData);
            } elseif ($request->paymentType == 0 && $request->expenseType == 2) {
                if (isset($request->advanced_salary_letter_photo) && $request->old_advanced_salary_letter_photo == '') {
                    $mainFolder = 'demand-advice/advancedsalary';
                    $file = $request->advanced_salary_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    ImageUpload::upload($file, $mainFolder,$fname);
                    
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $res = Files::create($fData);
                    $file_id = $res->id;
                } elseif (isset($request->advanced_salary_letter_photo) && $request->old_advanced_salary_letter_photo != '') {
                    $hiddenFileId = $request->old_advanced_salary_letter_photo;
                    // $mainFolder = storage_path() . '/images/demand-advice/advancedsalary';
                    $mainFolder ='demand-advice/advancedsalary';
                    $file = $request->advanced_salary_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    ImageUpload::upload($file, $mainFolder,$fname);
                    
                    $data = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $fileRes = Files::find($hiddenFileId);
                    $fileRes->update($data);
                    $file_id = $request->old_advanced_salary_letter_photo;
                } elseif ($request->advanced_salary_letter_photo == '') {
                    $file_id = $request->old_advanced_salary_letter_photo;
                }
                $daData = [
                    'payment_type' => $request->paymentType,
                    'sub_payment_type' => $request->expenseType,
                    'branch_id' => $request->branch,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->date))),
                    'employee_id' => $request->advanced_salary_employee_id,
                    'employee_name' => $request->advanced_salary_employee_name,
                    'mobile_number' => $request->advanced_salary_mobile_number,
                    'amount' => $request->advanced_salary_amount,
                    'letter_photo_id' => $file_id,
                    'narration' => $request->advanced_salary_narration,
                    'ssb_account' => $request->advanced_salary_ssb_account,
                    'bank_name' => $request->advanced_salary_bank_name,
                    'bank_account_number' => $request->advanced_salary_bank_account_number,
                    'bank_ifsc' => $request->advanced_salary_ifsc_code,
                    'updated_at' => $request->created_at,
                    'company_id' => $request->company_id,
                ];
                $demandAdvice = DemandAdvice::find($request->demand_advice_id);
                $demandAdvice->update($daData);
            } elseif ($request->paymentType == 0 && $request->expenseType == 3) {
                $daData = [
                    'payment_type' => $request->paymentType,
                    'sub_payment_type' => $request->expenseType,
                    'branch_id' => $request->branch,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->date))),
                    'employee_id' => $request->advanced_rent_employee_id,
                    'employee_name' => $request->advanced_rent_employee_name,
                    'owner_name' => $request->advanced_rent_party_name,
                    'mobile_number' => $request->advanced_rent_mobile_number,
                    'amount' => $request->advanced_rent_amount,
                    'narration' => $request->advanced_rent_narration,
                    'ssb_account' => $request->advanced_rent_ssb_account,
                    'bank_name' => $request->advanced_rent_bank_name,
                    'bank_account_number' => $request->advanced_rent_bank_account_number,
                    'bank_ifsc' => $request->advanced_rent_ifsc_code,
                    'updated_at' => $request->created_at,
                    'company_id' => $request->company_id,
                ];
                $demandAdvice = DemandAdvice::find($request->demand_advice_id);
                $demandAdvice->update($daData);
            } elseif ($request->paymentType == 2 && $request->maturity_prematurity_type == 0) {
                if (isset($request->maturity_letter_photo) && $request->old_maturity_letter_photo == '') {
                    // $mainFolder = storage_path() . '/images/demand-advice/maturity-prematurity';
                    $mainFolder ='demand-advice/maturity-prematurity';
                    $file = $request->maturity_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    ImageUpload::upload($file, $mainFolder,$fname);
                    
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $res = Files::create($fData);
                    $file_id = $res->id;
                } elseif (isset($request->maturity_letter_photo) && $request->old_maturity_letter_photo != '') {
                    $hiddenFileId = $request->old_maturity_letter_photo;
                    // $mainFolder = storage_path() . '/images/demand-advice/maturity-prematurity';
                    $mainFolder = 'demand-advice/maturity-prematurity';
                    $file = $request->maturity_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    ImageUpload::upload($file, $mainFolder,$fname);
                    
                    $data = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $fileRes = Files::find($hiddenFileId);
                    $fileRes->update($data);
                    $file_id = $request->old_maturity_letter_photo;
                } elseif ($request->maturity_letter_photo == '') {
                    $file_id = $request->old_maturity_letter_photo;
                }
                $daData = [
                    'payment_type' => 1,
                    'branch_id' => $request->branch,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->maturity_prematurity_date))),
                    'letter_photo_id' => $file_id,
                    //'investment_id' => $request->maturity_investmnet_id,
                    'account_number' => $request->maturity_account_number,
                    'opening_date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->maturity_opening_date))),
                    'plan_name' => $request->maturity_plan_name,
                    'tenure' => $request->maturity_tenure,
                    'account_holder_name' => $request->maturity_account_holder_name,
                    'father_name' => $request->maturity_father_name,
                    'maturity_prematurity_category' => $request->maturity_category,
                    'maturity_prematurity_amount' => $request->maturity_amount,
                    'mobile_number' => $request->maturity_mobile_number,
                    'ssb_account' => $request->maturity_ssb_account,
                    'bank_account_number' => $request->maturity_bank_account_number,
                    'bank_ifsc' => $request->maturity_ifsc_code,
                    'updated_at' => $request->created_at,
                    'company_id' => $request->company_id,
                ];
                if (isset($request->payment_mode) && $request->payment_mode != "") {
                    $daData["maturity_payment_mode"] = $request->payment_mode;
                }
                $demandAdvice = DemandAdvice::find($request->demand_advice_id);
                $demandAdvice->update($daData);
            } elseif ($request->paymentType == 2 && $request->maturity_prematurity_type == 1) {
                if (isset($request->prematurity_letter_photo) && $request->old_prematurity_letter_photo == '') {
                    $mainFolder = 'demand-advice/maturity-prematurity';
                    $file = $request->prematurity_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    ImageUpload::upload($file, $mainFolder,$fname);
                    
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $res = Files::create($fData);
                    $file_id = $res->id;
                } elseif (isset($request->prematurity_letter_photo) && $request->old_prematurity_letter_photo != '') {
                    $hiddenFileId = $request->old_prematurity_letter_photo;
                    $mainFolder = 'demand-advice/maturity-prematurity';
                    $file = $request->prematurity_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    ImageUpload::upload($file, $mainFolder,$fname);
                    
                    $data = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $fileRes = Files::find($hiddenFileId);
                    $fileRes->update($data);
                    $file_id = $request->old_prematurity_letter_photo;
                } elseif ($request->prematurity_letter_photo == '') {
                    $file_id = $request->old_prematurity_letter_photo;
                }
                $daData = [
                    'payment_type' => 2,
                    'branch_id' => $request->branch,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->maturity_prematurity_date))),
                    'letter_photo_id' => $file_id,
                  //  'investment_id' => $request->prematurity_investmnet_id,
                    'account_number' => $request->prematurity_account_number,
                    'opening_date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->prematurity_opening_date))),
                    'plan_name' => $request->prematurity_plan_name,
                    'tenure' => $request->prematurity_tenure,
                    'account_holder_name' => $request->prematurity_account_holder_name,
                    'father_name' => $request->prematurity_father_name,
                    'maturity_prematurity_category' => $request->prematurity_category,
                    'maturity_prematurity_amount' => $request->prematurity_amount,
                    'mobile_number' => $request->prematurity_mobile_number,
                    'ssb_account' => $request->prematurity_ssb_account,
                    'bank_account_number' => $request->prematurity_bank_account_number,
                    'bank_ifsc' => $request->prematurity_ifsc_code,
                    'updated_at' => $request->created_at,
                    'company_id' => $request->company_id,
                ];
                if (isset($request->payment_mode) && $request->payment_mode != "") {
                    $daData["maturity_payment_mode"] = $request->payment_mode;
                }
                $demandAdvice = DemandAdvice::find($request->demand_advice_id);
                $demandAdvice->update($daData);
            } elseif ($request->paymentType == 4) {
                if (isset($request->death_help_letter_photo) && $request->old_death_help_letter_photo == '') {
                    $mainFolder = 'demand-advice/death-help';
                    $file = $request->death_help_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    ImageUpload::upload($file, $mainFolder,$fname);
                    
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $res = Files::create($fData);
                    $file_id = $res->id;
                } elseif (isset($request->death_help_letter_photo) && $request->old_death_help_letter_photo != '') {
                    $hiddenFileId = $request->old_death_help_letter_photo;
                    $mainFolder = 'demand-advice/death-help';
                    $file = $request->death_help_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    ImageUpload::upload($file, $mainFolder,$fname);
                    
                    $data = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $fileRes = Files::find($hiddenFileId);
                    $fileRes->update($data);
                    $file_id = $request->old_death_help_letter_photo;
                } elseif ($request->death_help_letter_photo == '') {
                    $file_id = $request->old_death_help_letter_photo;
                }
                if ($request->death_help_category == 0) {
                    $subType = 4;
                } elseif ($request->death_help_category == 1) {
                    $subType = 5;
                }
                $daData = [
                    'payment_type' => 3,
                    'sub_payment_type' => $subType,
                    'branch_id' => $request->branch,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->death_help_date))),
                    'death_certificate_id' => $file_id,
                    'investment_id' => $request->death_help_investmnet_id,
                    'account_number' => $request->death_help_account_number,
                    'opening_date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->death_help_opening_date))),
                    'plan_name' => $request->death_help_plan_name,
                    'tenure' => $request->death_help_tenure,
                    'account_holder_name' => $request->death_help_account_holder_name,
                    'deno' => $request->death_help_deno,
                    'deposited_amount' => $request->death_help_deposited_amount,
                    //'death_claim_amount' => $request->death_help_death_claim_amount,
                    'nominee_name' => $request->death_help_nominee_name,
                    'naominee_member_id' => $request->death_help_nominee_member_id,
                    'mobile_number' => $request->death_help_mobile_number,
                    'ssb_account' => $request->death_help_ssb_account,
                    'updated_at' => $request->created_at,
                    'company_id' => $request->company_id,
                ];
                if (isset($request->payment_mode) && $request->payment_mode != "") {
                    $daData["maturity_payment_mode"] = $request->payment_mode;
                }
                $demandAdvice = DemandAdvice::find($request->demand_advice_id);
                $demandAdvice->update($daData);
            }
            $this->reDemand_advice($request->demand_advice_id);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        if ($request->demand_advice_id) {
            return redirect()->route('branch.demand.application')->with('success', 'Update was Successful!');
        } else {
            return back()->with('alert', 'An error occured');
        }
    }
    /**
     * Save Demand Advice.
     * Route: /save-account-head
     * Method: get
     * @return  array()  Response
     */
    public function updateTaAdvanced(Request $request)
    {
        DB::beginTransaction();
        try {
            $entryTime = date("H:i:s");
            Session::put('created_at', date("Y-m-d", strtotime(convertDate($request->payment_date))));
            $request['created_at'] = date("Y-m-d", strtotime(convertDate($request->payment_date)));
            foreach ($request->ta_expense as $key => $value) {
                if (isset($value['bill_photo'])) {
                    // $mainFolder = storage_path() . '/images/demand-advice/expense';
                    $mainFolder = 'demand-advice/expense';
                    $file = $value['bill_photo'];
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    ImageUpload::upload($file, $mainFolder,$fname);
                    
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $res = Files::create($fData);
                    $file_id = $res->id;
                } else {
                    $file_id = NULL;
                }
                $feData = [
                    'demand_advice_id' => $request->demand_advice_id,
                    'category' => $value['expenseCategory'],
                    'subcategory' => $value['expenseSubCategory'],
                    'amount' => $value['amount'],
                    'bill_number' => $value['billNumber'],
                    'bill_file_id' => $file_id,
                    'status' => 0,
                    'created_at' => date("Y-m-d", strtotime(convertDate($request->created_at))),
                ];
                $demandAdvice = DemandAdviceExpense::create($feData);
            }
            $response = DemandAdvice::where('id', $request->demand_advice_id)->update(['ta_advanced_adjustment' => 0]);
            $demandAdviceTaAdvanced = DemandAdvice::with('employee')->where('id', $request->demand_advice_id)->first();
            $taAdvanced = DemandAdviceExpense::where('demand_advice_id', $request->demand_advice_id)->get();
            $sumAmount = DemandAdviceExpense::where('demand_advice_id', $request->demand_advice_id)->sum('amount');
            $request['branch_id'] = $demandAdviceTaAdvanced->branch_id;
            $employeeAdvancedSalary = $demandAdviceTaAdvanced['employee']->advance_payment - $demandAdviceTaAdvanced->advanced_amount;
            $employeeCurrentBalance = $demandAdviceTaAdvanced['employee']->current_balance - $demandAdviceTaAdvanced->advanced_amount;
            $advancedSalaryUpdate = Employee::where('id', $demandAdviceTaAdvanced->employee_id)->update(['advance_payment' => $employeeAdvancedSalary, 'current_balance' => $employeeCurrentBalance]);
            $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id', 'balance', 'branch_id', 'branch_code', 'member_id', 'account_no')->where('account_no', $demandAdviceTaAdvanced['employee']->ssb_account)->first();
            if ($request->amount_mode == 2) {
                if ($request->mode == 3) {
                    SamraddhCheque::where('cheque_no', $request->cheque_number)->update(['status' => 2, 'is_use' => 1]);
                    SamraddhChequeIssue::create([
                        'cheque_id' => getSamraddhChequeData($request->cheque_number)->id,
                        'type' => 6,
                        'sub_type' => 62,
                        'type_id' => $request->demand_advice_id,
                        'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request->created_at))),
                        'status' => 1,
                    ]);
                }
            }
            if ($request->amount_mode == 1 && $ssbAccountDetails == '') {
                array_push($ssbArray, $value);
            } else {
                if ($request->amount_mode == 0 && $request->amount_mode != '') {
                    $branch_id = $request->branch_id;
                    $type = 13;
                    $sub_type = 132;
                    $jv_unique_id = NULL;
                    if ($ssbAccountDetails) {
                        $member_id = $ssbAccountDetails['ssbMember']->id;
                        $amount_to_id = $ssbAccountDetails['ssbMember']->id;
                        $amount_to_name = $ssbAccountDetails['ssbMember']->first_name . ' ' . $ssbAccountDetails['ssbMember']->last_name;
                    } else {
                        $member_id = $demandAdviceTaAdvanced['employee']->id;
                        $amount_to_id = $demandAdviceTaAdvanced['employee']->id;
                        $amount_to_name = $demandAdviceTaAdvanced['employee']->employee_name;
                    }
                    $type_id = $demandAdviceTaAdvanced->id;
                    $type_transaction_id = $request->demand_advice_id;
                    $associate_id = NULL;
                    $branch_id_to = NULL;
                    $branch_id_from = $request->branch_id;
                    if ($request->adjustment_level == 0) {
                        $taAdvancedAmount = $demandAdviceTaAdvanced->advanced_amount;
                        $taAmount = $demandAdviceTaAdvanced->advanced_amount;
                        $opening_balance = $demandAdviceTaAdvanced->advanced_amount;
                        $amount = $demandAdviceTaAdvanced->advanced_amount;
                        $closing_balance = $demandAdviceTaAdvanced->advanced_amount;
                        $description = getAcountHeadNameHeadId($value['expenseSubCategory']) . ' A/C Dr ' . $amount . ' - To Cash A/C Cr ' . $amount;
                        $description_dr = getAcountHeadNameHeadId($value['expenseSubCategory']) . ' A/C Dr ' . $amount;
                        $description_cr = 'To Cash A/C Cr ' . $amount;
                        $payment_type = 'DR';
                    } elseif ($request->adjustment_level == 1) {
                        $taAdvancedAmount = $demandAdviceTaAdvanced->advanced_amount;
                        $taAmount = $sumAmount;
                        $opening_balance = $sumAmount - $demandAdviceTaAdvanced->advanced_amount;
                        $amount = $sumAmount - $demandAdviceTaAdvanced->advanced_amount;
                        $closing_balance = $sumAmount - $demandAdviceTaAdvanced->advanced_amount;
                        $description = getAcountHeadNameHeadId($value['expenseSubCategory']) . ' A/C Dr ' . $amount . ' - To Cash A/C Cr ' . $amount;
                        $description_dr = getAcountHeadNameHeadId($value['expenseSubCategory']) . ' A/C Dr ' . $amount;
                        $description_cr = 'To Cash A/C Cr ' . $amount;
                        $payment_type = 'DR';
                    } elseif ($request->adjustment_level == 2) {
                        $taAdvancedAmount = $demandAdviceTaAdvanced->advanced_amount;
                        $taAmount = $sumAmount;
                        $opening_balance = $demandAdviceTaAdvanced->advanced_amount - $sumAmount;
                        $amount = $demandAdviceTaAdvanced->advanced_amount - $sumAmount;
                        $closing_balance = $demandAdviceTaAdvanced->advanced_amount - $sumAmount;
                        $description = getAcountHeadNameHeadId($value['expenseSubCategory']) . ' A/C Dr ' . $sumAmount . ' - To Cash A/C Cr ' . $sumAmount;
                        $description_dr = getAcountHeadNameHeadId($value['expenseSubCategory']) . ' A/C Dr ' . $sumAmount;
                        $description_cr = 'To Cash A/C Cr ' . $sumAmount;
                        $payment_type = 'CR';
                    }
                    $payment_mode = 0;
                    $currency_code = 'INR';
                    $amount_from_id = $request->branch_id;
                    $amount_from_name = getBranchDetail($demandAdviceTaAdvanced->branch_id)->name;
                    $v_no = NULL;
                    $v_date = NULL;
                    $ssb_account_id_from = NULL;
                    $ssb_account_id_to = NULL;
                    $cheque_no = NULL;
                    $cheque_type = NULL;
                    $cheque_date = NULL;
                    $cheque_id = NULL;
                    $cheque_bank_to_name = NULL;
                    $cheque_bank_to_branch = NULL;
                    $cheque_bank_to_ac_no = NULL;
                    $cheque_bank_from = NULL;
                    $cheque_bank_from_id = NULL;
                    $cheque_bank_ac_from_id = NULL;
                    $cheque_bank_ac_from = NULL;
                    $cheque_bank_ifsc_from = NULL;
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = NULL;
                    $cheque_bank_ac_to = NULL;
                    $cheque_bank_to_ifsc = NULL;
                    $transction_no = NULL;
                    $transction_bank_from = NULL;
                    $transction_bank_from_id = NULL;
                    $transction_bank_branch_from = NULL;
                    $transction_bank_to = NULL;
                    $transction_bank_ac_to = NULL;
                    $transction_bank_ac_from = NULL;
                    $transction_bank_ifsc_from = NULL;
                    $transction_date = NULL;
                    $entry_date = NULL;
                    $entry_time = NULL;
                    $created_by = 1;
                    $created_by_id = Auth::user()->id;
                    $is_contra = NULL;
                    $contra_id = NULL;
                    $created_at = NULL;
                    $bank_id = NULL;
                    $bank_ac_id = NULL;
                    $transction_bank_to_name = NULL;
                    $transction_bank_to_ac_no = NULL;
                    $transction_bank_to_branch = NULL;
                    $transction_bank_to_ifsc = NULL;
                    $ssb_account_id_to = NULL;
                    $to_bank_name = NULL;
                    $to_bank_branch = NULL;
                    $to_bank_ac_no = NULL;
                    $to_bank_ifsc = NULL;
                    $to_bank_id = NULL;
                    $to_bank_account_id = NULL;
                    $from_bank_name = NULL;
                    $from_bank_branch = NULL;
                    $from_bank_ac_no = NULL;
                    $from_bank_ifsc = NULL;
                    $from_bank_id = NULL;
                    $from_bank_ac_id = NULL;
                    $transaction_date = NULL;
                    $transaction_charge = NULL;
                    $cheque_id = NULL;
                } elseif ($request->amount_mode == 1 || $request->amount_mode == '') {
                    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                    $vno = "";
                    for ($i = 0; $i < 10; $i++) {
                        $vno .= $chars[mt_rand(0, strlen($chars) - 1)];
                    }
                    $branch_id = $demandAdviceTaAdvanced['employee']->branch_id;
                    $type = 13;
                    $sub_type = 132;
                    if ($ssbAccountDetails) {
                        $member_id = $ssbAccountDetails['ssbMember']->id;
                        $amount_to_id = $ssbAccountDetails['ssbMember']->id;
                        $amount_to_name = $ssbAccountDetails['ssbMember']->first_name . ' ' . $ssbAccountDetails['ssbMember']->last_name;
                    } else {
                        $member_id = $demandAdviceTaAdvanced['employee']->id;
                        $amount_to_id = $demandAdviceTaAdvanced['employee']->id;
                        $amount_to_name = $demandAdviceTaAdvanced['employee']->employee_name;
                    }
                    $type_id = $demandAdviceTaAdvanced->id;
                    $type_transaction_id = $demandAdviceTaAdvanced->id;
                    $associate_id = NULL;
                    $branch_id_to = NULL;
                    $branch_id_from = NULL;
                    if ($request->adjustment_level == 0) {
                        $taAdvancedAmount = $demandAdviceTaAdvanced->advanced_amount;
                        $taAmount = $demandAdviceTaAdvanced->advanced_amount;
                        $opening_balance = $demandAdviceTaAdvanced->advanced_amount;
                        $amount = $demandAdviceTaAdvanced->advanced_amount;
                        $closing_balance = $demandAdviceTaAdvanced->advanced_amount;
                        $description = getAcountHeadNameHeadId($value['expenseSubCategory']) . ' A/C Dr ' . $amount . ' - To ' . $demandAdviceTaAdvanced->employee_name . ' Cr ' . $amount;
                        $description_dr = getAcountHeadNameHeadId($value['expenseSubCategory']) . ' A/C Dr ' . $amount;
                        $description_cr = 'To ' . $demandAdviceTaAdvanced->employee_name . ' Cr ' . $amount;
                    } elseif ($request->adjustment_level == 1) {
                        $taAdvancedAmount = $demandAdviceTaAdvanced->advanced_amount;
                        $taAmount = $sumAmount;
                        $opening_balance = $sumAmount - $demandAdviceTaAdvanced->advanced_amount;
                        $amount = $sumAmount - $demandAdviceTaAdvanced->advanced_amount;
                        $closing_balance = $sumAmount - $demandAdviceTaAdvanced->advanced_amount;
                        $description = getAcountHeadNameHeadId($value['expenseSubCategory']) . ' A/C Dr ' . $sumAmount . ' - ' . $demandAdviceTaAdvanced['employee']->ssb_account . ' A/C Cr ' . $sumAmount;
                        $description_dr = getAcountHeadNameHeadId($value['expenseSubCategory']) . ' A/C Dr ' . $sumAmount;
                        $description_cr = $demandAdviceTaAdvanced['employee']->ssb_account . ' A/C Cr ' . $sumAmount;
                    }
                    $jv_unique_id = NULL;
                    $payment_type = 'CR';
                    $payment_mode = 3;
                    $currency_code = 'INR';
                    $amount_from_id = NULL;
                    $amount_from_name = NULL;
                    $v_no = $vno;
                    $v_date = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request->created_at)));
                    $ssb_account_id_from = NULL;
                    $cheque_type = NULL;
                    $cheque_id = NULL;
                    $cheque_no = NULL;
                    $cheque_date = NULL;
                    $cheque_bank_from = NULL;
                    $cheque_bank_to_name = NULL;
                    $cheque_bank_to_branch = NULL;
                    $cheque_bank_from_id = NULL;
                    $cheque_bank_ac_from_id = NULL;
                    $cheque_bank_to_ac_no = NULL;
                    $cheque_bank_to_ifsc = NULL;
                    $transction_bank_from_id = NULL;
                    $transction_bank_from_ac_id = NULL;
                    $cheque_bank_ac_from = NULL;
                    $cheque_bank_ifsc_from = NULL;
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = NULL;
                    $cheque_bank_ac_to = NULL;
                    $transction_no = NULL;
                    $transction_bank_from = NULL;
                    $transction_bank_ac_from = NULL;
                    $transction_bank_ifsc_from = NULL;
                    $transction_bank_branch_from = NULL;
                    $transction_bank_to = NULL;
                    $transction_bank_ac_to = NULL;
                    $transction_date = NULL;
                    $entry_date = NULL;
                    $entry_time = NULL;
                    $created_by = 1;
                    $created_by_id = Auth::user()->id;
                    $is_contra = NULL;
                    $contra_id = NULL;
                    $created_at = NULL;
                    $bank_id = NULL;
                    $bank_ac_id = NULL;
                    $transction_bank_to_name = NULL;
                    $transction_bank_to_ac_no = NULL;
                    $transction_bank_to_branch = NULL;
                    $transction_bank_to_ifsc = NULL;
                    $ssb_account_id_to = $demandAdviceTaAdvanced['employee']->ssb_id;
                    $to_bank_name = NULL;
                    $to_bank_branch = NULL;
                    $to_bank_ac_no = NULL;
                    $to_bank_ifsc = NULL;
                    $to_bank_id = NULL;
                    $to_bank_account_id = NULL;
                    $from_bank_name = NULL;
                    $from_bank_branch = NULL;
                    $from_bank_ac_no = NULL;
                    $from_bank_ifsc = NULL;
                    $from_bank_id = NULL;
                    $from_bank_ac_id = NULL;
                    $transaction_date = NULL;
                    $transaction_charge = NULL;
                    $cheque_id = NULL;
                } elseif ($request->amount_mode == 2 && $request->amount_mode != '') {
                    $branch_id = $demandAdviceTaAdvanced['employee']->branch_id;
                    $type = 13;
                    $sub_type = 132;
                    $type_id = $demandAdviceTaAdvanced->id;
                    $type_transaction_id = $request->demand_advice_id;
                    $associate_id = NULL;
                    if ($ssbAccountDetails) {
                        $member_id = $ssbAccountDetails['ssbMember']->id;
                        $amount_to_id = $ssbAccountDetails['ssbMember']->id;
                        $amount_to_name = $ssbAccountDetails['ssbMember']->first_name . ' ' . $ssbAccountDetails['ssbMember']->last_name;
                    } else {
                        $member_id = $demandAdviceTaAdvanced['employee']->id;
                        $amount_to_id = $demandAdviceTaAdvanced['employee']->id;
                        $amount_to_name = $demandAdviceTaAdvanced['employee']->employee_name;
                    }
                    $branch_id_to = NULL;
                    $branch_id_from = $request->branch_id;
                    if ($request->adjustment_level == 1) {
                        $taAdvancedAmount = $demandAdviceTaAdvanced->advanced_amount;
                        $taAmount = $sumAmount;
                        $opening_balance = $sumAmount - $demandAdviceTaAdvanced->advanced_amount;
                        $amount = $sumAmount - $demandAdviceTaAdvanced->advanced_amount;
                        $closing_balance = $sumAmount - $demandAdviceTaAdvanced->advanced_amount;
                        $description = getAcountHeadNameHeadId($value['expenseSubCategory']) . ' A/C Dr ' . $sumAmount . ' - To Bank A/C Cr ' . $sumAmount;
                        $description_dr = getAcountHeadNameHeadId($value['expenseSubCategory']) . ' A/C Dr ' . $sumAmount;
                        $description_cr = 'To Bank A/C Cr ' . $sumAmount;
                        $payment_type = 'DR';
                    }
                    $jv_unique_id = NULL;
                    $payment_type = 'DR';
                    $currency_code = 'INR';
                    $amount_from_id = $request->branch_id;
                    $amount_from_name = getBranchDetail($demandAdviceTaAdvanced->branch_id)->name;
                    $v_no = NULL;
                    $v_date = NULL;
                    $ssb_account_id_from = NULL;
                    if ($request->mode == 3) {
                        $cheque_type = 1;
                        $cheque_id = getSamraddhChequeData($request->cheque_number)->id;
                        $cheque_no = $request->cheque_number;
                        $cheque_date = getSamraddhChequeData($request->cheque_number)->cheque_create_date;
                        $cheque_bank_from = $request->bank;
                        $cheque_bank_from_id = $request->bank;
                        $cheque_bank_ac_from = $request->bank_account_number;
                        $cheque_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;
                        $cheque_bank_ac_from_id = getSamraddhBankAccount($request->bank_account_number)->id;
                        $cheque_bank_branch_from = NULL;
                        $cheque_bank_to = NULL;
                        $cheque_bank_ac_to = NULL;
                        $cheque_bank_to_name = NULL;
                        $cheque_bank_to_branch = NULL;
                        $cheque_bank_to_ac_no = NULL;
                        $cheque_bank_to_ifsc = NULL;
                        $transction_no = NULL;
                        $transction_bank_from = getSamraddhBank($request->bank)->bank_name;
                        $transction_bank_from_id = getSamraddhBank($request->bank)->id;
                        $transction_bank_ac_from = $request->bank_account_number;
                        $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;
                        $transction_bank_from_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;
                        $transction_bank_branch_from = NULL;
                        $transction_bank_to = NULL;
                        $transction_bank_ac_to = NULL;
                        $payment_mode = 1;
                        $transction_bank_to_name = NULL;
                        $transction_bank_to_ac_no = NULL;
                        $transction_bank_to_branch = NULL;
                        $transction_bank_to_ifsc = NULL;
                        $transaction_charge = NULL;
                        SamraddhCheque::where('cheque_no', $request->cheque_number)->update(['status' => 2, 'is_use' => 1]);
                        SamraddhChequeIssue::create([
                            'cheque_id' => getSamraddhChequeData($request->cheque_number)->id,
                            'type' => 6,
                            'sub_type' => 62,
                            'type_id' => $demandAdviceTaAdvanced->id,
                            'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request->created_at))),
                            'status' => 1,
                        ]);
                    } elseif ($request->mode == 4) {
                        $cheque_id = NULL;
                        $cheque_type = NULL;
                        $cheque_no = NULL;
                        $cheque_date = NULL;
                        $cheque_bank_from = NULL;
                        $cheque_bank_from_id = NULL;
                        $cheque_bank_ac_from = NULL;
                        $cheque_bank_ifsc_from = NULL;
                        $cheque_bank_branch_from = NULL;
                        $cheque_bank_to = NULL;
                        $cheque_bank_ac_to = NULL;
                        $cheque_bank_ac_from_id = NULL;
                        $cheque_bank_to_name = NULL;
                        $cheque_bank_to_branch = NULL;
                        $cheque_bank_to_ac_no = NULL;
                        $cheque_bank_to_ifsc = NULL;
                        $transction_no = $request->utr_number;
                        $transction_bank_from = getSamraddhBank($request->bank)->bank_name;
                        $transction_bank_from_id = getSamraddhBank($request->bank)->id;
                        $transction_bank_ac_from = $request->bank_account_number;
                        $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;
                        $transction_bank_from_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;
                        $transction_bank_branch_from = NULL;
                        $transction_bank_to = NULL;
                        $transction_bank_ac_to = NULL;
                        $transction_bank_to_name = NULL;
                        $transction_bank_to_ac_no = NULL;
                        $transction_bank_to_branch = NULL;
                        $transction_bank_to_ifsc = NULL;
                        $payment_mode = 2;
                    }
                    $transction_date = NULL;
                    $entry_date = NULL;
                    $entry_time = NULL;
                    $created_by = 1;
                    $created_by_id = Auth::user()->id;
                    $is_contra = NULL;
                    $contra_id = NULL;
                    $created_at = NULL;
                    $bank_id = NULL;
                    $bank_ac_id = NULL;
                    $bank_id = $request->bank;
                    $bank_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;
                    $ssb_account_id_to = NULL;
                    $to_bank_name = $demandAdviceTaAdvanced['employee']->bank_name;
                    $to_bank_branch = $demandAdviceTaAdvanced['employee']->bank_name;
                    $to_bank_ac_no = $demandAdviceTaAdvanced['employee']->bank_account_no;
                    $to_bank_ifsc = $demandAdviceTaAdvanced['employee']->bank_ifsc_code;
                    $to_bank_id = NULL;
                    $to_bank_account_id = NULL;
                    $from_bank_name = getSamraddhBank($request->bank)->bank_name;
                    $from_bank_branch = getSamraddhBankAccount($request->bank_account_number)->branch_name;
                    $from_bank_ac_no = $request->bank_account_number;
                    $from_bank_ifsc = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;
                    $from_bank_id = $request->bank;
                    $from_bank_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;
                    $transaction_date = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request->created_at)));
                }
                $dayBookRef = CommanTransactionsController::createBranchDayBookReference($amount, $request->created_at);
                $this->employeeSalaryLeaser($demandAdviceTaAdvanced->employee_id, $branch_id, 6, $type_id, $employeeCurrentBalance, NULL, $demandAdviceTaAdvanced->advanced_amount, 'TA Advanced amount A/C Dr ' . $demandAdviceTaAdvanced->advanced_amount . '', $currency_code, 'Dr', $payment_mode, 1, date("Y-m-d " . $entryTime . "", strtotime(convertDate($request->created_at))), $updated_at = NULL, $v_no, $v_date, $ssb_account_id_to, $ssb_account_id_from, $to_bank_name, $to_bank_branch, $to_bank_ac_no, $to_bank_ifsc, $to_bank_id, $to_bank_account_id, $from_bank_name, $from_bank_branch, $from_bank_ac_no, $from_bank_ifsc, $from_bank_id, $from_bank_ac_id, $cheque_id, $cheque_no, $cheque_date, $transction_no, $transaction_date, $transaction_charge);
                $this->employeeLedgerBackDateDR($demandAdviceTaAdvanced->employee_id, $request->created_at, $demandAdviceTaAdvanced->advanced_amount);
                $memberTransaction = CommanTransactionsController::memberTransactionNew($dayBookRef, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $demandAdviceTaAdvanced->advanced_amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                $allHeadTransaction = CommanTransactionsController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 72, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $taAdvancedAmount, $taAdvancedAmount, $taAdvancedAmount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $created_by, $created_by_id);
                /*$allTransaction = CommanTransactionsController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,29,72,$head5=NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$taAdvancedAmount,$taAdvancedAmount,$taAdvancedAmount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/
                if ($request->amount_mode == 0 && ($request->adjustment_level == 1 || $request->adjustment_level == 2)) {
                    if ($payment_type == 'CR') {
                        $pType = 'DR';
                    } else {
                        $pType = 'CR';
                    }
                    $allHeadTransaction = CommanTransactionsController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 28, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $pType, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $created_by, $created_by_id);
                    /*$allTransaction = CommanTransactionsController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/
                    $branchDayBook = CommanTransactionsController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                }
                if ($request->amount_mode == 0 && $request->adjustment_level == 1) {
                    $updateBranchCash = $this->updateBranchCashDr($branch_id, $request->created_at, $amount, 0);
                    $updateBranchClosing = $this->updateBranchClosingCashDr($branch_id, $request->created_at, $amount, 0);
                } elseif ($request->amount_mode == 0 && $request->adjustment_level == 2) {
                    $updateBranchCash = $this->updateBranchCashCr($branch_id, $request->created_at, $amount, 0);
                    $updateBranchClosing = $this->updateBranchClosingCashCr($branch_id, $request->created_at, $amount, 0);
                }
                if ($request->amount_mode == 1 && $request->adjustment_level == 1) {
                    $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id', 'balance', 'branch_id', 'branch_code', 'member_id', 'account_no')->where('account_no', $demandAdviceTaAdvanced['employee']->ssb_account)->first();
                    $paymentMode = 4;
                    $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name . ' ' . $ssbAccountDetails['ssbMember']->last_name;
                    $ssb['saving_account_id'] = $ssbAccountDetails->id;
                    $ssb['account_no'] = $ssbAccountDetails->account_no;
                    if ($request['pay_file_charge'] == 0) {
                        $ssb['opening_balance'] = $amount + $ssbAccountDetails->balance;
                        $ssb['deposit'] = $amount;
                    } else {
                        $ssb['opening_balance'] = $amount + $ssbAccountDetails->balance;
                        $ssb['deposit'] = $amount;
                    }
                    $ssb['branch_id'] = $demandAdviceTaAdvanced['employee']->branch_id;
                    $ssb['type'] = 11;
                    $ssb['withdrawal'] = 0;
                    $ssb['description'] = $description;
                    $ssb['currency_code'] = 'INR';
                    $ssb['payment_type'] = 'CR';
                    $ssb['payment_mode'] = 3;
                    $ssb['created_at'] = date("Y-m-d", strtotime(convertDate($request->created_at)));
                    $ssbAccountTran = SavingAccountTranscation::create($ssb);
                    $saTranctionId = $ssbAccountTran->id;
                    $saToId = $ssbAccountDetails->id;
                    $saToTranctionId = $ssbAccountTran->id;
                    $balance_update = $amount + $ssbAccountDetails->balance;
                    $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                    $ssbBalance->balance = $balance_update;
                    $ssbBalance->save();
                    $data['saving_account_transaction_id'] = $saTranctionId;
                    $data['investment_id'] = $demandAdviceTaAdvanced['employee']->id;
                    $data['created_at'] = date("Y-m-d", strtotime(convertDate($request->created_at)));
                    $satRef = TransactionReferences::create($data);
                    $satRefId = $satRef->id;
                    $amountArraySsb = array('1' => $amount);
                    $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->member_id, $ssbAccountDetails->branch_id, $ssbAccountDetails->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $ssbAccountDetails->member_id, $ssbAccountDetails->account_no, $cheque_dd_no = NULL, $bank_name = NULL, $branch_name = NULL, date("Y-m-d", strtotime(convertDate($request->created_at))), $online_payment_id = NULL, $online_payment_by = NULL, $ssbAccountDetails->account_no, 'CR');
                    $description = $description;
                    $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran, $satRefId, 1, $ssbAccountDetails->id, NULL, $ssbAccountDetails->member_id, $request->maturity_amount_payable + $ssbAccountDetails->balance, $amount, $withdrawal = 0, $description, $ssbAccountDetails->account_no, $ssbAccountDetails->branch_id, $ssbAccountDetails->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $ssbAccountDetails->member_id, $ssbAccountDetails->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($request->created_at))), NULL, $online_payment_by = NULL, $ssbAccountDetails->account_no, 'CR');
                    $allHeadTransaction = CommanTransactionsController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 56, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to, $saToTranctionId, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $created_by, $created_by_id);
                    /*$allTransaction = $this->createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,20,56,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$saToId,$saToTranctionId,NULL);*/
                    $memberTransaction = CommanTransactionsController::memberTransactionNew($dayBookRef, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $amount, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $saToTranctionId, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                }
                foreach ($taAdvanced as $key => $expvalue) {
                    $head1 = 4;
                    $head2 = 86;
                    $head3 = $expvalue->subcategory;
                    $head4 = NULL;
                    $head5 = NULL;
                    $allHeadTransaction = CommanTransactionsController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $head3, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $taAmount, $taAmount, $taAmount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to, $saToTranctionId, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $created_by, $created_by_id);
                    /*$allTransaction = CommanTransactionsController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$taAmount,$taAmount,$taAmount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/
                }
                if ($request->amount_mode == 2) {
                    if ($request->mode == 4) {
                        $bankAmount = $amount + $request->neft_charge;
                    } else {
                        $bankAmount = $amount;
                    }
                    $allHeadTransaction = CommanTransactionsController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, getSamraddhBank($request->bank)->account_head_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $bankAmount, $bankAmount, $bankAmount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to, $saToTranctionId, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $created_by, $created_by_id);
                    /*$allTransaction = CommanTransactionsController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,27,getSamraddhBank($request->bank)->account_head_id,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$bankAmount,$bankAmount,$bankAmount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/
                    $samraddhBankDaybook = CommanTransactionsController::samraddhBankDaybookNew($dayBookRef, $bank_id, $bank_ac_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $opening_balance, $bankAmount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id, $cheque_type, $cheque_id, $ssb_account_tran_id_to, $ssb_account_tran_id_from);
                }
                if ($request->amount_mode == 2 && $request->mode == 4) {
                    $allHeadTransaction = CommanTransactionsController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 92, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $request->neft_charge, $request->neft_charge, $request->neft_charge, 'NEFT Charge A/c Cr ' . $request->neft_charge . '', 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to, $saToTranctionId, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $created_by, $created_by_id);
                    /*$allTransaction = CommanTransactionsController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,92,$head4=NULL,$head5=NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge,$request->neft_charge,$request->neft_charge,'NEFT Charge A/c Cr '.$request->neft_charge.'','CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/
                    $updateBackDateloanBankBalance = CommanTransactionsController::updateBackDateloanBankBalance($amount, $request->bank, getSamraddhBankAccount($request->bank_account_number)->id, $request->created_at, $request->neft_charge);
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        if (count($ssbArray) > 0) {
            return redirect()->route('branch.damandadvice.viewtaadvanced')->with('success', 'Ta Advanced Successfully Adjustment AND ' . $ssbString . ' demand advice not have any ssb account!');
        } else {
            return redirect()->route('branch.damandadvice.viewtaadvanced')->with('success', 'Ta Advanced Successfully Adjustment!');
        }
    }
    /**
     * Update status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function approveDemandAdvice($id)
    {
        $demandStatus = DemandAdvice::select('status')->where('id', $id)->first();
        $adata = DemandAdvice::findOrFail($id);
        if ($demandStatus->status == 0) {
            $adata->status = 1;
        } else {
            $adata->status = 0;
        }
        $adata = $adata->save();
        if ($adata) {
            return redirect()->route('branch.demand.advices')->with('success', 'Demand advice approved successfully!');
        } else {
            return back()->with('alert', 'Problem with update demand advice');
        }
    }
    /**
     * Delete demand advice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $demandAdvice = DemandAdvice::select('payment_type')->where('id', $id)->first();
        if ($demandAdvice->payment_type == 0) {
            $deleteExpense = DemandAdviceExpense::where('demand_advice_id', $id)->delete();
            $deleteDemandAdvice = DemandAdvice::where('id', $id)->delete();
        } elseif ($demandAdvice->payment_type == 1 || $demandAdvice->payment_type == 2 || $demandAdvice->payment_type == 3 || $demandAdvice->payment_type == 3) {
            $deleteDemandAdvice = DemandAdvice::where('id', $id)->delete();
        }
        return back()->with('success', 'Demand advice deleted successfully!');
    }
    /**
     * Get employee code by employee name.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getSsbDetails(Request $request)
    {
        $account_number = $request->val;
        $ssbDetails = SavingAccount::with('ssbMember')->where('account_no', $account_number)->first();
        $return_array = compact('ssbDetails');
        return json_encode($return_array);
    }
    /**
     * Get employee details by employee code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getEmployeeDetails(Request $request)
    {
        $employee_code = $request->employee_code;
        $employeeDetails = Employee::select('id', 'employee_name', 'mobile_no', 'ssb_account', 'bank_name', 'bank_account_no', 'bank_ifsc_code')->where('employee_code', $employee_code)->where('status', 1)->get();
        $resCount = count($employeeDetails);
        $return_array = compact('employeeDetails', 'resCount');
        return json_encode($return_array);
    }
    /**
     * Get owner details by owner name.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getOwnerDetails(Request $request)
    {
        $ownerId = $request->val;
        $ownerDetails = RentLiability::select('owner_mobile_number', 'owner_ssb_number', 'owner_bank_name', 'owner_bank_account_number', 'owner_bank_ifsc_code')->where('id', $ownerId)->where('status', 0)->first();
        $return_array = compact('ownerDetails');
        return json_encode($return_array);
    }
    /**
     * Get investment details by account number.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getInvestmentDetails(Request $request)
    {
        $investmentAccount = $request->val;
        $type = $request->type;
        $subtype = $request->subtype;
        $companyId = $request->company_id;
        // $globaldate =date('Y-m-d',strtotime($request->date));
        $dateTime = DateTime::createFromFormat('d/m/Y', $request->date);
        $globaldate = $dateTime->format('Y-m-d');
        $cDate = date("Y-m-d");
        $getbranch = getUserBranchId(Auth::user()->id);
        $getCompanyId = \App\Models\CompanyBranch::wherehas('company',function($q){
            $q->whereStatus(1);
          })->where('branch_id',Auth::user()->branches->id)->pluck('company_id');
        $investmentDetails = Memberinvestments::select('account_number','associate_id','branch_id','current_balance','deposite_amount','interest_rate','id','plan_id','created_at','member_id','customer_id','tenure','ssb_account_number','company_id')
            ->with(
                'investmentNomiees:id,investment_id,name,phone_number', 
                'plan:id,name,plan_category_code,plan_sub_category_code,prematurity', 
                'member:id,member_id,first_name,last_name,mother_name,father_husband,mobile_no,status,is_block,photo,signature', 
                'ssb:id,member_id,account_no', 
                'memberBankDetail', 
                'investmentNomiees'
            )
            ->where('account_number', $investmentAccount)
            ->where('account_number', 'not like', "%R-%")
            ->where('is_mature', 1)
            ->where('investment_correction_request', 0)
            ->where('renewal_correction_request', 0)
            ->whereIn('company_id',$getCompanyId)
            ->where('branch_id',Auth::user()->branches->id)
            ->first();
        if ($investmentDetails) { 
            $date =  date('Y-m-d', strtotime($investmentDetails->created_at));
            $newdate =  date('Y-m-d', strtotime($date . '+ 1 year'));
            $requestDate =  date('Y-m-d', strtotime(convertDate($request->date)));
            $to = ($globaldate >= $investmentDetails->maturity_date) ? \Carbon\Carbon::parse($investmentDetails->maturity_date) : \Carbon\Carbon::parse($globaldate);
             /** Mahesh has added ->startOfDay() on 5 jan 2024 because without this we were unable to make demand on same day */
            $from = \Carbon\Carbon::parse($investmentDetails->created_at)->startOfDay();
            $investmentMonths = $to->diffInMonths($from,true);
            // dd($investmentDetails->member->photo);
            if($investmentDetails->member->photo != null && $investmentDetails->member->signature != null){
                if($investmentDetails->member->status == '0'){
                    $message = 'Customer is Inactive. Please contact administrator!';
                    $status = 500;
                    $isDefaulter = 0;
                    $finalAmount = 0;
                }else{
                    if($investmentDetails->member->is_block == '1'){
                        $message = 'Customer is Inactive. Please Upload Signature and Photo.';
                        $status = 500;
                        $isDefaulter = 0;
                        $finalAmount = 0;
                    }else{
                        if($subtype != '1'){
                            if (($investmentDetails->plan->prematurity == '0'  && $subtype != '0'   && $subtype != '1') || $investmentDetails->plan->plan_category_code == 'S') {
                                $message = 'Prematurity option not available for this plan!';
                                $status = 500;
                            } else {
                                if ($investmentDetails->plan->plan_category_code != 'S') {
                                    if ($type == 2 && $subtype == 1) {
                                        if ($requestDate < $newdate && $investmentDetails->plan->plan_sub_category_code == 'X') {
                                            $message = 'You Cannot Mature Plan before 1 Year  !';
                                            $status = 500;
                                        } else {
                                            $message = 'Record Not Found!';
                                            $status = 400;
                                            if ($investmentDetails) {
                                                $message = '';
                                                $status = 200;
                                            }
                                        }
                                    } else {
                                        $maturityDate =  date('Y-m-d', strtotime($investmentDetails->created_at . ' + ' . ($investmentDetails->tenure) . ' year'));
                                        $currentDate = date_create($cDate);
                                        $diff = strtotime($maturityDate) - strtotime($cDate);
                                        $daydiff = abs(round($diff / 86400));
                                        if ($globaldate < $maturityDate) {
                                            $mat_date = date('d/m/Y', strtotime($maturityDate));
                                            $message = "You Cannot Mature Plan before Maturity Date ($mat_date) !";
                                            // $message = "The Maturity Date of This Plan is $maturityDate So Please Have Patience!";
                                            $status = 500;
                                        } else {
                                            $message = '';
                                            $status = 200;
                                        }
                                    }
                                } else {
                                    $message = 'Record Not Found!';
                                    $status = 400;
                                }
                            }
                        }else{
                            $message = '';
                            $status = 200;
                        }
                        if ($investmentDetails && $status == 200) {
                            // Updated By Mahesh on 18 january 2024 because if we select pre-maturity then it is not going in pre-maturity
                            if (((($investmentDetails->plan->prematurity == '0' && $type != 4)) && $subtype != '0' /**&& $subtype != '1'*/) || $investmentDetails->plan->plan_category_code == 'S') {
                                $message = 'Prematurity option not available for this plan!';
                                $status = 500;
                                $isDefaulter = 0;
                                $finalAmount = 0;
                            } else {
                            $demandAdviceRecord = DemandAdvice::where('investment_id', $investmentDetails->id)->where('is_deleted', 0)->first();
                            if ($demandAdviceRecord) {
                                $isDefaulter = 0;
                                $finalAmount = 0;
                                $message = 'Already request created for this plan!';
                                $status = 500;
                            } else {
                                $mInvestment = $investmentDetails;
                                $planCategory = $investmentDetails->plan->plan_category_code;
                                if($subtype != 1)
                                {
                                    $interestData = getplanroi($investmentDetails->plan_id);
                                    $checkRoi = getRoi($interestData,$investmentMonths,$investmentDetails);
                                    $ActualInterest =  $checkRoi['ActualInterest'];
                                    if(!$checkRoi['roiExist'])
                                    {
                                        $message = 'Maturity Setting Not Updated for this Plan!';
                                        $status = 400;
                                    }
                                    if ($investmentDetails->plan->plan_category_code == 'D' ||  $investmentDetails->plan->plan_category_code == 'M') {
                                        $result = maturityCalculation($investmentDetails, 'demand_create',$investmentMonths,$ActualInterest);
                                        $isDefaulter = $result['defaulter'];
                                        $finalAmount = $result['final_amount'];
                                    }else{
                                        $isDefaulter = 0;
                                        $finalAmount = 0;
                                    }
                                }else {
                                    $isDefaulter = 0;
                                    $finalAmount = 0;
                                }
                            } }
                        } else {
                            $isDefaulter = 0;
                            $finalAmount = 0;
                        }
                    }
                }
            } else {
                $isDefaulter = '';
                $finalAmount = 0;
                $message = 'Please upload Photo and Signature of Customer and demand again';
                $status = 400;
            }
            $finalAmount = number_format((float)$finalAmount, 2, '.', '');
        }else{
            $isDefaulter = '';
            $finalAmount = 0;
            $message = 'Record Not Found!';
            $status = 400;
        }
        if(isset($investmentDetails) && isset($investmentDetails['current_balance'])){
            $investmentDetails['current_balance'] = InvestmentBalance::where('investment_id',$investmentDetails['id'])->value('totalBalance');
        }
        $signature = !empty($investmentDetails) ? $investmentDetails['member'] ? $investmentDetails['member']['signature'] ? ImageUpload::generatePreSignedUrl('profile/member_signature/'.$investmentDetails['member']['signature']) : '' : '' : '';
        $photo = !empty($investmentDetails) ? $investmentDetails['member'] ? $investmentDetails['member']['signature'] ? ImageUpload::generatePreSignedUrl('profile/member_avatar/'.$investmentDetails['member']['photo']) : '' : '' : ''; 
        $return_array = compact('investmentDetails', 'isDefaulter', 'finalAmount', 'message', 'status','signature','photo');
       return json_encode($return_array);
    }
    /**
     * Get investment details by account number.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getMemberDetails(Request $request)
    {
        $mId = $request->val;
        $companyId = $request->company_id;
        $mDetails = \App\Models\Member::with(['savingAccount_Customnew'=>function($q) use($companyId){
            $q->whereCompanyId($companyId);
        }])->where('member_id', $mId)->first();
        $count = \App\Models\Member::with(['savingAccount_Customnew'=>function($q) use($companyId){
            $q->whereCompanyId($companyId);
        }])->where('member_id', $mId)->count();
        // $mDetails = Member::with('savingAccount')->where('member_id', $mId)->first();
        // $count = Member::with('savingAccount')->where('member_id', $mId)->count();
        $return_array = compact('mDetails', 'count');
        return json_encode($return_array);
    }
    /*Call death help maturity page*/
    public function demandAdvicematurity()
    {
        $data['title'] = 'Maturity Management';
        return view('templates.branch.demand-advice.demand_advice_maturity', $data);
    }
    /**
     * Get investment details by id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getInvestmentData(Request $request)
    {
        $investmentId = $request->investmentId;
        $paymentType = $request->paymentType;
        $subPaymentType = $request->subPaymentType;
        $mInvestment = Memberinvestments::select('id', 'plan_id', 'tenure', 'deposite_amount', 'interest_rate', 'maturity_date', 'created_at')->where('id', $investmentId)->first();
        $maturity_date =  date("Y-m-d", strtotime('+ ' . ($mInvestment->tenure) . 'months', strtotime(date("Y/m/d"))));
        $investmentData = Daybook::select('investment_id', 'deposit', 'created_at')->where('investment_id', $investmentId)->whereIn('transaction_type', [2, 4])->where('created_at', '<=', $maturity_date)->orderby('created_at', 'asc')->get();
        $view = view("templates.branch.demand-advice.maturity_calculation", compact('investmentData', 'mInvestment', 'maturity_date', 'paymentType', 'subPaymentType'))->render();
        return response()->json(['html' => $view]);
    }
    /**
     * Get investment details by id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function saveInvestmentMaturityAmount(Request $request)
    {
        $demandAdviceIds = $request->demand_advice_id;
        DB::beginTransaction();
        try {
            foreach ($demandAdviceIds as $key => $value) {
                $daData = [
                    'maturity_amount_till_date' => $request->maturity_amount_till_date[$key],
                    'maturity_amount_payable' => $request->maturity_amount_payable[$key],
                    'is_mature' => 0,
                ];
                $demandAdvice = DemandAdvice::find($key);
                $demandAdvice->update($daData);
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return back()->with('success', 'Successfully created maturity');
    }
    /**
     * Display application listing of the account heads.
     *
     * @return \Illuminate\Http\Response
     */
    public function application()
    {
        if (!in_array('Demand Advice Application', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $data['title'] = 'Demand Advice Application';
        $getBranchId = getUserBranchId(Auth::user()->id);
        $data['branch_id'] = $getBranchId->id;
        return view('templates.branch.demand-advice.application', $data);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function applicationListing(Request $request)
    {
        $arrFormData = array();
        if (!empty($_POST['searchform'])) {
            foreach ($_POST['searchform'] as $frm_data) {
                $arrFormData[$frm_data['name']] = $frm_data['value'];
            }
        }
        if ($request->ajax() && $arrFormData['company_id']) {
            $getBranchId = getUserBranchId(Auth::user()->id);
            $branch_id = $getBranchId->id;
            $data = DemandAdvice::select('id', 'investment_id','is_reject', 'maturity_payment_mode', 'tds_amount', 'maturity_amount_till_date', 'maturity_prematurity_amount', 'payment_mode', 'payment_type', 'opening_date', 'sub_payment_type', 'date', 'voucher_number', 'maturity_amount_payable', 'final_amount', 'account_number', 'bank_account_number', 'bank_ifsc', 'is_print', 'status', 'employee_id', 'branch_id', 'owner_id', 'letter_photo_id', 'is_mature', 'advanced_amount', 'interestAmount', 'company_id')
                ->with('expenses', 'branch:id,name', 'company:id,name')
                ->where('branch_id', $branch_id)
                ->where('is_deleted', '0');
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['date_from'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));
                    if ($arrFormData['date_to'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween('date', [$startDate, $endDate]);
                }
                if (isset($arrFormData['account_number']) && $arrFormData['account_number'] != '') {
                    $account_number = $arrFormData['account_number'];
                    $data = $data->where('account_number', '=', $account_number);
                }
                // if ($arrFormData['filter_branch'] != '') {
                //     $branchId = $arrFormData['filter_branch'];
                //     $data = $data->where('branch_id', '=', $branchId);
                // }
                if ($arrFormData['company_id'] != '') {
                    $company_id = $arrFormData['company_id'];
                    $data = $data->where('company_id', '=', $company_id);
                }
                if ($arrFormData['advice_type'] != '') {
                    $advice_id = $arrFormData['advice_type'];
                    $advice_type_id = $arrFormData['expense_type'];
                    // if ($advice_id == 1 || $advice_id == 2 || $advice_id == 3 || $advice_id == 4) {
                    //     $data = $data->where('is_mature', 0);
                    // }
                    if ($advice_id == 0 || $advice_id == 1 || $advice_id == 2) {
                        if ($advice_type_id != '') {
                            $data = $data->where('payment_type', '=', $advice_id)->where('sub_payment_type', $advice_type_id);
                        } else {
                            $data = $data->where('payment_type', '=', $advice_id);
                        }
                    } elseif ($advice_id == 3) {
                        $data = $data->where('payment_type', '=', 3)->where('death_help_catgeory', '=', 0);
                    } elseif ($advice_id == 4) {
                        $data = $data->where('payment_type', '=', 3)->where('death_help_catgeory', '=', 1);
                    }
                }
            }/*else{
                $data=$data->where('payment_type',5);
            }*/
            $data = $data->where('status', 0)->orderby('id', 'DESC')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('branch_name', function ($row) {
                    return $row['branch']->name;
                })
                ->rawColumns(['branch_name'])
                ->addColumn('maturity_payment_mode', function ($row) {
                    return $row['maturity_payment_mode'];
                })
                ->rawColumns(['maturity_payment_mode'])
                ->addColumn('company_name', function ($row) {
                    $company_name = $row['company']->name;
                    return $company_name;
                })
                ->rawColumns(['company_name'])
                ->addColumn('account_number', function ($row) {
                    if ($row->account_number) {
                        // return $row->account_number;
                        return ($row['investment'] ? $row['investment']['member'] ? '('.$row['investment']['member']->member_id.') ' : '' : ''). $row->account_number;
                    } elseif ($row->investment_id) {
                        $account_number = getInvestmentDetails($row->investment_id)->account_number;
                        // return $account_number;
                        return ($row['investment'] ? $row['investment']['member'] ? '('.$row['investment']['member']->member_id.') ' : '' : ''). $account_number;
                    } else {
                        return "N/A";
                    }
                })
                ->rawColumns(['account_number'])
                ->addColumn('member_name', function ($row) {
                    if ($row->investment_id) {
                        // $member_name = getMemberDataAssociateId($associate_id)->first_name . ' ' . getMemberDataAssociateId($associate_id)->last_name;
                        $name = $row->investment ? $row->investment->member ? $row->investment->member->first_name.' '.$row->investment->member->last_name??'' : 'N/A' : 'N/A';
                        $member_name = '<a target="_blank" href="' . route("branch.memberDetail", $row->investment->member->id) . '?type=0" >' . $name . '</a>';
                        // $member_name = $name;
                    } else {
                        $member_name = "N/A";
                    }
                    return $member_name;
                })
                ->rawColumns(['member_name'])
                ->addColumn('associate_code', function ($row) {
                    if ($row->investment_id) {
                        $associate_code = $row->investment ? $row->investment->associateMember ? $row->investment->associateMember->associate_no ?? 'N/A' : 'N/A' : 'N/A';
                    } else {
                        $associate_code = "N/A";
                    }
                    return $associate_code;
                })
                ->rawColumns(['associate_code'])
                ->addColumn('is_loan', function ($row) {
                    if ($row->investment_id) {
                        $member_id = getInvestmentDetails($row->investment_id)->associate_id;
                        $customer_id = getInvestmentDetails($row->investment_id)->customer_id;
                        $loanDetail =  $this->getDatabyCustomer(new Memberloans(), $customer_id);
                    } else {
                        $loanDetail = 'N/A';
                    }
                    return $loanDetail;
                })
                ->rawColumns(['is_loan'])
                ->addColumn('associate_name', function ($row) {
                    if ($row->investment_id) {
                        $associate_name = $row->investment ? $row->investment->associateMember ? $row->investment->associateMember->first_name.' '.$row->investment->associateMember->last_name??'' : 'N/A' : 'N/A';
                    } else {
                        $associate_name = "N/A";
                    }
                    return $associate_name;
                })
                ->rawColumns(['associate_name'])
                ->addColumn('date', function ($row) {
                    return date("d/m/Y", strtotime($row->date));
                })
                ->rawColumns(['date'])
                ->addColumn('created_at', function ($row) {
                    if ($row->investment_id) {
                        $investmentDetail = getInvestmentDetails($row->investment_id)->created_at;
                        return date("d/m/Y", strtotime($investmentDetail));
                    }
                })
                ->rawColumns(['created_at'])
                ->addColumn('advice_type', function ($row) {
                    if ($row->payment_type == 0) {
                        return 'Expenses';
                    } elseif ($row->payment_type == 1) {
                        return 'Maturity';
                    } elseif ($row->payment_type == 2) {
                        return 'Prematurity';
                    } elseif ($row->payment_type == 3) {
                        if ($row->sub_payment_type == '4') {
                            return 'Death Help';
                        } elseif ($row->sub_payment_type == '5') {
                            return 'Death Claim';
                        }
                    }
                })
                ->rawColumns(['advice_type'])
                ->addColumn('expense_type', function ($row) {
                    if ($row->sub_payment_type == '0') {
                        return 'Fresh Expense';
                    } elseif ($row->sub_payment_type == '1') {
                        return 'TA Advanced';
                    } elseif ($row->sub_payment_type == '2') {
                        return 'Advanced salary';
                    } elseif ($row->sub_payment_type == '3') {
                        return 'Advanced Rent';
                    } elseif ($row->sub_payment_type == '4') {
                        return 'N/A';
                    } elseif ($row->sub_payment_type == '5') {
                        return 'N/A';
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['expense_type'])
                ->addColumn('voucher_number', function ($row) {
                    return $row->voucher_number;
                })
                ->rawColumns(['voucher_number'])
                ->addColumn('total_amount', function ($row) {
                    if ($row->investment_id) {
                        $investmentAmount = Daybook::where('investment_id', $row->investment_id)
                            ->whereIn('transaction_type', [2, 4])
                            ->where('account_no', $row->account_number)
                            ->sum('deposit');
                        return round($investmentAmount) . ' &#8377';
                    } else if ($row->advanced_amount) {
                        return round($row->advanced_amount) . ' &#8377';
                    } else {
                        return 'N/A';
                    }
                })
                ->escapeColumns(['total_amount'])
                ->addColumn('total_payable_amount', function ($row) {                                           
                    return $row->maturity_amount_payable ? (round($row->maturity_amount_payable).' ') : 'N/A';
                })
                ->escapeColumns(['total_payable_amount'])
                ->addColumn('status', function ($row) {
                 
                    if ($row->status == 0 && $row->is_reject == 1) {
                        $status = 'Rejected';
                    } else {
                        if ($row->status == 0) {
                            $status = 'Pending';
                        } else {
                            $status = 'Approved';
                        }
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->addColumn('reason', function ($row) {
                    if (isset($row['demandReason'][0]->reason)) {
                        $reason = $row['demandReason'][0]->reason;
                    } else {
                        $reason = 'N/A';
                    }
                    return $reason;
                })
                ->rawColumns(['reason'])
                ->addColumn('action', function ($row) {
                    $url = URL::to("branch/demand-advice/edit-demand-advice/" . $row->id . "");
                    $deleteurl = URL::to("branch/delete-demand-advice/" . $row->id . "");
                    if ($row->is_reject  != 0) {
                        $btn = '<a class="dropdown-item" href="' . $url . '"><i class="icon-pencil7"></i>Edit</a>';
                        $btn .= '<a class="dropdown-item delete-demand-advice" href="' . $deleteurl . '"><i class="fas fa-trash-alt"></i></a>';
                        return $btn;
                    }
                })
                ->rawColumns(['action'])
                ->make(true);
        } else {
            return json_encode([]);
        }
    }
    /**
     * Display rent payable listing.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function approveDemandAdviceView(Request $request)
    {
        $data['title'] = 'Demand Advice | Approve';
        if ($request['selected_records'] && isset($request['selected_records'])) {
            $sRecord = explode(',', $request['selected_records']);
            $data['demandAdvice'] = DemandAdvice::with('investment', 'expenses', 'branch')->whereIn('id', $sRecord)->get();
            $data['selectedRecords'] = $request['selected_records'];
        } else {
            $data['demandAdvice'] = array();
            $data['selectedRecords'] = 0;
        }
        $data['cBanks'] = SamraddhBank::with('bankAccount')->where("status", "1")->get();
        $data['cheques'] = SamraddhCheque::select('cheque_no', 'account_id')->where('status', 1)->get();
        $data['assets_category'] = AccountHeads::where('parent_id', 2)->get();
        $data['assets_subcategory'] = AccountHeads::whereIn('parent_id', [9, 10, 11])->get();
        if ($data['demandAdvice'][0]->payment_type == 0 && $data['demandAdvice'][0]->sub_payment_type == 0) {
            $data['type'] = 0;
            $data['subType'] = 0;
            return view('templates.branch.demand-advice.fresh_expense_approve', $data);
        }
        if ($data['demandAdvice'][0]->payment_type == 4) {
            $data['type'] = $data['demandAdvice'][0]->payment_type;
            $data['subType'] = $data['demandAdvice'][0]->sub_payment_type;
            return view('templates.branch.demand-advice.approve-emergancy-maturity', $data);
        } else {
            $data['type'] = $data['demandAdvice'][0]->payment_type;
            $data['subType'] = $data['demandAdvice'][0]->sub_payment_type;
            return view('templates.branch.demand-advice.approve', $data);
        }
    }
    public function getBankDayBookAmount(Request $request)
    {
        $fromBankId = $request->fromBankId;
        $date = date("Y-m-d", strtotime(convertDate($request->date)));
        $bankRes = SamraddhBankClosing::where('bank_id', $fromBankId)->orderBy('entry_date', 'desc')->first();
        if ($bankRes) {
            $bankDayBookAmount = (int)$bankRes->balance;
        } else {
            $bankRes = SamraddhBankClosing::where('bank_id', $fromBankId)->whereDate('entry_date', '<', $date)->orderby('entry_date', 'DESC')->first();
            $bankDayBookAmount = (int)$bankRes->balance;
        }
        $return_array = compact('bankDayBookAmount');
        return json_encode($return_array);
    }
    // Edit Branch to ho
    public function getBranchDayBookAmount(Request $request)
    {
        $branch_id = $request->branch_id;
        $date = date("Y-m-d", strtotime(convertDate($request->date)));
        $microLoanRes = BranchCash::select('balance', 'loan_balance')->where('branch_id', $branch_id)->orderBy('entry_date', 'desc')->first();
        if ($microLoanRes) {
            $loanDayBookAmount = (int)$microLoanRes->loan_balance;
            $microDayBookAmount = (int)$microLoanRes->balance;
        } else {
            $microLoan = BranchCash::select('balance', 'loan_balance')->where('branch_id', $branch_id)->whereDate('entry_date', '<', $date)->orderby('entry_date', 'DESC')->first();
            $loanDayBookAmount = (int)$microLoan->loan_balance;
            $microDayBookAmount = (int)$microLoan->balance;
        }
        $return_array = compact('microDayBookAmount', 'loanDayBookAmount');
        return json_encode($return_array);
    }
    public static function updateBranchCashCr($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s", strtotime(convertDate($date)));
        $currentDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            $Result = \App\Models\BranchCash::find($currentDateRecord->id);
            if ($type == 0) {
                $data['balance'] = $currentDateRecord->balance + $amount;
            } elseif ($type == 1) {
                $data['loan_balance'] = $currentDateRecord->loan_balance + $amount;
            }
            $data['updated_at'] = $date;
            $Result->update($data);
            $getNextBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextBranchRecord) {
                foreach ($getNextBranchRecord as $key => $value) {
                    $sResult = \App\Models\BranchCash::find($value->id);
                    if ($type == 1) {
                        $sData['loan_opening_balance'] = $value->loan_closing_balance;
                        $sData['loan_balance'] = $value->loan_balance + $amount;
                        if ($value->closing_balance > 0) {
                            $sData['loan_closing_balance'] = $value->loan_closing_balance + $amount;
                        }
                    } elseif ($type == 0) {
                        $sData['opening_balance'] = $value->closing_balance;
                        $sData['balance'] = $value->balance + $amount;
                        if ($value->closing_balance > 0) {
                            $sData['closing_balance'] = $value->closing_balance + $amount;
                        }
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                    $sResult->update($sData);
                }
            }
        } else {
            $oldDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchCash::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $data1['loan_closing_balance'] = $oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data1RecordExists = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($type == 0) {
                    $data['balance'] = $oldDateRecord->balance + $amount;
                } else {
                    $data['balance'] = $oldDateRecord->balance;
                }
                if ($type == 1) {
                    $data['loan_balance'] = $oldDateRecord->loan_balance + $amount;
                } else {
                    $data['loan_balance'] = $oldDateRecord->loan_balance;
                }
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['loan_opening_balance'] = $oldDateRecord->loan_balance;
                if ($data1RecordExists) {
                    if ($type == 0) {
                        $data['closing_balance'] = $oldDateRecord->balance + $amount;
                    } else {
                        $data['closing_balance'] = $oldDateRecord->balance;
                    }
                    if ($type == 1) {
                        $data['loan_closing_balance'] = $oldDateRecord->loan_balance + $amount;
                    } else {
                        $data['loan_closing_balance'] = $oldDateRecord->loan_balance;
                    }
                    foreach ($data1RecordExists as $key => $value) {
                        $sResult = \App\Models\BranchCash::find($value->id);
                        if ($type == 1) {
                            $sData['loan_opening_balance'] = $value->loan_closing_balance;
                            $sData['loan_balance'] = $value->loan_balance + $amount;
                            if ($value->closing_balance > 0) {
                                $sData['loan_closing_balance'] = $value->loan_closing_balance + $amount;
                            }
                        } elseif ($type == 0) {
                            $sData['opening_balance'] = $value->closing_balance;
                            $sData['balance'] = $value->balance + $amount;
                            if ($value->closing_balance > 0) {
                                $sData['closing_balance'] = $value->closing_balance + $amount;
                            }
                        }
                        $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                        $sResult->update($sData);
                    }
                } else {
                    $data['closing_balance'] = 0;
                    $data['loan_closing_balance'] = 0;
                }
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            } else {
                if ($type == 0) {
                    $data['balance'] = $amount;
                } else {
                    $data['balance'] = 0;
                }
                if ($type == 1) {
                    $data['loan_balance'] = $amount;
                } else {
                    $data['loan_balance'] = 0;
                }
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                $data['loan_opening_balance'] = 0;
                $data['loan_closing_balance'] = 0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            }
        }
        return true;
    }
    public static function updateBranchClosingCashCr($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s", strtotime(convertDate($date)));
        $currentDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            $Result = \App\Models\BranchClosing::find($currentDateRecord->id);
            if ($type == 0) {
                $data['balance'] = $currentDateRecord->balance + $amount;
            } elseif ($type == 1) {
                $data['loan_balance'] = $currentDateRecord->loan_balance + $amount;
            }
            $data['updated_at'] = $date;
            $Result->update($data);
            $getNextBranchClosingRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextBranchClosingRecord) {
                foreach ($getNextBranchClosingRecord as $key => $value) {
                    $sResult = \App\Models\BranchClosing::find($value->id);
                    if ($type == 1) {
                        $sData['loan_opening_balance'] = $value->loan_closing_balance;
                        $sData['loan_balance'] = $value->loan_balance + $amount;
                        if ($value->closing_balance > 0) {
                            $sData['loan_closing_balance'] = $value->loan_closing_balance + $amount;
                        }
                    } elseif ($type == 0) {
                        $sData['opening_balance'] = $value->closing_balance;
                        $sData['balance'] = $value->balance + $amount;
                        if ($value->closing_balance > 0) {
                            $sData['closing_balance'] = $value->closing_balance + $amount;
                        }
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                    $sResult->update($sData);
                }
            }
        } else {
            $oldDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $data1['loan_closing_balance'] = $oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data1RecordExists = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($type == 0) {
                    $data['balance'] = $oldDateRecord->balance + $amount;
                } else {
                    $data['balance'] = $oldDateRecord->balance;
                }
                if ($type == 1) {
                    $data['loan_balance'] = $oldDateRecord->loan_balance + $amount;
                } else {
                    $data['loan_balance'] = $oldDateRecord->loan_balance;
                }
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['loan_opening_balance'] = $oldDateRecord->loan_balance;
                if ($data1RecordExists) {
                    if ($type == 0) {
                        $data['closing_balance'] = $oldDateRecord->balance + $amount;
                    } else {
                        $data['closing_balance'] = $oldDateRecord->balance;
                    }
                    if ($type == 1) {
                        $data['loan_closing_balance'] = $oldDateRecord->loan_balance + $amount;
                    } else {
                        $data['loan_closing_balance'] = $oldDateRecord->loan_balance;
                    }
                    foreach ($data1RecordExists as $key => $value) {
                        $sResult = \App\Models\BranchClosing::find($value->id);
                        if ($type == 1) {
                            $sData['loan_opening_balance'] = $value->loan_closing_balance;
                            $sData['loan_balance'] = $value->loan_balance + $amount;
                            if ($value->closing_balance > 0) {
                                $sData['loan_closing_balance'] = $value->loan_closing_balance + $amount;
                            }
                        } elseif ($type == 0) {
                            $sData['opening_balance'] = $value->closing_balance;
                            $sData['balance'] = $value->balance + $amount;
                            if ($value->closing_balance > 0) {
                                $sData['closing_balance'] = $value->closing_balance + $amount;
                            }
                        }
                        $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                        $sResult->update($sData);
                    }
                } else {
                    $data['closing_balance'] = 0;
                    $data['loan_closing_balance'] = 0;
                }
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            } else {
                if ($type == 0) {
                    $data['balance'] = $amount;
                } else {
                    $data['balance'] = 0;
                }
                if ($type == 1) {
                    $data['loan_balance'] = $amount;
                } else {
                    $data['loan_balance'] = 0;
                }
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                $data['loan_opening_balance'] = 0;
                $data['loan_closing_balance'] = 0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            }
        }
        return true;
    }
    public static function updateBranchCashDr($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s", strtotime(convertDate($date)));
        $currentDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            $Result = \App\Models\BranchCash::find($currentDateRecord->id);
            if ($type == 0) {
                $data['balance'] = $currentDateRecord->balance - $amount;
            } elseif ($type == 1) {
                $data['loan_balance'] = $currentDateRecord->loan_balance - $amount;
            }
            $data['updated_at'] = $date;
            $Result->update($data);
            $insertid = $currentDateRecord->id;
        } else {
            $oldDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchCash::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $data1['loan_closing_balance'] = $oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                if ($type == 0) {
                    $data['balance'] = $oldDateRecord->balance - $amount;
                } else {
                    $data['balance'] = $oldDateRecord->balance;
                }
                if ($type == 1) {
                    $data['loan_balance'] = $oldDateRecord->loan_balance - $amount;
                } else {
                    $data['loan_balance'] = $oldDateRecord->loan_balance;
                }
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['closing_balance'] = 0;
                $data['loan_opening_balance'] = $oldDateRecord->loan_balance;
                $data['loan_closing_balance'] = 0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            } else {
                if ($type == 0) {
                    $data['balance'] = $amount;
                } else {
                    $data['balance'] = 0;
                }
                if ($type == 1) {
                    $data['loan_balance'] = $amount;
                } else {
                    $data['loan_balance'] = 0;
                }
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                $data['loan_opening_balance'] = 0;
                $data['loan_closing_balance'] = 0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            }
        }
        return true;
    }
    public static function updateBranchClosingCashDr($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s", strtotime(convertDate($date)));
        $currentDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            $Result = \App\Models\BranchClosing::find($currentDateRecord->id);
            if ($type == 0) {
                $data['balance'] = $currentDateRecord->balance - $amount;
            } elseif ($type == 1) {
                $data['loan_balance'] = $currentDateRecord->loan_balance - $amount;
            }
            $data['updated_at'] = $date;
            $Result->update($data);
            $getNextBranchClosingRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextBranchClosingRecord) {
                foreach ($getNextBranchClosingRecord as $key => $value) {
                    $sResult = \App\Models\BranchClosing::find($value->id);
                    if ($type == 1) {
                        $sData['loan_opening_balance'] = $value->loan_closing_balance;
                        $sData['loan_balance'] = $value->loan_balance - $amount;
                        if ($value->closing_balance > 0) {
                            $sData['loan_closing_balance'] = $value->loan_closing_balance - $amount;
                        }
                    } elseif ($type == 0) {
                        $sData['opening_balance'] = $value->closing_balance;
                        $sData['balance'] = $value->balance - $amount;
                        if ($value->closing_balance > 0) {
                            $sData['closing_balance'] = $value->closing_balance - $amount;
                        }
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                    $sResult->update($sData);
                }
            }
        } else {
            $oldDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $data1['loan_closing_balance'] = $oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data1RecordExists = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($type == 0) {
                    $data['balance'] = $oldDateRecord->balance - $amount;
                } else {
                    $data['balance'] = $oldDateRecord->balance;
                }
                if ($type == 1) {
                    $data['loan_balance'] = $oldDateRecord->loan_balance - $amount;
                } else {
                    $data['loan_balance'] = $oldDateRecord->loan_balance;
                }
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['loan_opening_balance'] = $oldDateRecord->loan_balance;
                if ($data1RecordExists) {
                    if ($type == 0) {
                        $data['closing_balance'] = $oldDateRecord->balance - $amount;
                    } else {
                        $data['closing_balance'] = $oldDateRecord->balance;
                    }
                    if ($type == 1) {
                        $data['loan_closing_balance'] = $oldDateRecord->loan_balance - $amount;
                    } else {
                        $data['loan_closing_balance'] = $oldDateRecord->loan_balance;
                    }
                    foreach ($data1RecordExists as $key => $value) {
                        $sResult = \App\Models\BranchClosing::find($value->id);
                        if ($type == 1) {
                            $sData['loan_opening_balance'] = $value->loan_closing_balance;
                            $sData['loan_balance'] = $value->loan_balance - $amount;
                            if ($value->closing_balance > 0) {
                                $sData['loan_closing_balance'] = $value->loan_closing_balance - $amount;
                            }
                        } elseif ($type == 0) {
                            $sData['opening_balance'] = $value->closing_balance;
                            $sData['balance'] = $value->balance - $amount;
                            if ($value->closing_balance > 0) {
                                $sData['closing_balance'] = $value->closing_balance - $amount;
                            }
                        }
                        $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                        $sResult->update($sData);
                    }
                } else {
                    $data['closing_balance'] = 0;
                    $data['loan_closing_balance'] = 0;
                }
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            } else {
                if ($type == 0) {
                    $data['balance'] = $amount;
                } else {
                    $data['balance'] = 0;
                }
                if ($type == 1) {
                    $data['loan_balance'] = $amount;
                } else {
                    $data['loan_balance'] = 0;
                }
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                $data['loan_opening_balance'] = 0;
                $data['loan_closing_balance'] = 0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            }
        }
        return true;
    }
    public static function employeeSalaryLeaser($employee_id, $branch_id, $type, $type_id, $opening_balance, $deposit, $withdrawal, $description, $currency_code, $payment_type, $payment_mode, $status, $created_at, $updated_at, $v_no, $v_date, $ssb_account_id_to, $ssb_account_id_from, $to_bank_name, $to_bank_branch, $to_bank_ac_no, $to_bank_ifsc, $to_bank_id, $to_bank_account_id, $from_bank_name, $from_bank_branch, $from_bank_ac_no, $from_bank_ifsc, $from_bank_id, $from_bank_ac_id, $cheque_id, $cheque_no, $cheque_date, $transaction_no, $transaction_date, $transaction_charge)
    {
        $data['employee_id'] = $employee_id;
        $data['branch_id'] = $branch_id;
        $data['type'] = $type;
        $data['type_id'] = $type_id;
        $data['opening_balance'] = $opening_balance;
        $data['deposit'] = $deposit;
        $data['withdrawal'] = $withdrawal;
        $data['description'] = $description;
        $data['currency_code'] = $currency_code;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['status'] = $status;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['to_bank_name'] = $to_bank_name;
        $data['to_bank_branch'] = $to_bank_branch;
        $data['to_bank_ac_no'] = $to_bank_ac_no;
        $data['to_bank_ifsc'] = $to_bank_ifsc;
        $data['to_bank_id'] = $to_bank_id;
        $data['to_bank_account_id'] = $to_bank_account_id;
        $data['from_bank_name'] = $from_bank_name;
        $data['from_bank_branch'] = $from_bank_branch;
        $data['from_bank_ac_no'] = $from_bank_ac_no;
        $data['from_bank_ifsc'] = $from_bank_ifsc;
        $data['from_bank_id'] = $from_bank_id;
        $data['from_bank_ac_id'] = $from_bank_ac_id;
        $data['cheque_id'] = $cheque_id;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['transaction_no'] = $transaction_no;
        $data['transaction_date'] = $transaction_date;
        $data['transaction_charge'] = $transaction_charge;
        $transcation = \App\Models\EmployeeSalaryLeaser::create($data);
        return $transcation->id;
    }
    public static function employeeLedgerBackDateDR($employee_id, $date, $amount)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("h:i:s");
        $getNextrecord = \App\Models\EmployeeLedger::where('employee_id', $employee_id)->whereDate('created_at', '>', $entryDate)->orderby('created_at', 'ASC')->get();
        if ($getNextrecord) {
            foreach ($getNextrecord as $v1) {
                $ResultNext1 = \App\Models\EmployeeLedger::find($v1->id);
                $nextData1['opening_balance'] = $v1->opening_balance - $amount;
                $ResultNext1->update($nextData1);
                $insertid = $ResultNext1->id;
            }
        }
        return true;
    }
    public static function RentLiabilityLedger($rent_liability_id, $type, $type_id, $opening_balance, $deposit, $withdrawal, $description, $currency_code, $payment_type, $payment_mode, $status, $created_at, $updated_at, $v_no, $v_date, $ssb_account_id_to, $ssb_account_id_from, $to_bank_name, $to_bank_branch, $to_bank_ac_no, $to_bank_ifsc, $to_bank_id, $to_bank_account_id, $from_bank_name, $from_bank_branch, $from_bank_ac_no, $from_bank_ifsc, $from_bank_id, $from_bank_ac_id, $cheque_id, $cheque_no, $cheque_date, $transaction_no, $transaction_date, $transaction_charge)
    {
        $data['rent_liability_id'] = $rent_liability_id;
        $data['type'] = $type;
        $data['type_id'] = $type_id;
        $data['opening_balance'] = $opening_balance;
        $data['deposit'] = $deposit;
        $data['withdrawal'] = $withdrawal;
        $data['description'] = $description;
        $data['currency_code'] = $currency_code;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['status'] = $status;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['to_bank_name'] = $to_bank_name;
        $data['to_bank_branch'] = $to_bank_branch;
        $data['to_bank_ac_no'] = $to_bank_ac_no;
        $data['to_bank_ifsc'] = $to_bank_ifsc;
        $data['to_bank_id'] = $to_bank_id;
        $data['to_bank_account_id'] = $to_bank_account_id;
        $data['from_bank_name'] = $from_bank_name;
        $data['from_bank_branch'] = $from_bank_branch;
        $data['from_bank_ac_no'] = $from_bank_ac_no;
        $data['from_bank_ifsc'] = $from_bank_ifsc;
        $data['from_bank_id'] = $from_bank_id;
        $data['from_bank_ac_id'] = $from_bank_ac_id;
        $data['cheque_id'] = $cheque_id;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['transaction_no'] = $transaction_no;
        $data['transaction_date'] = $transaction_date;
        $data['transaction_charge'] = $transaction_charge;
        $transcation = \App\Models\RentLiabilityLedger::create($data);
        return $transcation->id;
    }
    public static function createAllTransaction($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head1, $head2, $head3, $head4, $head5, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from)
    {
        $t = date("H:i:s");
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
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($created_at)));
        $transcation = \App\Models\AllTransaction::create($data);
        return $transcation->id;
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
    public function getHeadLedgerData(Request $request)
    {
        $head_id = $request->head_id;
        $accountHead = AccountHeads::where('parent_id', $head_id)->where('status', 0)->get();
        echo json_encode($accountHead);
    }
    public function reDemand_advice($id)
    {
        $response['status'] = '';
        $id = $id;
        $demandData = DemandAdvice::find($id);
        $redemandData = RedemandDemandAdvice::where('demand_id', $demandData->id)
            ->first();
        if (!empty($redemandData)) {
            $redemandData->update(['redemand_times' => $redemandData->redemand_times + 1]);
        } else {
            $data = ['demand_id' => $demandData->id, 'redemand_times' => 1,];
            $createdData =RedemandDemandAdvice::create($data);
            $response['status'] = 1;
        }
        $demandData->update(['is_reject' => '0', 'is_redemand' => '1']);
        $response = array(
            'status' => 'success',
            'msg' => 'Redemand Successfully',
        );
        return response()->json($response);
    }
    public function getSSBAccountNumber(Request $request)
    {
        $account_number = $request->account_number;
        $account_number1 = "";
        $status1 = "0";
        $getSavingAccountDetails = Memberinvestments::where('account_number', $account_number)->first();
        if (isset($getSavingAccountDetails->member_id)) {
            $member_id = $getSavingAccountDetails->member_id;
            $saving_account_number = SavingAccount::where("member_id", $member_id)->first();
            if (isset($saving_account_number->account_no)) {
                $account_number1 = $saving_account_number->account_no;
                $status1 = "1";
            }
        }
        $resp = array("status" => $status1, "account_number" => $account_number1);
        echo json_encode($resp);
        die;
    }
    public function exportDemandAdviceReport(Request $request)
    {
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branchId = $getBranchId->id;
        $fromDate = $request->input('date_from');
        $toDate = $request->input('date_to');
        $startDate = '';
        $companyId = $request->input('company_id');
        $account_number = $request->input('account_number');
        $advice_id = $request->input('advice_type');
        $advice_type_id = $request->input('expense_type');
        $voucher_number = $request->input('voucher_number');
        $status = $request->input('status');
        $tokenName = '';
        $input = $request->all();
        if ($request['demand_advice_report_export'] == 0) {
            $token = Session::get('_fileName');           
            $start = $input["start"];
            $limit = $input["limit"];
            if ($start == 0) {
                $tokenName = Carbon::now()->format('Y_m_d_u');
            } else {
                $tokenName = $input["fileName"];
            }
            $returnURL = URL::to('/') . "/asset/" . $tokenName . '_demand_advice_report.csv';
            $fileName = env('APP_EXPORTURL') . "/asset/" . $tokenName . '_demand_advice_report.csv';
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            // header("Content-type: text/csv");
        }
        DB::beginTransaction();
        try {
            $data = \App\Models\DemandAdviceReport::where('payment_type', '!=', 0) 
            ->when($fromDate != '', function ($query) use ($fromDate, &$startDate, $toDate) {
                $startDate = date("Y-m-d", strtotime(convertDate($fromDate)));
                $query->when($toDate != '', function ($query) use ($toDate, &$endDate) {
                    $endDate = date("Y-m-d", strtotime(convertDate($toDate)));
                });
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->when($branchId != '', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($companyId != '', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->when($account_number != '', function ($query) use ($account_number, $advice_id) {
                if ($advice_id != '' && $advice_id == 5) {
                    $query->where('investmentAccountno', $account_number);
                } else {
                    $query->where('account_number', '=', $account_number);
                }
            }) 
            ->when($advice_id != '', function ($query) use ($advice_id, $advice_type_id) {
                if ($advice_id == 3) {
                    $query->where('payment_type', '=', 3)
                            ->where('death_help_catgeory', '=', 0);
                } elseif ($advice_id == 4) {
                    $query->where('payment_type', '=', 3)
                            ->where('death_help_catgeory', '=', 1);
                } elseif ($advice_id == 5) {
                    $query->where('payment_type', '=', 4);
                } else {
                    $query->where('payment_type', '=', $advice_id);
                }                    
            })
            ->when($voucher_number != '', function ($query) use ($voucher_number) {
                $query->where('voucher_number', $voucher_number);
            })
            ->when($status != '', function ($query) use ($status) {
                $query->when(in_array($status,[0,1]),function($q) use($status){
                    $q->where('status', $status)->where('is_reject','0');
                })->when($status == 2 ,function ($q) {                        
                    $q->where('is_reject','1');
                });
            })
            ;
            
            $totalResults = $data->count('id');
            $results = $data->orderby('created_at', 'DESC')
                ->offset($start)->limit($limit)
                ->get(); 
            $result = 'next';
            if (($start + $limit) >= $totalResults) {
                $result = 'finished';
            }
            $sno = $_POST['start'];
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
            $paymentType = [
                '0' => 'Expenses',
                '1' => 'Maturity',
                '2' => 'Pre-Maturity',
                '3' => [
                    '4' => 'Death Help',
                    '5' => 'Death Claim',
                ],
                '4' => 'Emergency Maturity',
            ];
            $sub_types = [
                '0' => 'Fresh Expense',
                '1' => 'TA Advanced',
                '2' => 'Advanced salary',
                '3' => 'Advanced Rent',
                '4' => 'Death Help',
                '5' => 'Death Claim',
            ];

            $modes = [
                0 => 'Cash',
                1 => 'Cheque',
                2 => 'Online Transfer',
                3 => 'SSB Transfer',
            ];
            $redemandDemandAdviceReason = RedemandDemandAdvice::pluck('reason','demand_id')->toArray();
            foreach ($results as $row) {
                // $row = $value[0];
                $sno ++;
                if ($row->status == 0 && $row->is_reject == 1) {
                    $status = 'Rejected';
                } else {
                    if ($row->status == 0) {
                        $status = 'Pending';
                    } else {
                        $status = 'Approved';
                    }
                }
                $loanDetail = 'NO';
                if(($row->loancount>0) || ($row->grouploancount>0))
                {
                    $loanDetail = 'Yes';
                }
                $rtgs = 'N/A';
                if ($row->id) {
                    if ($row->payment_mode == 2) {
                        $transaction = $row['demandAmountHead'];
                        if (isset($transaction->amount)) {
                            $rtgs = round($transaction->amount);
                        } else {
                            $rtgs =   'N/A';
                        }
                    } else {
                        $rtgs =   'N/A';
                    }
                } else {
                    $rtgs = 'N/A';
                }
                // by table member_loan can get due amount upto system date               
                $account = ($row->payment_type == 4 && $row->investment_id) ? $row->investmentAccountno : ($row->account_number ?? 'N/A');                
                $val = [
                    'S/No' => $sno,
                    'COMPANY NAME' => isset($row->comname) ? $row->comname : 'N/A',
                    'CUSTOMER ID' => isset($row->customerID) ? $row->customerID : 'N/A',
                    'MEMBER ID' => isset($row->memberId) ? $row->memberId : 'N/A',
                    'BR NAME' => isset($row->bname) ? $row->bname : 'N/A',
                    'BR CODE' => isset($row->branch_code) ? $row->branch_code : 'N/A',
                    'MEMBER NAME' => isset($row->memberfname) ? $row->memberfname . ' ' . $row->memberlname : 'N/A',
                    'NOMINEE NAME' => isset($row->memberNomineesName) ? $row->memberNomineesName : 'N/A',
                    'ASSOCIATE CODE' => isset($row->associateCode) ? $row->associateCode : 'N/A',
                    'ASSOCIATE NAME' => isset($row->assoFname) ? $row->assoFname . ' ' . $row->assoLname : 'N/A',
                    'AC OPENING DATE' => isset($row->m_created_at) ? (date("d/m/Y", strtotime($row->m_created_at))) : (isset($row->opening_date) ? (date("d/m/Y", strtotime($row->opening_date))) : 'N/A'),
                    'ADVICE TYPE' => isset($row->payment_type) ? (($row->payment_type == 3) ? $paymentType[$row->payment_type][$row->sub_payment_type] : $paymentType[$row->payment_type]) : 'N/A',
                    'EXPENSE TYPE' => isset($sub_types[$row->sub_payment_type]) ? $sub_types[$row->sub_payment_type] : 'N/A',
                    'DATE' => date("d/m/Y", strtotime($row->date)),
                    'VOUCHER NO' => $row->voucher_number ?? 'N/A',
                    'PAYMENT MODE' => isset($modes[$row->payment_mode]) ? $modes[$row->payment_mode] : 'N/A',
                    'PAYMENT TRF AMOUNT' => ($row->payment_type == 2) ? round($row->final_amount) : round($row->final_amount),
                    'IS LOAN' =>$loanDetail,
                    'TDS AMOUNT' => round($row->tds_amount) ?? 'N/A',
                    // 'INTEREST AMOUNT' => round($row->interestAmount) ?? 'N/A',
                    'TOTAL PAYABLE AMOUNT WITH INTEREST' => round($row->maturity_amount_till_date) ?? 'N/A',
                    'RTGS CHARGE' => $rtgs,
                    'ACCOUNT NO' => $account,
                    'SSB ACCOUNT NO' => ($row->ssb_account)??'N/A',
                    'TOTAL DEPOSIT AMOUNT' => isset($row->total_deposit_amount) ? $row->total_deposit_amount : 0, 
                    'BANK ACCOUNT NO' => isset($row->bank_account_number) ? "'" . $row->bank_account_number . "'" : "N/A",
                    'IFSC CODE' => $row->bank_ifsc ?? 'N/A',
                    'PRINT' => ($row->is_print == 0) ? 'Yes' : 'No',
                    'REQUESTED PAYMENT MODE' => $row->maturity_payment_mode ?? 'N/A',
                    'REASON' => isset($redemandDemandAdviceReason[$row->id]) ? preg_replace( "/\r|\n/", "",$redemandDemandAdviceReason[$row->id]) : 'N/A',
                    'STATUS' => $status,
                    // Rest of the key-value pairs go here...
                ];
                // pd($val);
                if (!$headerDisplayed) {
                    fputcsv($handle, array_keys($val));
                    $headerDisplayed = true;
                }
                fputcsv($handle, $val);
            }
            // Close the file
            fclose($handle);
            DB::commit();

            if ($totalResults == 0) {
                $percentage = 100;

            } else {
                $percentage = ($start + $limit) * 100 / $totalResults;
                $percentage = number_format((float) $percentage, 1, '.', '');
            }
        } catch (\Exception $e) {
            DB::rollBack();

            // Handle the exception or log an error message
            echo 'Export failed: ' . $e->getMessage();
        }
        // Output some stuff for jquery to use
        $response = array(
            'result' => $result,
            'start' => $start,
            'limit' => $limit,
            'totalResults' => $totalResults,
            'fileName' => $returnURL,
            'percentage' => $percentage,
            'tokenName' => $tokenName
        );
        echo json_encode($response);
    }
}