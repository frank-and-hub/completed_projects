<?php
namespace App\Http\Controllers\Admin\DemandAdvice;
use App\Http\Controllers\Admin\CommanController;
use App\Services\Sms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{MemberIdProof,RedemandDemandAdvice,Memberloans,Files,AccountHeads,Companies,CompanyBranch,InvestmentBalance,DemandAdviceExpense,DemandAdvice,SavingAccount,Employee,RentLiability,Memberinvestments,Branch,Daybook,SamraddhBank,SamraddhCheque,SamraddhBankClosing,BranchCash,SavingAccountTranscation,SamraddhChequeIssue,TransactionReferences,Member,TdsDeposit,InvestmentMonthlyYearlyInterestDeposits,MemberInvestmentInterest,MemberInvestmentInterestTds,Form15G,AllHeadTransaction,Grouploans};
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use URL;
use DB;
use Session;
use App\Http\Traits\IsLoanTrait;
use App\Services\ImageUpload;
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
        return view('templates.admin.demand-advice.demand-advice-listing', $data);
    }
    public function fetchBranch(Request $request)
    {
        $bankList = CompanyBranch::with('branch:id,name')->where('company_id', $request->company_id)->where('status', 1)->get(['id', 'branch_id', 'status', 'company_id']);
        $return_array = compact('bankList');
        return json_encode($return_array);
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
            $data = DemandAdvice::with('expenses', 'branch')->where('is_deleted', 0);
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['date_from'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));
                    if ($arrFormData['date_to'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereHas('expenses', function ($query) use ($startDate, $endDate) {
                        $query->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                        // $query->whereBetween(\DB::raw('DATE(demand_advices_expenses.created_at)'), [$startDate, $endDate]);
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
                    $vurl = URL::to("admin/demand-advice/view/" . $row->id . "");
                    $url = URL::to("admin/demand-advice/edit-demand-advice/" . $row->id . "");
                    $deleteurl = URL::to("admin/delete-demand-advice/" . $row->id . "");
                    /*$approveurl = URL::to("admin/approve-demand-advice/".$row->id."");*/
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $btn .= '<a class="dropdown-item" href="' . $vurl . '"><i class="fas fa-eye"></i>View</a>';
                    if ($row->is_mature == 1 && $row->status == 0) {
                        $btn .= '<a class="dropdown-item" href="' . $url . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                    }
                    /*$btn .= '<a class="dropdown-item" href="'.$approveurl.'"><i class="fas fa-thumbs-up"></i>Approve</a>';*/
                    $btn .= '<a class="dropdown-item delete-demand-advice" href="' . $deleteurl . '"><i class="fas fa-trash-alt"></i>Delete</a>';
                    $btn .= '</div></div></div>';
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
        if (check_my_permission(Auth::user()->id, "84") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Demand Advice Report';
        return view('templates.admin.demand-advice.report', $data);
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
        if ($request->ajax() && check_my_permission(Auth::user()->id, "84") == "1") {
            // $data = DemandAdvice::select('id', 'investment_id', 'maturity_payment_mode', 'tds_amount', 'maturity_amount_till_date', 'maturity_prematurity_amount', 'payment_mode', 'payment_type', 'opening_date', 'sub_payment_type', 'date', 'voucher_number', 'maturity_amount_payable', 'final_amount', 'account_number', 'bank_account_number', 'bank_ifsc', 'is_print', 'status', 'employee_id', 'branch_id', 'owner_id', 'letter_photo_id', 'interestAmount', 'company_id', 'ssb_account')
            //     ->with([
            //         'investment' => function ($q) {
            //             $q->select('id', 'member_id', 'associate_id', 'created_at', 'account_number', 'customer_id')
            //                 ->with([
            //                     'member' => function ($q) {
            //                                 $q->select('id', 'first_name', 'last_name', 'member_id', 'associate_code')
            //                                     ->with([
            //                                         'memberNomineeDetails' => function ($q) {
            //                                                                     $q->select('id', 'member_id', 'name');
            //                                                                 }
            //                                     ]);
            //                             }
            //                 ])->with([
            //                     'associateMember' => function ($q) {
            //                                 $q->select('id', 'associate_no', 'first_name', 'last_name', 'associate_code');
            //                             }
            //                 ])->with([
            //                     'ssb' => function ($q) {
            //                                 $q->select('id', 'account_no');
            //                             }
            //                 ]);
            //         }
            //     ])->with([
            //         'branch' => function ($q) {
            //             $q->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
            //         }
            //     ])->with([
            //         'company' => function ($q) {
            //             $q->select('id', 'name');
            //         }
            //     ])->with([
            //         'demandAmountHead' => function ($q) {
            //             $q->select('id', 'type_id', 'amount', 'head_id');
            //         }
            //     ])->with(['sumdeposite'])->with(['sumdeposite2'])->where('is_deleted', 0)->where('is_reject', '0')->where('payment_type', '!=', 0);
            // if (Auth::user()->branch_id > 0) {
            //     $id = Auth::user()->branch_id;
            //     $data = $data->where('branch_id', '=', $id);
            // }
            // if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
            //     if ($arrFormData['date_from'] != '') {
            //         $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));
            //         if ($arrFormData['date_to'] != '') {
            //             $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));
            //         } else {
            //             $endDate = '';
            //         }
            //         $data = $data->whereBetween(\DB::raw('DATE(date)'), [$startDate, $endDate]);
            //     }
            //     if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
            //         $branchId = $arrFormData['branch_id'];
            //         $data = $data->where('branch_id', '=', $branchId);
            //     }
            //     if (isset($arrFormData['company_id']) && $arrFormData['company_id'] != '') {
            //         $company_id = $arrFormData['company_id'];
            //         $data = $data->where('company_id', '=', $company_id);
            //     }
            //     if (isset($arrFormData['account_number']) && $arrFormData['account_number'] != '') {
            //         $account_number = $arrFormData['account_number'];
            //         if ($arrFormData['advice_type'] != '' && $arrFormData['advice_type'] == 5) {
            //             $data->whereHas('investment', function ($q) use ($account_number) {
            //                 $data = $q->where('account_number', '=', $account_number);
            //             });
            //         } else {
            //             $data = $data->where('account_number', '=', $account_number);
            //         }
            //     }
            //     if ($arrFormData['advice_type'] != '') {
            //         $advice_id = $arrFormData['advice_type'];
            //         if ($advice_id == 3) {
            //             $data = $data->where('payment_type', '=', 3)->where('death_help_catgeory', '=', 0);
            //         } elseif ($advice_id == 4) {
            //             $data = $data->where('payment_type', '=', 3)->where('death_help_catgeory', '=', 1);
            //         } elseif ($advice_id == 5) {
            //             $data = $data->where('payment_type', '=', 4);
            //         } else {
            //             $data->where('payment_type', '=', $advice_id);
            //         }
            //     }
            //     if ($arrFormData['voucher_number'] != '') {
            //         $voucher_number = $arrFormData['voucher_number'];
            //         $data = $data->where('voucher_number', '=', $voucher_number);
            //     }
            //     if ($arrFormData['status'] != '') {
            //         $status = $arrFormData['status'];
            //         $data = $data->where('status', '=', $status);
            //     }
            // }
            // $count = $data->count('id');
            // $data = $data->offset($_POST['start'])->limit($_POST['length'])->orderBy('created_at', 'DESC')->get();
            // $totalCount = $count;
            // $sno = $_POST['start'];
            // $rowReturn = array();
            // start data from view
            $fromDate = $arrFormData['date_from'];
            $toDate = $arrFormData['date_to'];
            $branchId = $arrFormData['branch_id'];
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
                    $query->where('status', $status)->where('is_reject','0');
                });
                $data->orderBy('created_at', 'DESC');
                $results = $data->get()
                ;  
                $totalResults = $results->count('id');
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
                foreach ($results->slice($start,$length) as $row) {
                    $sno++;
                    $loanDetail = (($row->loancount > 0) || ($row->grouploancount > 0)) ? 'Yes' : 'NO';
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
                    $account = ($row->payment_type == 4 && $row->investment_id) ? $row->investmentAccountno : ($row->account_number ?? 'N/A');
                    $vurl = URL::to("admin/demand-advice/view/" . $row->id . "");
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    if ($row->status == 1) {
                        if (Auth::user()->id != "13") {
                            $btn .= '<a class="dropdown-item" href="' . $vurl . '"><i class="icon-pencil7 mr-2"></i>Print</a>';
                        }
                    }
                    $btn .= '</div></div></div>';
                    $status = (($row->is_reject == 1)&&($row->status == 0)) ? 'Rejected' : ((($row->is_reject == 0)&&($row->status == 1)) ? 'Approved' : 'Pending');
                    $val = [
                        'DT_RowIndex' => $sno,
                        'company' => isset($row->comname) ? $row->comname : 'N/A',
                        'customer_id' => isset($row->customerID) ? $row->customerID : 'N/A',
                        'member_id' =>isset($row->memberId) ? $row->memberId : 'N/A',
                        'branch_name' => isset($row->bname) ? $row->bname : 'N/A',
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
                        'status' => $status,
                        'action'=> ($row->is_print == 0) ? $btn : 'N/A',
                        // Rest of the key-value pairs go here...
                    ];
                    $rowReturn[] = $val;
                }
                // pd($rowReturn);
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalResults, "recordsFiltered" => $totalResults, "data" => $rowReturn);
                return json_encode($output);
            } else {
                $output = array("draw" => $_POST['draw'], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
                return json_encode($output);
            }
        } else {
            return json_encode([]);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $token = Session::get('_fileName');
        $fromDate = $request->input('date_from');
        $toDate = $request->input('date_to');
        $startDate = '';
        $branchId = $request->input('branch_id');
        $companyId = $request->input('company_id');
        $account_number = $request->input('account_number');
        $advice_id = $request->input('advice_type');
        $advice_type_id = $request->input('expense_type');
        $voucher_number = $request->input('voucher_number');
        $status = $request->input('status');
        $tokenName = '';
        $input = $request->all();
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
        header("Content-type: text/csv");
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
                $query->where('status', $status)->where('is_reject','0');
            });
            $totalResults = $data->count('id');
            $results = $data->orderby('created_at', 'DESC')
                ->offset($start)->limit($limit)
                ->get()
                ;  
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
            foreach ($results as $row) {
                
                // $row = $value[0];
                $sno ++;
                $loanDetail = 'NO';
                if(($row->loancount>0) || $row->grouploancount>0)
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
                //code modify by sourab on 09-10-2023 for make changes on listing and export though view table demand_advice_report
                $account = ($row->payment_type == 4 && $row->investment_id) ? $row->investmentAccountno : ($row->account_number ?? 'N/A');
                $status = $row->is_reject == 1 ? 'Rejected' : (($row->status == 0) ? 'Pending' : 'Approved');
                $val = [
                    'S/No' => $sno,
                    'COMPANY NAME' => isset($row->comname) ? $row->comname : 'N/A',
                    'CUSTOMER ID' => isset($row->customerID) ? $row->customerID : 'N/A',
                    'BR NAME' => isset($row->bname) ? $row->bname : 'N/A',
                    'BR CODE' => isset($row->branch_code) ? $row->branch_code : 'N/A',
                    'SO NAME' => isset($row->sector) ? $row->sector : 'N/A',
                    'RO NAME' => isset($row->regan) ? $row->regan : 'N/A',
                    'ZO NAME' => isset($row->zone) ? $row->zone : 'N/A',
                    'DATE' => date("d/m/Y", strtotime(($row->date))),
                    'AC OPENING DATE' => isset($row->m_created_at) ? (date("d/m/Y", strtotime(convertDate($row->m_created_at)))) : (isset($row->opening_date) ? (date("d/m/Y", strtotime(convertDate($row->opening_date)))) : 'N/A'),
                    'PAYMENT TRF AMOUNT' => ($row->payment_type == 2) ? round($row->final_amount) : round($row->final_amount),
                    'ACCOUNT NO' => $account,
                    'SSB ACCOUNT NO' => ($row->ssb_account)??'N/A',
                    'MEMBER NAME' => isset($row->memberfname) ? $row->memberfname . ' ' . $row->memberlname : 'N/A',
                    'NOMINEE NAME' => isset($row->memberNomineesName) ? $row->memberNomineesName : 'N/A',
                    'ASSOCIATE CODE' => isset($row->associateCode) ? $row->associateCode : 'N/A',
                    'ASSOCIATE NAME' => isset($row->assoFname) ? $row->assoFname . ' ' . $row->assoLname : 'N/A',
                    'IS LOAN' => $loanDetail, // add by aman
                    'TOTAL DEPOSIT AMOUNT' => isset($row->total_deposit_amount) ? $row->total_deposit_amount : 0, 
                    'TDS AMOUNT' => round($row->tds_amount) ?? 'N/A',
                    'INTEREST AMOUNT' => round($row->interestAmount) ?? 'N/A',
                    'TOTAL PAYABLE AMOUNT WITH INTEREST' => round($row->maturity_amount_till_date) ?? 'N/A',
                    'ADVICE TYPE' => isset($row->payment_type) ? (($row->payment_type == 3) ? $paymentType[$row->payment_type][$row->sub_payment_type] : $paymentType[$row->payment_type]) : 'N/A',
                    'EXPENSE TYPE' => isset($sub_types[$row->sub_payment_type]) ? $sub_types[$row->sub_payment_type] : 'N/A',
                    'VOUCHER NO' => $row->voucher_number ?? 'N/A',
                    'PAYMENT MODE' => isset($modes[$row->payment_mode]) ? $modes[$row->payment_mode] : 'N/A',
                    'NEFT CHARGE' => $rtgs ?? 0,
                    'BANK ACCOUNT NO' => isset($row->bank_account_number) ? "'" . $row->bank_account_number . "'" : "N/A",
                    'IFSC CODE' => $row->bank_ifsc ?? 'N/A',
                    'PRINT' => ($row->is_print == 0) ? 'Yes' : 'No',
                    'REQUESTED PAYMENT MODE' => $row->maturity_payment_mode ?? 'N/A',
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
    public function taAdvancedListing(Request $request)
    {
        if ($request->ajax() && check_my_permission(Auth::user()->id, "87") == "1") {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = DemandAdvice::select('id', 'payment_type', 'sub_payment_type', 'employee_code', 'employee_name', 'advanced_amount', 'status', 'date', 'branch_id')->with([
                'expenses' => function ($q) {
                    $q->select('id');
                }
            ])->with([
                    'branch' => function ($q) {
                        $q->select('id', 'name');
                    }
                ])->where('payment_type', 0)->where('sub_payment_type', 1)->where('status', 1)->where('ta_advanced_adjustment', 1)->where('is_deleted', 0);
            if (Auth::user()->branch_id > 0) {
                $id = Auth::user()->branch_id;
                $data = $data->where('branch_id', '=', $id);
            }
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
            $count = $data->count('id');
            $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $totalCount = $count;
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
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
                $val['payment_type'] = $payment_type;
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
                $val['sub_payment_type'] = $sub_payment_type;
                $val['branch'] = $row['branch']->name;
                $val['employee_code'] = $row->employee_code;
                $val['employee_name'] = $row->employee_name;
                $val['advanced_amount'] = $row->advanced_amount;
                if ($row->status == 0) {
                    $status = 'Pending';
                } else {
                    $status = 'Approved';
                }
                $val['status'] = $status;
                $val['created_at'] = date("d/m/Y", strtotime($row->date));
                $url = URL::to("admin/demand-advice/adjust-ta-advanced/" . $row->id . "");
                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                $btn .= '<a class="dropdown-item" href="' . $url . '"><i class="icon-pencil7 mr-2"></i>Adjestment</a>';
                $btn .= '</div></div></div>';
                $val['action'] = $btn;
                $rowReturn[] = $val;
            }
            $output = array("branch_id" => Auth::user()->branch_id, "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
            return json_encode($output);
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
        if (check_my_permission(Auth::user()->id, "133") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Add Demand Advice';
        $data['expenseCategories'] = AccountHeads::select('id', 'sub_head')->where('parent_id', 4)->where('status', 0)->get();
        $data['expenseSubCategories'] = AccountHeads::select('id', 'sub_head', 'parent_id')->whereIn('parent_id', array(14, 86))->whereNotIn('id', array(37, 40, 53, 87, 88, 92))->where('status', 0)->get();
        $data['liabilityHeads'] = array('');
        $data['rentOwners'] = RentLiability::select('id', 'owner_name')->where('status', 0)->get();
        return view('templates.admin.demand-advice.add-demand-advice', $data);
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
            'branch_id' => 'required',
            //'date' => 'required',
            //'expenseType' => 'required',
        ];
        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];
        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {
            $voucherRecord = DemandAdvice::whereNotNull('mi_code')->orderby('id', 'desc')->where('is_deleted', 0)->first('mi_code');
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
                    'branch_id' => $request->branch_id,
                    'mi_code' => $miCode,
                    'voucher_number' => $voucherNumber,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->date))),
                    'created_at' => $request->created_at,
                ];
                $demandAdvice = DemandAdvice::create($daData);
                $demandAdviceId = $demandAdvice->id;
                foreach ($request->fresh_expense as $key => $value) {
                    if (isset($value['bill_photo'])) {
                        $mainFolder = '/demand-advice/expense';
                        $file = $value['bill_photo'];
                        $uploadFile = $file->getClientOriginalName();
                        $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                        $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                        ImageUpload::upload($file, $mainFolder, $fname);
                        // $file->move($mainFolder, $fname);
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
                    'branch_id' => $request->branch_id,
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
                    $mainFolder = storage_path() . '/images/demand-advice/advancedsalary';
                    $file = $request->advanced_salary_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($mainFolder, $fname);
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
                    'branch_id' => $request->branch_id,
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
                    'branch_id' => $request->branch_id,
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
                    // $file = $request->maturity_letter_photo;
                    // $uploadFile = $file->getClientOriginalName();
                    // $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    // $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $mainFolder = '/demand-advice/maturity-prematurity';
                    $file = $request->maturity_letter_photo;
                    ;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    ImageUpload::upload($file, $mainFolder, $fname);
                    // $file->move($mainFolder, $fname);
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
                    'branch_id' => $request->branch_id,
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
                    $mainFolder = '/demand-advice/maturity-prematurity';
                    $file = $request->prematurity_letter_photo;
                    ;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    ImageUpload::upload($file, $mainFolder, $fname);
                    // $file = $request->prematurity_letter_photo;
                    // $uploadFile = $file->getClientOriginalName();
                    // $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    // $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    // $file->move($mainFolder, $fname);
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
                    'branch_id' => $request->branch_id,
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
                    $mainFolder = '/demand-advice/death-help';
                    $file = $request->death_help_letter_photo;
                    ;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    ImageUpload::upload($file, $mainFolder, $fname);
                    // $file = $request->death_help_letter_photo;
                    // $uploadFile = $file->getClientOriginalName();
                    // $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    // $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    // $file->move($mainFolder, $fname);
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
                    'branch_id' => $request->branch_id,
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
            return redirect()->route('admin.demand.application')->with('success', 'Demand Advice Added Successfully!');
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
        $data['row'] = DemandAdvice::with('investment', 'expenses', 'branch')->where('id', $id)->where('is_deleted', 0)->first();
        return view('templates.admin.demand-advice.print-demand-advice', $data);
    }
    /**
     * Demand Advice View.
     * Route: /member/passbook
     * Method: get sss
     * @return  array()  Response
     */
    public function viewTaAdvanced()
    {
        if (check_my_permission(Auth::user()->id, "87") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'View TA advance and Imprest Advice';
        return view('templates.admin.demand-advice.view_ta_advanced', $data);
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
        $data['expenseSubCategories'] = AccountHeads::select('id', 'sub_head', 'parent_id')->whereIn('parent_id', array(14, 86))->whereNotIn('id', array(37, 40, 53, 87, 88, 92))->where('status', 0)->get();
        $data['liabilityHeads'] = array('');
        $data['rentOwners'] = RentLiability::select('id', 'owner_name')->where('status', 0)->get();
        $data['subCategory1'] = AccountHeads::where('parent_id', 86)->get();
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
        $data['demandAdvice'] = DemandAdvice::with('expenses', 'branch')->where('id', $id)->where('is_deleted', 0)->first();
        $data['investmentDetails'] = Memberinvestments::with('plan', 'member', 'ssb', 'memberBankDetail')->where('id', $data['demandAdvice']->investment_id)->first();
        return view('templates.admin.demand-advice.edit-demand-advice', $data);
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
        $data['demandAdvice'] = DemandAdvice::select('id', 'payment_type', 'sub_payment_type', 'advanced_amount', 'particular', 'branch_id')
            ->with([
                'expenses' => function ($q) {
                    $q->select('id', 'demand_advice_id');
                }
            ])
            ->with([
                'branch' => function ($q) {
                    $q->select('id');
                }
            ])
            ->where('id', $id)->where('is_deleted', 0)->first();
        $data['cBanks'] = SamraddhBank::select('id', 'bank_name')
            ->with([
                'bankAccount' => function ($q) {
                    $q->select('id', 'account_no', 'bank_id');
                }
            ])->where("status", "1")->get();
        $data['cheques'] = SamraddhCheque::select('cheque_no', 'account_id')->where('status', 1)->get();
        return view('templates.admin.demand-advice.adjust_ta_advanced', $data);
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
            'branch_id' => 'required',
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
                    'branch_id' => $request->branch_id,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->date))),
                    'updated_at' => $request->created_at,
                ];
                $demandAdvice = DemandAdvice::find($request->demand_advice_id);
                $demandAdvice->update($daData);
                foreach ($request->fresh_expense as $key => $value) {
                    if ($value['id'] == '') {
                        if (isset($value['bill_photo'])) {
                            $mainFolder = storage_path() . '/images/demand-advice/expense';
                            $file = $value['bill_photo'];
                            $uploadFile = $file->getClientOriginalName();
                            $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                            $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                            $file->move($mainFolder, $fname);
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
                        ];
                        $demandAdvice = DemandAdviceExpense::create($feData);
                    } else {
                        if (isset($value['bill_photo']) && isset($value['file_id'])) {
                            $hiddenFileId = $value['file_id'];
                            $mainFolder = storage_path() . '/images/demand-advice/expense';
                            $file = $value['bill_photo'];
                            $uploadFile = $file->getClientOriginalName();
                            $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                            $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                            $file->move($mainFolder, $fname);
                            $data = [
                                'file_name' => $fname,
                                'file_path' => $mainFolder,
                                'file_extension' => $file->getClientOriginalExtension(),
                            ];
                            $fileRes = Files::find($hiddenFileId);
                            $fileRes->update($data);
                            $feData = [
                                'category' => $value['expenseCategory'],
                                'subcategory' => $value['expenseSubCategory'],
                                'party_name' => $value['party_name'],
                                'particular' => $value['particular'],
                                'mobile_number' => $value['mobile_number'],
                                'amount' => $value['amount'],
                                'bill_number' => $value['billNumber'],
                            ];
                        } elseif (isset($value['bill_photo'])) {
                            $mainFolder = storage_path() . '/images/demand-advice/expense';
                            $file = $value['bill_photo'];
                            $uploadFile = $file->getClientOriginalName();
                            $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                            $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                            $file->move($mainFolder, $fname);
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
                    'branch_id' => $request->branch_id,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->date))),
                    'employee_id' => $request->ta_employee_id,
                    'employee_name' => $request->ta_employee_name,
                    'particular' => $request->ta_particular,
                    'advanced_amount' => $request->ta_advance_amount,
                    'updated_at' => $request->created_at,
                ];
                $demandAdvice = DemandAdvice::find($request->demand_advice_id);
                $demandAdvice->update($daData);
            } elseif ($request->paymentType == 0 && $request->expenseType == 2) {
                if (isset($request->advanced_salary_letter_photo) && $request->old_advanced_salary_letter_photo == '') {
                    $mainFolder = storage_path() . '/images/demand-advice/advancedsalary';
                    $file = $request->advanced_salary_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($mainFolder, $fname);
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $res = Files::create($fData);
                    $file_id = $res->id;
                } elseif (isset($request->advanced_salary_letter_photo) && $request->old_advanced_salary_letter_photo != '') {
                    $hiddenFileId = $request->old_advanced_salary_letter_photo;
                    $mainFolder = storage_path() . '/images/demand-advice/advancedsalary';
                    $file = $request->advanced_salary_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($mainFolder, $fname);
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
                    'branch_id' => $request->branch_id,
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
                ];
                $demandAdvice = DemandAdvice::find($request->demand_advice_id);
                $demandAdvice->update($daData);
            } elseif ($request->paymentType == 0 && $request->expenseType == 3) {
                $daData = [
                    'payment_type' => $request->paymentType,
                    'sub_payment_type' => $request->expenseType,
                    'branch_id' => $request->branch_id,
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
                ];
                $demandAdvice = DemandAdvice::find($request->demand_advice_id);
                $demandAdvice->update($daData);
            } elseif ($request->paymentType == 2 && $request->maturity_prematurity_type == 0) {
                if (isset($request->maturity_letter_photo) && $request->old_maturity_letter_photo == '') {
                    $mainFolder = storage_path() . '/images/demand-advice/maturity-prematurity';
                    $file = $request->maturity_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($mainFolder, $fname);
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $res = Files::create($fData);
                    $file_id = $res->id;
                } elseif (isset($request->maturity_letter_photo) && $request->old_maturity_letter_photo != '') {
                    $hiddenFileId = $request->old_maturity_letter_photo;
                    $mainFolder = storage_path() . '/images/demand-advice/maturity-prematurity';
                    $file = $request->maturity_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($mainFolder, $fname);
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
                    'branch_id' => $request->branch_id,
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
                    'updated_at' => $request->created_at,
                ];
                if (isset($request->payment_mode) && $request->payment_mode != "") {
                    $daData["maturity_payment_mode"] = $request->payment_mode;
                }
                $demandAdvice = DemandAdvice::find($request->demand_advice_id);
                $demandAdvice->update($daData);
            } elseif ($request->paymentType == 2 && $request->maturity_prematurity_type == 1) {
                if (isset($request->prematurity_letter_photo) && $request->old_prematurity_letter_photo == '') {
                    $mainFolder = storage_path() . '/images/demand-advice/maturity-prematurity';
                    $file = $request->prematurity_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($mainFolder, $fname);
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $res = Files::create($fData);
                    $file_id = $res->id;
                } elseif (isset($request->prematurity_letter_photo) && $request->old_prematurity_letter_photo != '') {
                    $hiddenFileId = $request->old_prematurity_letter_photo;
                    $mainFolder = storage_path() . '/images/demand-advice/maturity-prematurity';
                    $file = $request->prematurity_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($mainFolder, $fname);
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
                    'branch_id' => $request->branch_id,
                    'date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->maturity_prematurity_date))),
                    'letter_photo_id' => $file_id,
                    // 'investment_id' => $request->prematurity_investmnet_id,
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
                ];
                if (isset($request->payment_mode) && $request->payment_mode != "") {
                    $daData["maturity_payment_mode"] = $request->payment_mode;
                }
                $demandAdvice = DemandAdvice::find($request->demand_advice_id);
                $demandAdvice->update($daData);
            } elseif ($request->paymentType == 4) {
                if (isset($request->death_help_letter_photo) && $request->old_death_help_letter_photo == '') {
                    $mainFolder = storage_path() . '/images/demand-advice/death-help';
                    $file = $request->death_help_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($mainFolder, $fname);
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $res = Files::create($fData);
                    $file_id = $res->id;
                } elseif (isset($request->death_help_letter_photo) && $request->old_death_help_letter_photo != '') {
                    $hiddenFileId = $request->old_death_help_letter_photo;
                    $mainFolder = storage_path() . '/images/demand-advice/death-help';
                    $file = $request->death_help_letter_photo;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($mainFolder, $fname);
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
                    'branch_id' => $request->branch_id,
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
                ];
                if (isset($request->payment_mode) && $request->payment_mode != "") {
                    $daData["maturity_payment_mode"] = $request->payment_mode;
                }
                $demandAdvice = DemandAdvice::find($request->demand_advice_id);
                $demandAdvice->update($daData);
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        if ($request->demand_advice_id) {
            return redirect()->route('admin.demand.application')->with('success', 'Update was Successful!');
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
            //echo $request->adjustment_level; die;
            $ssbArray = array();
            $entryTime = date("H:i:s");
            Session::put('created_at', date("Y-m-d", strtotime(convertDate($request->payment_date))));
            $request['created_at'] = date("Y-m-d", strtotime(convertDate($request->payment_date)));
            foreach ($request->ta_expense as $key => $value) {
                if (isset($value['bill_photo'])) {
                    $mainFolder = storage_path() . '/images/demand-advice/expense';
                    $file = $value['bill_photo'];
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($mainFolder, $fname);
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
            $demandAdviceTaAdvanced = DemandAdvice::with('employee')->where('id', $request->demand_advice_id)->where('is_deleted', 0)->first();
            $taAdvanced = DemandAdviceExpense::where('demand_advice_id', $request->demand_advice_id)->get();
            $sumAmount = DemandAdviceExpense::where('demand_advice_id', $request->demand_advice_id)->sum('amount');
            $request['branch_id'] = $demandAdviceTaAdvanced->branch_id;
            $employeeAdvancedSalary = $demandAdviceTaAdvanced['employee']->advance_payment - $demandAdviceTaAdvanced->advanced_amount;
            $employeeCurrentBalance = $demandAdviceTaAdvanced['employee']->current_balance - $demandAdviceTaAdvanced->advanced_amount;
            $advancedSalaryUpdate = Employee::where('id', $demandAdviceTaAdvanced->employee_id)->update(['advance_payment' => $employeeAdvancedSalary, 'current_balance' => $employeeCurrentBalance]);
            $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id', 'balance', 'branch_id', 'branch_code', 'member_id', 'account_no')->where('account_no', $demandAdviceTaAdvanced['employee']->ssb_account)->first();
            if ($request->amount_mode == 2) {
                if ($request->mode == 3) {
                    SamraddhCheque::where('cheque_no', $request->cheque_number)->update(['status' => 3, 'is_use' => 1]);
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
                if ($request->amount_mode == 0 || $request->amount_mode == '') {
                    $branch_id = $request->branch_id;
                    $type = 13;
                    $sub_type = 132;
                    $jv_unique_id = NULL;
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
                    $cheque_type = NULL;
                    $cheque_id = NULL;
                    $cheque_no = NULL;
                    $cheque_date = NULL;
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
                    $ssb_account_tran_id_to = NULL;
                    $ssb_account_tran_id_from = NULL;
                    $transction_bank_from_ac_id = NULL;
                    $saToTranctionId = NULL;
                } elseif ($request->amount_mode == 1) {
                    $transction_bank_from_ac_id = NULL;
                    $saToTranctionId = NULL;
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
                    $payment_type = 'CR';
                    $payment_mode = 3;
                    $currency_code = 'INR';
                    $amount_from_id = NULL;
                    $amount_from_name = NULL;
                    $v_no = $vno;
                    $v_date = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request->created_at)));
                    $jv_unique_id = NULL;
                    $ssb_account_id_from = NULL;
                    $cheque_type = NULL;
                    $cheque_id = NULL;
                    $cheque_no = NULL;
                    $cheque_date = NULL;
                    $cheque_bank_to_name = NULL;
                    $cheque_bank_to_branch = NULL;
                    $cheque_bank_from = NULL;
                    $cheque_bank_from_id = NULL;
                    $cheque_bank_ac_from = NULL;
                    $cheque_bank_ac_from_id = NULL;
                    $cheque_bank_to_ac_no = NULL;
                    $cheque_bank_ifsc_from = NULL;
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = NULL;
                    $cheque_bank_ac_to = NULL;
                    $cheque_bank_to_ifsc = NULL;
                    $transction_no = NULL;
                    $transction_bank_from = NULL;
                    $transction_bank_from_id = NULL;
                    $transction_bank_ac_from = NULL;
                    $transction_bank_ifsc_from = NULL;
                    $transction_bank_branch_from = NULL;
                    $transction_bank_from_ac_id = NULL;
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
                    $ssb_account_tran_id_to = NULL;
                    $ssb_account_tran_id_from = NULL;
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
                } elseif ($request->amount_mode == 2) {
                    $saToTranctionId = NULL;
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
                    $payment_type = 'DR';
                    $currency_code = 'INR';
                    $amount_from_id = $request->branch_id;
                    $amount_from_name = getBranchDetail($demandAdviceTaAdvanced->branch_id)->name;
                    $v_no = NULL;
                    $v_date = NULL;
                    $jv_unique_id = NULL;
                    $ssb_account_id_from = NULL;
                    if ($request->mode == 3) {
                        $cheque_type = 1;
                        $cheque_id = getSamraddhChequeData($request->cheque_number)->id;
                        $cheque_no = $request->cheque_number;
                        $cheque_date = getSamraddhChequeData($request->cheque_number)->cheque_create_date;
                        $cheque_bank_from = $transction_bank_from_ac_id = $request->bank;
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
                        SamraddhCheque::where('cheque_no', $request->cheque_number)->update(['status' => 3, 'is_use' => 1]);
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
                        $transction_bank_from_id = $transction_bank_from_ac_id = getSamraddhBank($request->bank)->id;
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
                        $transaction_charge = $request->neft_charge ?? 0;
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
                    $ssb_account_tran_id_to = NULL;
                    $ssb_account_tran_id_from = NULL;
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
                $dayBookRef = CommanController::createBranchDayBookReference($amount);
                $this->employeeSalaryLeaser($demandAdviceTaAdvanced->employee_id, $branch_id, 6, $type_id, $employeeCurrentBalance, NULL, $demandAdviceTaAdvanced->advanced_amount, 'TA Advanced amount A/C Dr ' . $demandAdviceTaAdvanced->advanced_amount . '', $currency_code, 'Dr', $payment_mode, 1, date("Y-m-d " . $entryTime . "", strtotime(convertDate($request->created_at))), $updated_at = NULL, $v_no, $v_date, $ssb_account_id_to, $ssb_account_id_from, $to_bank_name, $to_bank_branch, $to_bank_ac_no, $to_bank_ifsc, $to_bank_id, $to_bank_account_id, $from_bank_name, $from_bank_branch, $from_bank_ac_no, $from_bank_ifsc, $from_bank_id, $from_bank_ac_id, $cheque_id, $cheque_no, $cheque_date, $transction_no, $transaction_date, $transaction_charge);
                $this->employeeLedgerBackDateDR($demandAdviceTaAdvanced->employee_id, $request->created_at, $demandAdviceTaAdvanced->advanced_amount);
                $memberTransaction = CommanController::memberTransactionNew($dayBookRef, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $demandAdviceTaAdvanced->advanced_amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 72, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $taAdvancedAmount, $taAdvancedAmount, $taAdvancedAmount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $created_by, $created_by_id);
                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,29,72,$head5=NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$taAdvancedAmount,$taAdvancedAmount,$taAdvancedAmount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/
                if ($request->amount_mode == 0 && ($request->adjustment_level == 1 || $request->adjustment_level == 2)) {
                    if ($payment_type == 'CR') {
                        $pType = 'DR';
                    } else {
                        $pType = 'CR';
                    }
                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 28, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $pType, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $created_by, $created_by_id);
                    /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/
                    $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
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
                    $ssbCreateTran = CommanController::createTransaction($satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->member_id, $ssbAccountDetails->branch_id, $ssbAccountDetails->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $ssbAccountDetails->member_id, $ssbAccountDetails->account_no, $cheque_dd_no = NULL, $bank_name = NULL, $branch_name = NULL, date("Y-m-d", strtotime(convertDate($request->created_at))), $online_payment_id = NULL, $online_payment_by = NULL, $ssbAccountDetails->account_no, 'CR');
                    $description = $description;
                    $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 1, $ssbAccountDetails->id, NULL, $ssbAccountDetails->member_id, $request->maturity_amount_payable + $ssbAccountDetails->balance, $amount, $withdrawal = 0, $description, $ssbAccountDetails->account_no, $ssbAccountDetails->branch_id, $ssbAccountDetails->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $ssbAccountDetails->member_id, $ssbAccountDetails->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($request->created_at))), NULL, $online_payment_by = NULL, $ssbAccountDetails->account_no, 'CR');
                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 56, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to, $saToTranctionId, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $created_by, $created_by_id);
                    /*$allTransaction = $this->createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,20,56,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$saToId,$saToTranctionId,NULL);*/
                    $memberTransaction = CommanController::memberTransactionNew($dayBookRef, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $amount, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $saToTranctionId, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                }
                foreach ($taAdvanced as $key => $expvalue) {
                    $head1 = 4;
                    $head2 = 86;
                    $head3 = $expvalue->subcategory;
                    $head4 = NULL;
                    $head5 = NULL;
                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $head3, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $taAmount, $taAmount, $taAmount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to, $saToTranctionId, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $created_by, $created_by_id);
                    /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$taAmount,$taAmount,$taAmount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/
                }
                if ($request->amount_mode == 2) {
                    if ($request->mode == 4) {
                        $bankAmount = $amount + $request->neft_charge ?? 0;
                    } else {
                        $bankAmount = $amount;
                    }
                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, getSamraddhBank($request->bank)->account_head_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $bankAmount, $bankAmount, $bankAmount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to, $saToTranctionId, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $created_by, $created_by_id);
                    /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,27,getSamraddhBank($request->bank)->account_head_id,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$bankAmount,$bankAmount,$bankAmount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/
                    $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($dayBookRef, $bank_id, $bank_ac_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $opening_balance, $bankAmount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id, $cheque_type, $cheque_id, $ssb_account_tran_id_to, $ssb_account_tran_id_from);
                    $bankClosing = $this->updateBankClosingDR($bank_id, $bank_ac_id, $request->created_at, $bankAmount, 0);
                }
                if ($request->amount_mode == 2 && $request->mode == 4) {
                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 92, $type, 142, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $request->neft_charge, $request->neft_charge, $request->neft_charge, 'NEFT Charge A/c Cr ' . $request->neft_charge . '', 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to, $saToTranctionId, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $created_by, $created_by_id);
                    /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,92,$head4=NULL,$head5=NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge,$request->neft_charge,$request->neft_charge,'NEFT Charge A/c Cr '.$request->neft_charge.'','CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/
                    $updateBackDateloanBankBalance = CommanController::updateBackDateloanBankBalance($amount, $request->bank, getSamraddhBankAccount($request->bank_account_number)->id, $request->created_at, $request->neft_charge);
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        if (count($ssbArray) > 0) {
            return redirect()->route('admin.damandadvice.viewtaadvanced')->with('success', 'Ta Advanced Successfully Adjustment AND ' . $ssbString . ' demand advice not have any ssb account!');
        } else {
            return redirect()->route('admin.damandadvice.viewtaadvanced')->with('success', 'Ta Advanced Successfully Adjustment!');
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
        $demandStatus = DemandAdvice::select('status')->where('id', $id)->where('is_deleted', 0)->first();
        $adata = DemandAdvice::findOrFail($id);
        if ($demandStatus->status == 0) {
            $adata->status = 1;
        } else {
            $adata->status = 0;
        }
        $adata = $adata->save();
        if ($adata) {
            return redirect()->route('admin.demand.advices')->with('success', 'Demand advice approved successfully!');
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
        $demandAdvice = DemandAdvice::select('payment_type')->where('id', $id)->where('is_deleted', 0)->first();
        if ($demandAdvice->payment_type == 0) {
            $deleteExpense = DemandAdviceExpense::where('demand_advice_id', $id)->delete();
            $deleteDemandAdvice = DemandAdvice::where('id', $id)->delete();
        } elseif ($demandAdvice->payment_type == 1 || $demandAdvice->payment_type == 2 || $demandAdvice->payment_type == 3 || $demandAdvice->payment_type == 4) {
            $deleteDemandAdvice = DemandAdvice::where('id', $id)->delete();
        }
        return back()->with('success', 'Demand advice deleted successfully!');
    }
    /**
     * Delete demand advice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteMultiple(Request $request)
    {
        $sRecord = explode(',', $request['select_deleted_records']);
        foreach ($sRecord as $key => $value) {
            $demandAdvice = DemandAdvice::select('payment_type')->where('id', $value)->where('is_deleted', 0)->first();
            if ($demandAdvice->payment_type == 0) {
                $deleteExpense = DemandAdviceExpense::where('demand_advice_id', $value)->delete();
                $deleteDemandAdvice = DemandAdvice::where('id', $value)->delete();
            } elseif ($demandAdvice->payment_type == 1 || $demandAdvice->payment_type == 2 || $demandAdvice->payment_type == 3) {
                $deleteDemandAdvice = DemandAdvice::where('id', $value)->delete();
            }
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
        $ownerDetails = RentLiability::select('owner_mobile_number', 'owner_ssb_number', 'owner_bank_name', 'owner_bank_account_number', 'owner_bank_ifsc_code', 'created_at', 'agreement_from')->where('id', $ownerId)->where('status', 0)->first();
        $return_array = compact('ownerDetails');
        return json_encode($return_array);
    }
    // public function getInvestmentDetails(Request $request)
    // {
    //     $investmentAccount = $request->val;
    //     $type = $request->type;
    //     $subtype = $request->subtype;
    //     $companyId = $request->company_id;
    //     $cDate = date("Y-m-d");
    //     if($type == 4 && $subtype == 0){
    //         $investmentDetails = Memberinvestments::with('investmentNomiees','plan','member','ssb','memberBankDetail','investmentNomiees')->where('account_number',$investmentAccount)->where('account_number', 'not like', "%R-%")->where('plan_id',6)->where('is_mature',1)->where('investment_correction_request',0)->where('renewal_correction_request',0)->getCompanyRecords('CompanyId',$companyId)->first();
    //         if($investmentDetails){
    //             $message = '';
    //             $status = 200;
    //         }else{
    //             $message = 'Record Not Found!';
    //             $status = 400;
    //         }
    //     }elseif($type == 4 && $subtype == 1){
    //         $investmentDetails = Memberinvestments::with('investmentNomiees','plan','member','ssb','memberBankDetail','investmentNomiees')->where('account_number',$investmentAccount)->where('account_number', 'not like', "%R-%")->whereNotIn('plan_id',[1,6])->where('is_mature',1)->where('investment_correction_request',0)->where('renewal_correction_request',0)->getCompanyRecords('CompanyId',$companyId)->first();
    //         if($investmentDetails){
    //             $message = '';
    //             $status = 200;
    //         }else{
    //             $message = 'Record Not Found!';
    //             $status = 400;
    //         }
    //     }
    //     //maturity
    //     elseif($type == 2 && $subtype == 0){
    //         $investmentDetails = Memberinvestments::with('investmentNomiees','plan','member','ssb','memberBankDetail','investmentNomiees')->where('account_number',$investmentAccount)->where('account_number', 'not like', "%R-%")->whereNotIn('plan_id',[1,6])->where('is_mature',1)->where('investment_correction_request',0)->where('renewal_correction_request',0)->first();
    //         dd( $investmentDetails);
    //         if($investmentDetails){
    //             $maturityDate =  date('Y-m-d', strtotime($investmentDetails->created_at. ' + '.($investmentDetails->tenure).' year'));
    //             $currentDate=date_create($cDate);
    //             $diff = strtotime($maturityDate) - strtotime($cDate);
    //             $daydiff = abs(round($diff / 86400));
    //             if($cDate < $maturityDate ){
    //                     $message = 'You Cannot Mature Plan before Maturity Date!';
    //                 $status = 500;
    //             }else{
    //                 $message = '';
    //                 $status = 200;
    //             }
    //         }else{
    //             $message = 'Record Not Found!';
    //             $status = 400;
    //         }
    //     }
    //      //pre - maturity
    //     elseif($type == 2 && $subtype == 1){
    //         $investmentDetails = Memberinvestments::with('investmentNomiees','plan','member','ssb','memberBankDetail','investmentNomiees')->where('account_number',$investmentAccount)->where('account_number', 'not like', "%R-%")/*->whereIn('plan_id',[4,5])*/->where('is_mature',1)->where('investment_correction_request',0)->where('renewal_correction_request',0)->getCompanyRecords('CompanyId',$companyId)->first();
    //         $arr = [4,5];
    //         if(!in_array($investmentDetails->plan_id,$arr))
    //         {
    //             $message ='Prematurity option not available for this plan!';
    //             $status = 500;
    //         }
    //         // else if($investmentDetails->branch_id != $branch){
    //         //     $message = 'Selected Branch Should be same as Investment Branch!';
    //         //     $status = 400;
    //         // }
    //         else{
    //             $date =  date('Y-m-d', strtotime($investmentDetails->created_at));
    //             $newdate =  date('Y-m-d', strtotime($date.'+ 1 year'));
    //             $requestDate =  date('Y-m-d', strtotime(convertDate($request->date)));
    //             if($requestDate == '1970-01-01' || $requestDate == '')
    //             {
    //                 $message = 'Please Select Date!';
    //                 $status = 500;
    //             }
    //             else{
    //                 if($investmentDetails->plan_id == 4 || $investmentDetails->plan_id == 5 )
    //                 {
    //                     if($requestDate < $newdate)
    //                     {
    //                         $message ='You Cannot Mature Plan before 1 Year  !';
    //                         $status = 500;
    //                     }
    //                     // else if($investmentDetails->branch_id != $branch){
    //                     //     $message = 'Selected Branch Should be same as Investment Branch!';
    //                     //     $status = 400;
    //                     // }
    //                     else{
    //                         if($investmentDetails){
    //                             $message = '';
    //                             $status = 200;
    //                         }else{
    //                             $message = 'Record Not Found!';
    //                             $status = 400;
    //                         }
    //                     }
    //                 }
    //                 // if($investmentDetails){
    //                 //     $message = '';
    //                 //     $status = 200;
    //                 // }else{
    //                 //     $message = 'Record Not Found!';
    //                 //     $status = 400;
    //                 // }
    //             }
    //         }
    //     }
    //     if($investmentDetails && $status == 200){
    //         $demandAdviceRecord = DemandAdvice::where('investment_id',$investmentDetails->id)->where('is_deleted',0)->count();
    //         if($demandAdviceRecord > 0){
    //             $isDefaulter = 0;
    //             $finalAmount = 0;
    //             $message = 'Already request created for this plan!';
    //             $status = 500;
    //         }else{
    //             $mInvestment = $investmentDetails;
    //             $maturity_date =  date('Y-m-d', strtotime($mInvestment->created_at. ' + '.($mInvestment->tenure).' year'));
    //             $investmentData = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereIn('transaction_type', [2,4])->whereDate('created_at','<=',$maturity_date)->orderby('created_at', 'asc')->get();
    //             $keyVal = 0;
    //             $cInterest = 0;
    //             $regularInterest = 0;
    //             $total = 0;
    //             $collection = 0;
    //             $monthly = array(10,11);
    //             $daily = array(7);
    //             $preMaturity = array(4,5);
    //             $fixed = array(8,9);
    //             $samraddhJeevan = array(2,6);
    //             $moneyBack = array(3);
    //             $totalDeposit = 0;
    //             $totalInterestDeposit = 0;
    //             if(in_array($mInvestment->plan_id, $monthly)){
    //                 if($investmentData){
    //                     $investmentMonths = $mInvestment->tenure*12;
    //                     $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                     for ($i=1; $i <= $investmentMonths ; $i++){
    //                             $val = $mInvestment;
    //                             $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
    //                             $cMonth = date('m');
    //                             $cYear = date('Y');
    //                             $cuurentInterest = $mInvestment->interest_rate;
    //                             $totalDeposit = $totalInvestmentAmount;
    //                             $previousRecord = Daybook::select('deposit','created_at')->whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<', $nDate)->max('created_at');
    //                             $sumPreviousRecordAmount = Daybook::whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<=', $nDate)->sum('deposit');
    //                             $d1 = explode('-',$mInvestment->created_at);
    //                             $d2 = explode('-',$nDate);
    //                             $ts1 = strtotime($mInvestment->created_at);
    //                             $ts2 = strtotime($nDate);
    //                             $year1 = date('Y', $ts1);
    //                             $year2 = date('Y', $ts2);
    //                             $month1 = date('m', $ts1);
    //                             $month2 = date('m', $ts2);
    //                             $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
    //                             if($cMonth > $d2[1] && $cYear > $d2[0]){
    //                                 if($previousRecord){
    //                                     $previousDate = explode('-',$previousRecord);
    //                                     $previousMonth = $previousDate[1];
    //                                     if(($secondMonth-$previousMonth) >= 3 && $sumPreviousRecordAmount < $mInvestment->deposite_amount*$monthDiff){
    //                                         $defaulterInterest = 1.50;
    //                                         $isDefaulter = 1;
    //                                     }else{
    //                                         $defaulterInterest = 0;
    //                                         $isDefaulter = 0;
    //                                     }
    //                                 }else{
    //                                     $defaulterInterest = 0;
    //                                     $isDefaulter = 0;
    //                                 }
    //                             }else{
    //                                 $defaulterInterest = 0;
    //                                 $isDefaulter = 1;
    //                             }
    //                             $cfAmount = Memberinvestments::where('id',$val->id)->first();
    //                             if($val->deposite_amount*$monthDiff <= $totalDeposit){
    //                                 $extraAmount = (int) $totalDeposit-(int) ($val->deposite_amount*$monthDiff);
    //                                 $cfdAmount = (int) $extraAmount+(int) $cfAmount->carry_forward_amount;
    //                                 Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
    //                                 $aviAmount = $val->deposite_amount;
    //                                 $total = $total+$val->deposite_amount;
    //                                 if($monthDiff % 3 == 0 && $monthDiff != 0){
    //                                     $total = $total+$regularInterest;
    //                                     $cInterest = $regularInterest;
    //                                 }else{
    //                                     $total = $total;
    //                                     $cInterest = 0;
    //                                 }
    //                                 $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
    //                                 $addInterest = ($cuurentInterest-$defaulterInterest);
    //                                 $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
    //                                 $interest = number_format((float)$a, 2, '.', '');
    //                                 $totalInterestDeposit = $totalInterestDeposit+$interest;
    //                             }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
    //                                 $pendingAmount =  ($val->deposite_amount*12)-$totalDeposit;
    //                                 if((int) $cfAmount->carry_forward_amount >= (int) $pendingAmount){
    //                                     $cfdAmount = (int) $cfAmount->carry_forward_amount-(int) $pendingAmount;
    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
    //                                     $collection = (int) $totalDeposit+(int) $pendingAmount;
    //                                 }elseif((int) $cfAmount->carry_forward_amount == (int) $pendingAmount){
    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
    //                                     $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
    //                                 }elseif((int) $pendingAmount > (int) $cfAmount->carry_forward_amount){
    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
    //                                     $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
    //                                 }
    //                                 $checkAmount = $collection+($totalDeposit-$val->deposite_amount*($monthDiff-1));
    //                                 if($checkAmount > 0){
    //                                     $aviAmount = $checkAmount;
    //                                     $total = $total+$checkAmount;
    //                                     if($monthDiff % 3 == 0 && $monthDiff != 0){
    //                                         $total = $total+$regularInterest;
    //                                         $cInterest = $regularInterest;
    //                                     }else{
    //                                         $total = $total;
    //                                         $cInterest = 0;
    //                                     }
    //                                     $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
    //                                     $addInterest = ($cuurentInterest-$defaulterInterest);
    //                                     $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
    //                                     $interest = number_format((float)$a, 2, '.', '');
    //                                 }else{
    //                                     $aviAmount = 0;
    //                                     $total = 0;
    //                                     $cuurentInterest = 0;
    //                                     $interest = 0;
    //                                     $addInterest = 0;
    //                                 }
    //                                 $totalInterestDeposit = $totalInterestDeposit+$interest;
    //                             }
    //                     }
    //                     $interstAmount = round($totalInterestDeposit);
    //                     if($request->type == 2){
    //                         $finalAmount = round(($totalDeposit+$totalInterestDeposit)-(1.5*($totalDeposit+$totalInterestDeposit)/100));
    //                     }else{
    //                         $finalAmount = round($totalDeposit+$totalInterestDeposit);
    //                         $investAmount = $totalDeposit;
    //                     }
    //                 }else{
    //                     $interstAmount =  0;
    //                     $isDefaulter = 1;
    //                     $finalAmount = 0;
    //                     $investAmount = 0;
    //                 }
    //             }elseif(in_array($mInvestment->plan_id, $daily)){
    //                 if($investmentData){
    //                         $cMonth = date('m');
    //                         $cYear = date('Y');
    //                         $cuurentInterest = $mInvestment->interest_rate;
    //                         $tenureMonths = $mInvestment->tenure*12;
    //                         $i = 0;
    //                         for ($i = 0; $i <= $tenureMonths; $i++){
    //                             /*$integer = $i+1;
    //                             $createdMonth = date("m", strtotime($mInvestment->created_at));
    //                             $createdYear = date("Y", strtotime($mInvestment->created_at));
    //                             if($createdMonth > $integer){
    //                                 $month = $createdMonth+$i;
    //                                 $year = $createdYear;
    //                             }elseif($integer == $createdMonth){
    //                                 $month = 1;
    //                                 $year = $createdYear+1;
    //                             }elseif(($i+1) > $createdMonth){
    //                                 $month = ($integer-$createdMonth)+1;
    //                                 $year = $createdYear+1;
    //                             }*/
    //                             //$month = date("m", strtotime("".$i." month", strtotime($mInvestment->created_at)));
    //                             //$year = date("Y", strtotime("".$i." month", strtotime($mInvestment->created_at)));
    //                             $newdate = date("Y-m-d", strtotime("".$i." month", strtotime($mInvestment->created_at)));
    //                             $implodeArray = explode('-',$newdate);
    //                             $year = $implodeArray[0];
    //                             //$month = $implodeArray[1];
    //                             $cdate = $mInvestment->created_at;
    //                             $cexplodedate = explode('-',$mInvestment->created_at);
    //                             if(($cexplodedate[1]+$i) > 12){
    //                                 $month = ($cexplodedate[1]+$i)-12;
    //                             }else{
    //                                 $month = $cexplodedate[1]+$i;
    //                             }
    //                             if(($i+1) == 13){
    //                                 $fRecord = Daybook::where('investment_id', $mInvestment->id)
    //                                 ->whereMonth('created_at', $month)->whereYear('created_at', $year)->first();
    //                                 if($fRecord){
    //                                     $total = Daybook::where('investment_id', $mInvestment->id)->whereIn('transaction_type', [2,4])->where('id','>=',$fRecord->id)->sum('deposit');
    //                                 }else{
    //                                     $total = Daybook::where('investment_id', $mInvestment->id)
    //                                 ->whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('deposit');
    //                                 }
    //                             }else{
    //                                 $total = Daybook::where('investment_id', $mInvestment->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                             }
    //                             $totalDeposit = $totalDeposit+$total;
    //                             $countDays = Daybook::where('investment_id', $mInvestment->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->whereIn('transaction_type', [2,4])->count();
    //                             /*if($cMonth > $month && $cYear > $year){
    //                                 if($countDays < 25 && ($mInvestment->deposite_amount*25) > $total){
    //                                     $defaulterInterest = 1.50;
    //                                     $isDefaulter = 1;
    //                                 }else{
    //                                     $defaulterInterest = 0;
    //                                     $isDefaulter = 0;
    //                                 }
    //                             }else{
    //                                 $defaulterInterest = 0;
    //                                 $isDefaulter = 0;
    //                             }*/
    //                             if(($mInvestment->deposite_amount*25) > $total){
    //                                 $defaulterInterest = 1.50;
    //                                 $isDefaulter = 1;
    //                             }else{
    //                                 $defaulterInterest = 0;
    //                                 $isDefaulter = 0;
    //                             }
    //                             if($tenureMonths == 12){
    //                                 $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/117800;
    //                             }elseif($tenureMonths == 24){
    //                                 $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/115000;
    //                             }elseif($tenureMonths == 36){
    //                                 $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/111900;
    //                             }elseif($tenureMonths == 60){
    //                                 $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/106100;
    //                             }
    //                             if(($tenureMonths-$i) == 0){
    //                                 $interest = 0;
    //                             }
    //                             $totalInterestDeposit = $totalInterestDeposit+$interest;
    //                         }
    //                     $interstAmount = round($totalInterestDeposit);
    //                     $finalAmount = round($totalDeposit+$totalInterestDeposit);
    //                     $investAmount = $totalDeposit;
    //                 }else{
    //                     $interstAmount =  0;
    //                     $finalAmount = 0;
    //                     $isDefaulter = 1;
    //                     $investAmount = 0;
    //                 }
    //             }elseif(in_array($mInvestment->plan_id, $preMaturity)){
    //                 $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                 if($investmentData){
    //                     $cDate = date('Y-m-d');
    //                     $ts1 = strtotime($mInvestment->created_at);
    //                     $ts2 = strtotime($cDate);
    //                     $year1 = date('Y', $ts1);
    //                     $year2 = date('Y', $ts2);
    //                     $month1 = date('m', $ts1);
    //                     $month2 = date('m', $ts2);
    //                     $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
    //                     if($mInvestment->plan_id == 4){
    //                         if($monthDiff >= 0 && $monthDiff <= 36){
    //                             $cuurentInterest = 8;
    //                         }else if($monthDiff >= 37 && $monthDiff <= 48){
    //                             $cuurentInterest = 8.25;
    //                         }else if($monthDiff >= 49 && $monthDiff <= 60){
    //                             $cuurentInterest = 8.50;
    //                         }else if($monthDiff >= 61 && $monthDiff <= 72){
    //                             $cuurentInterest = 8.75;
    //                         }else if($monthDiff >= 73 && $monthDiff <= 84){
    //                             $cuurentInterest = 9;
    //                         }else if($monthDiff >= 85 && $monthDiff <= 96){
    //                             $cuurentInterest = 9.50;
    //                         }else if($monthDiff >= 97 && $monthDiff <= 108){
    //                             $cuurentInterest = 10;
    //                         }else if($monthDiff >= 109 && $monthDiff <= 120){
    //                             $cuurentInterest = 11;
    //                         }else{
    //                             $cuurentInterest = 11;
    //                         }
    //                     }elseif($mInvestment->plan_id == 5){
    //                         if($monthDiff >= 0 && $monthDiff <= 12){
    //                             $cuurentInterest = 5;
    //                         }else if($monthDiff >= 12 && $monthDiff <= 24){
    //                             $cuurentInterest = 6;
    //                         }else if($monthDiff >= 24 && $monthDiff <= 36){
    //                             $cuurentInterest = 6.50;
    //                         }else if($monthDiff >= 36 && $monthDiff <= 48){
    //                             $cuurentInterest = 7;
    //                         }else if($monthDiff >= 48 && $monthDiff <= 60){
    //                             $cuurentInterest = 9;
    //                         }else{
    //                             $cuurentInterest = 9;
    //                         }
    //                     }
    //                     if($mInvestment->plan_id == 4){
    //                         /*if($cDate < $maturity_date && $monthDiff != 120){
    //                             $defaulterInterest = 1.50;
    //                             $isDefaulter = 1;
    //                         }else{
    //                             $defaulterInterest = 0;
    //                             $isDefaulter = 0;
    //                         }*/
    //                         $defaulterInterest = 0;
    //                         $isDefaulter = 0;
    //                         $irate = ($cuurentInterest-$defaulterInterest) / 1;
    //                         $year = $monthDiff / 12;
    //                         $result =  ( $totalInvestmentAmount*(pow((1 + $irate / 100), $year)))-($totalInvestmentAmount);
    //                     }else{
    //                         if($cDate < $maturity_date && $monthDiff != 60){
    //                             $defaulterInterest = 1.50;
    //                             $isDefaulter = 1;
    //                         }else{
    //                             $defaulterInterest = 0;
    //                             $isDefaulter = 0;
    //                         }
    //                         $irate = ($cuurentInterest-$defaulterInterest) / 1;
    //                         $year = $monthDiff / 12;
    //                         $maturity=0;
    //                         $freq = 4;
    //                         for($i=1; $i<=$monthDiff;$i++){
    //                             $rmaturity = ($mInvestment->deposite_amount*(pow((1+(($irate/100)/$freq)), $freq*(($monthDiff-$i+1)/12))));
    //                             $maturity = $maturity+$rmaturity;
    //                         }
    //                         if($maturity > ($mInvestment->deposite_amount*$monthDiff)){
    //                             $result =  $maturity-($mInvestment->deposite_amount*$monthDiff);
    //                         }else{
    //                             $result =  $maturity;
    //                         }
    //                     }
    //                     $interstAmount = round($result);
    //                     $finalAmount = round($totalInvestmentAmount+$interstAmount);
    //                     $investAmount = $totalInvestmentAmount;
    //                 }else{
    //                     $interstAmount =  0;
    //                     $finalAmount = 0;
    //                     $isDefaulter = 1;
    //                     $investAmount = 0;
    //                 }
    //             }elseif(in_array($mInvestment->plan_id, $fixed)){
    //                 if($investmentData){
    //                         $cDate = date('Y-m-d');
    //                         $cYear = date('Y');
    //                         $cuurentInterest = $mInvestment->interest_rate;
    //                         if($cDate < $maturity_date){
    //                             $defaulterInterest = 1.50;
    //                             $isDefaulter = 1;
    //                         }else{
    //                             $defaulterInterest = 0;
    //                             $isDefaulter = 0;
    //                         }
    //                     $irate = ($cuurentInterest-$defaulterInterest) / 1;
    //                     $year = $mInvestment->tenure*12;
    //                     $interstAmount =  round(( $mInvestment->deposite_amount*(pow((1 + $irate / 100), $year)))-($mInvestment->deposite_amount));
    //                     $finalAmount = round($mInvestment->deposite_amount+$interstAmount);
    //                     $investAmount = $mInvestment->deposite_amount;
    //                 }else{
    //                     $interstAmount =  0;
    //                     $finalAmount = 0;
    //                     $isDefaulter = 1;
    //                     $investAmount = 0;
    //                 }
    //             }elseif(in_array($mInvestment->plan_id, $samraddhJeevan)){
    //                 if($investmentData){
    //                     $cDate = date('Y-m-d');
    //                     $investmentMonths = $mInvestment->tenure*12;
    //                     $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                     $isDefaulter = 0;
    //                     if($cDate >= $maturity_date){
    //                         $defaulterInterest = 6;
    //                         $isDefaulter = 0;
    //                         $depositAmount = ($mInvestment->deposite_amount*12)*$mInvestment->tenure;
    //                         $result = $defaulterInterest*($depositAmount) / 100;
    //                         $interstAmount = number_format((float)$result, 2, '.', '');
    //                         $finalAmount = round($depositAmount+$result);
    //                         $investAmount = $depositAmount;
    //                     }elseif($cDate < $maturity_date){
    //                         for ($i=1; $i <= $investmentMonths ; $i++){
    //                             $val = $mInvestment;
    //                             $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
    //                             $cMonth = date('m');
    //                             $cYear = date('Y');
    //                             $cMonth = date('m');
    //                             $cYear = date('Y');
    //                             if($mInvestment->plan_id == 2){
    //                                 $cuurentInterest = $val->interest_rate;
    //                             }elseif($mInvestment->plan_id == 6){
    //                                 $cuurentInterest = 11;
    //                             }
    //                             $totalDeposit = $totalInvestmentAmount;
    //                             $d1 = explode('-',$val->created_at);
    //                             $d2 = explode('-',$nDate);
    //                             $ts1 = strtotime($val->created_at);
    //                             $ts2 = strtotime($nDate);
    //                             $year1 = date('Y', $ts1);
    //                             $year2 = date('Y', $ts2);
    //                             $month1 = date('m', $ts1);
    //                             $month2 = date('m', $ts2);
    //                             $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
    //                             $cfAmount = Memberinvestments::where('id',$val->id)->first();
    //                             if($val->deposite_amount*$monthDiff <= $totalDeposit){
    //                                 $extraAmount = (int) $totalDeposit-(int) ($val->deposite_amount*$monthDiff);
    //                                 $cfdAmount = (int) $extraAmount+(int) $cfAmount->carry_forward_amount;
    //                                 Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
    //                                 $aviAmount = $val->deposite_amount;
    //                                 $total = $total+$val->deposite_amount;
    //                                 if($monthDiff % 12 == 0 && $monthDiff != 0){
    //                                     $total = $total+$regularInterest;
    //                                     $cInterest = $regularInterest;
    //                                 }else{
    //                                     $total = $total;
    //                                     $cInterest = 0;
    //                                 }
    //                                 $regularInterest = $regularInterest+($cuurentInterest*$total/1200);
    //                                 $a = -$aviAmount+$aviAmount*(pow((1+$cuurentInterest/100), (1*$i/12)));
    //                                 $interest = number_format((float)$a, 2, '.', '');
    //                                 $totalInterestDeposit = $totalInterestDeposit+($interest);
    //                             }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
    //                                 $pendingAmount =  ($val->deposite_amount*12)-$totalDeposit;
    //                                 if((int) $cfAmount->carry_forward_amount >= (int) $pendingAmount){
    //                                     $cfdAmount = (int) $cfAmount->carry_forward_amount-(int) $pendingAmount;
    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
    //                                     $collection = (int) $totalDeposit+(int) $pendingAmount;
    //                                 }elseif((int) $cfAmount->carry_forward_amount == (int) $pendingAmount){
    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
    //                                     $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
    //                                 }elseif((int) $pendingAmount > (int) $cfAmount->carry_forward_amount){
    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
    //                                     $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
    //                                 }
    //                                 $checkAmount = $collection+($totalDeposit-$val->deposite_amount*($monthDiff-1));
    //                                 if($checkAmount > 0){
    //                                     $aviAmount = $checkAmount;
    //                                     $total = $total+$checkAmount;
    //                                     if($monthDiff % 12 == 0 && $monthDiff != 0){
    //                                         $total = $total+$regularInterest;
    //                                         $cInterest = $regularInterest;
    //                                     }else{
    //                                         $total = $total;
    //                                         $cInterest = 0;
    //                                     }
    //                                     $regularInterest = $regularInterest+($cuurentInterest*$total/1200);
    //                                     $a = -$aviAmount+$aviAmount*(pow((1+$cuurentInterest/100), (1*$i/12)));
    //                                     $interest = number_format((float)$a, 2, '.', '');
    //                                 }else{
    //                                     $aviAmount = 0;
    //                                     $total = 0;
    //                                     $cuurentInterest = 0;
    //                                     $interest = 0;
    //                                     $a = 0;
    //                                 }
    //                                 $totalInterestDeposit = $totalInterestDeposit+($interest);
    //                             }
    //                         }
    //                         $interstAmount = round($totalInterestDeposit);
    //                         $finalAmount = round($totalDeposit+$totalInterestDeposit);
    //                         $investAmount = $totalDeposit;
    //                     }
    //                 }else{
    //                     $finalAmount = 0;
    //                     $isDefaulter = 1;
    //                     $investAmount = 0;
    //                     $interstAmount = 0;
    //                 }
    //             }elseif(in_array($mInvestment->plan_id, $moneyBack)){
    //                 if($investmentData){
    //                     $diff = abs(strtotime($cDate) - strtotime($mInvestment->last_deposit_to_ssb_date));
    //                     $years = floor($diff / (365*60*60*24));
    //                     if($cDate >= $maturity_date){
    //                         $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                         $maturityAmount = getMoneyBackAmount($mInvestment->id);
    //                         $fAmount = $maturityAmount->available_amount;
    //                         $isDefaulter = 0;
    //                         $refundAmount = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$mInvestment->id)->where('plan_type_id',3)->sum('yearly_deposit_amount');
    //                         $interstAmount = ($fAmount+$refundAmount)-$totalInvestmentAmount;
    //                         $finalAmount = $fAmount-$interstAmount;
    //                         $investAmount = $finalAmount;
    //                     }elseif($cDate < $maturity_date){
    //                         $totaldepositAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                         $depositInterest = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$mInvestment->id)->where('plan_type_id',3)->orderby('date', 'desc')->first();
    //                         $investmentMonths = $mInvestment->tenure*12;
    //                         if($mInvestment->last_deposit_to_ssb_date){
    //                             $sDate = date("Y-m-d", strtotime(convertDate($mInvestment->last_deposit_to_ssb_date)));
    //                             $ts1 = strtotime($sDate);
    //                             $ts2 = strtotime($cDate);
    //                             $year1 = date('Y', $ts1);
    //                             $year2 = date('Y', $ts2);
    //                             $month1 = date('m', $ts1);
    //                             $month2 = date('m', $ts2);
    //                             $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
    //                             $investmentMonths = $monthDiff;
    //                             $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereIn('transaction_type', [2,4])->whereDate('created_at','>=',$mInvestment->last_deposit_to_ssb_date)->sum('deposit');
    //                         }else{
    //                             $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                             $sDate = date("Y-m-d", strtotime(convertDate($mInvestment->created_at)));
    //                             $ts1 = strtotime($sDate);
    //                             $ts2 = strtotime($cDate);
    //                             $year1 = date('Y', $ts1);
    //                             $year2 = date('Y', $ts2);
    //                             $month1 = date('m', $ts1);
    //                             $month2 = date('m', $ts2);
    //                             $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
    //                             $investmentMonths = $monthDiff;
    //                         }
    //                         for ($i=1; $i <= $investmentMonths ; $i++){
    //                             $val = $mInvestment;
    //                             $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
    //                             $cMonth = date('m');
    //                             $cYear = date('Y');
    //                             $cuurentInterest = $mInvestment->interest_rate;
    //                             $totalDeposit = $totalInvestmentAmount;
    //                             $ts1 = strtotime($mInvestment->created_at);
    //                             $ts2 = strtotime($nDate);
    //                             $year1 = date('Y', $ts1);
    //                             $year2 = date('Y', $ts2);
    //                             $month1 = date('m', $ts1);
    //                             $month2 = date('m', $ts2);
    //                             $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
    //                             $defaulterInterest = 0;
    //                             if($val->deposite_amount*$monthDiff <= $totalDeposit){
    //                                 $aviAmount = $val->deposite_amount;
    //                                 $total = $total+$val->deposite_amount;
    //                                 if($monthDiff % 3 == 0 && $monthDiff != 0){
    //                                     $total = $total+$regularInterest;
    //                                     $cInterest = $regularInterest;
    //                                 }else{
    //                                     $total = $total;
    //                                     $cInterest = 0;
    //                                 }
    //                                 $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
    //                                 $addInterest = ($cuurentInterest-$defaulterInterest);
    //                                 $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
    //                                 $interest = number_format((float)$a, 2, '.', '');
    //                                 $totalInterestDeposit = $totalInterestDeposit+$interest;
    //                             }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
    //                                 $checkAmount = ($totalDeposit-$val->deposite_amount*($monthDiff-1));
    //                                 if($checkAmount > 0){
    //                                     $aviAmount = $checkAmount;
    //                                     $total = $total+$checkAmount;
    //                                     if($monthDiff % 3 == 0 && $monthDiff != 0){
    //                                         $total = $total+$regularInterest;
    //                                         $cInterest = $regularInterest;
    //                                     }else{
    //                                         $total = $total;
    //                                         $cInterest = 0;
    //                                     }
    //                                     $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
    //                                     $addInterest = ($cuurentInterest-$defaulterInterest);
    //                                     $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
    //                                     $interest = number_format((float)$a, 2, '.', '');
    //                                 }else{
    //                                     $aviAmount = 0;
    //                                     $total = 0;
    //                                     $cuurentInterest = 0;
    //                                     $interest = 0;
    //                                     $addInterest = 0;
    //                                 }
    //                                 $totalInterestDeposit = $totalInterestDeposit+$interest;
    //                             }
    //                         }
    //                         if($depositInterest){
    //                             $availableAmountFd = ($depositInterest->available_amount*9/100)+$depositInterest->available_amount;
    //                             $fAmount = round($totalDeposit+$totalInterestDeposit+$availableAmountFd);
    //                         }else{
    //                             $fAmount = round($totalDeposit+$totalInterestDeposit);
    //                         }
    //                         $isDefaulter = 0;
    //                         $refundAmount = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$mInvestment->id)->where('plan_type_id',3)->sum('yearly_deposit_amount');
    //                         if($refundAmount){
    //                             $interstAmount = round(($fAmount+$refundAmount)-$totaldepositAmount);
    //                             $finalAmount = $fAmount-$interstAmount;
    //                             $investAmount = $finalAmount;
    //                         }else{
    //                             $interstAmount = $fAmount-$totaldepositAmount;
    //                             $finalAmount = $fAmount-$interstAmount;
    //                             $investAmount = $finalAmount;
    //                         }
    //                     }
    //                 }else{
    //                     $finalAmount = 0;
    //                     $isDefaulter = 1;
    //                     $investAmount = 0;
    //                     $interstAmount = 0;
    //                 }
    //             }
    //         }
    //     } else{
    //             $isDefaulter = 0;
    //             $finalAmount = 0;
    //     }
    //     $finalAmount= number_format((float)$finalAmount, 2, '.', '');
    //     $return_array = compact('investmentDetails','isDefaulter','finalAmount','message','status');
    //     return json_encode($return_array);
    // }
    // public function getInvestmentDetails(Request $request)
    // {
    //     $investmentAccount = $request->val;
    //     $type = $request->type;
    //     $subtype = $request->subtype;
    //     $cDate = date("Y-m-d");
    //     if($type == 4 && $subtype == 0){
    //         $investmentDetails = Memberinvestments::with('investmentNomiees','plan','member','ssb','memberBankDetail','investmentNomiees')->where('account_number',$investmentAccount)->where('account_number', 'not like', "%R-%")->where('plan_id',6)->where('is_mature',1)->where('investment_correction_request',0)->where('renewal_correction_request',0)->first();
    //         if($investmentDetails){
    //             $message = '';
    //             $status = 200;
    //         }else{
    //             $message = 'Record Not Found!';
    //             $status = 400;
    //         }
    //     }elseif($type == 4 && $subtype == 1){
    //         $investmentDetails = Memberinvestments::with('investmentNomiees','plan','member','ssb','memberBankDetail','investmentNomiees')->where('account_number',$investmentAccount)->where('account_number', 'not like', "%R-%")->whereNotIn('plan_id',[1,6])->where('is_mature',1)->where('investment_correction_request',0)->where('renewal_correction_request',0)->first();
    //         if($investmentDetails){
    //             $message = '';
    //             $status = 200;
    //         }else{
    //             $message = 'Record Not Found!';
    //             $status = 400;
    //         }
    //     }elseif($type == 2 && $subtype == 0){
    //         $investmentDetails = Memberinvestments::with('investmentNomiees','plan','member','ssb','memberBankDetail','investmentNomiees')->where('account_number',$investmentAccount)->where('account_number', 'not like', "%R-%")->whereNotIn('plan_id',[1,6])->where('is_mature',1)->where('investment_correction_request',0)->where('renewal_correction_request',0)->first();
    //         if($investmentDetails){
    //           $existInvestment = \App\Models\Loaninvestmentmembers::where('plan_id',$investmentDetails->id)->first();
    //           if($existInvestment)
    //           {
    //                 $checkloanClose = Memberloans::where('id',$existInvestment->member_loan_id)->where('status','!=',3)->first();
    //               if($existInvestment && $checkloanClose)
    //               {
    //                      $message = 'Deposite Against Loan Please Close the Loan!';
    //                     $status = 500;
    //               }
    //                 else{
    //                     $maturityDate =  date('Y-m-d', strtotime($investmentDetails->created_at. ' + '.($investmentDetails->tenure).' year'));
    //                     $currentDate=date_create($cDate);
    //                     $diff = strtotime($maturityDate) - strtotime($cDate);
    //                     $daydiff = abs(round($diff / 86400));
    //                     if($cDate < $maturityDate && $daydiff > 5555555555555555){
    //                         $message = 'Record Not Match With Maturity Conditions!';
    //                         $status = 500;
    //                     }else{
    //                         $message = '';
    //                         $status = 200;
    //                     }
    //                 }
    //           }
    //         }else{
    //             $message = 'Record Not Found!';
    //             $status = 400;
    //         }
    //     }elseif($type == 2 && $subtype == 1){
    //         $investmentDetails = Memberinvestments::with('investmentNomiees','plan','member','ssb','memberBankDetail','investmentNomiees')->where('account_number',$investmentAccount)->where('account_number', 'not like', "%R-%")->whereIn('plan_id',[4,5])->where('is_mature',1)->where('investment_correction_request',0)->where('renewal_correction_request',0)->first();
    //         if($investmentDetails){
    //           $existInvestment = \App\Models\Loaninvestmentmembers::where('plan_id',$investmentDetails->id)->first();
    //           if($existInvestment)
    //           {
    //                 $checkloanClose = Memberloans::where('id',$existInvestment->member_loan_id)->where('status','!=',3)->first();
    //               if($existInvestment && $checkloanClose)
    //               {
    //                     $message = 'Loan Ongoing on this Plan!';
    //                     $status = 500;
    //               }
    //               else{
    //                 $message = '';
    //                 $status = 200;
    //             }
    //           }
    //         }else{
    //             $message = 'Record Not Found!';
    //             $status = 400;
    //         }
    //     }
    //     if($investmentDetails ){
    //         $demandAdviceRecord = DemandAdvice::where('investment_id',$investmentDetails->id)->where('is_deleted',0)->count();
    //         if($demandAdviceRecord > 0){
    //             $isDefaulter = 0;
    //             $finalAmount = 0;
    //             $message = 'Already request created for this paln!';
    //             $status = 500;
    //         }else{
    //             $mInvestment = $investmentDetails;
    //             $maturity_date =  date('Y-m-d', strtotime($mInvestment->created_at. ' + '.($mInvestment->tenure).' year'));
    //             $investmentData = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereIn('transaction_type', [2,4])->whereDate('created_at','<=',$maturity_date)->orderby('created_at', 'asc')->get();
    //             $keyVal = 0;
    //             $cInterest = 0;
    //             $regularInterest = 0;
    //             $total = 0;
    //             $collection = 0;
    //             $monthly = array(10,11);
    //             $daily = array(7);
    //             $preMaturity = array(4,5);
    //             $fixed = array(8,9);
    //             $samraddhJeevan = array(2,6);
    //             $moneyBack = array(3);
    //             $totalDeposit = 0;
    //             $totalInterestDeposit = 0;
    //             if(in_array($mInvestment->plan_id, $monthly)){
    //                 if($investmentData){
    //                     $investmentMonths = $mInvestment->tenure*12;
    //                     $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                     for ($i=1; $i <= $investmentMonths ; $i++){
    //                             $val = $mInvestment;
    //                             $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
    //                             $cMonth = date('m');
    //                             $cYear = date('Y');
    //                             $cuurentInterest = $mInvestment->interest_rate;
    //                             $totalDeposit = $totalInvestmentAmount;
    //                             $previousRecord = Daybook::select('deposit','created_at')->whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<', $nDate)->max('created_at');
    //                             $sumPreviousRecordAmount = Daybook::whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<=', $nDate)->sum('deposit');
    //                             $d1 = explode('-',$mInvestment->created_at);
    //                             $d2 = explode('-',$nDate);
    //                             $ts1 = strtotime($mInvestment->created_at);
    //                             $ts2 = strtotime($nDate);
    //                             $year1 = date('Y', $ts1);
    //                             $year2 = date('Y', $ts2);
    //                             $month1 = date('m', $ts1);
    //                             $month2 = date('m', $ts2);
    //                             $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
    //                             if($cMonth > $d2[1] && $cYear > $d2[0]){
    //                                 if($previousRecord){
    //                                     $previousDate = explode('-',$previousRecord);
    //                                     $previousMonth = $previousDate[1];
    //                                     if(($secondMonth-$previousMonth) >= 3 && $sumPreviousRecordAmount < $mInvestment->deposite_amount*$monthDiff){
    //                                         $defaulterInterest = 1.50;
    //                                         $isDefaulter = 1;
    //                                     }else{
    //                                         $defaulterInterest = 0;
    //                                         $isDefaulter = 0;
    //                                     }
    //                                 }else{
    //                                     $defaulterInterest = 0;
    //                                     $isDefaulter = 0;
    //                                 }
    //                             }else{
    //                                 $defaulterInterest = 0;
    //                                 $isDefaulter = 1;
    //                             }
    //                             $cfAmount = Memberinvestments::where('id',$val->id)->first();
    //                             if($val->deposite_amount*$monthDiff <= $totalDeposit){
    //                                 $extraAmount = (int) $totalDeposit-(int) ($val->deposite_amount*$monthDiff);
    //                                 $cfdAmount = (int) $extraAmount+(int) $cfAmount->carry_forward_amount;
    //                                 Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
    //                                 $aviAmount = $val->deposite_amount;
    //                                 $total = $total+$val->deposite_amount;
    //                                 if($monthDiff % 3 == 0 && $monthDiff != 0){
    //                                     $total = $total+$regularInterest;
    //                                     $cInterest = $regularInterest;
    //                                 }else{
    //                                     $total = $total;
    //                                     $cInterest = 0;
    //                                 }
    //                                 $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
    //                                 $addInterest = ($cuurentInterest-$defaulterInterest);
    //                                 $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
    //                                 $interest = number_format((float)$a, 2, '.', '');
    //                                 $totalInterestDeposit = $totalInterestDeposit+$interest;
    //                             }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
    //                                 $pendingAmount =  ($val->deposite_amount*12)-$totalDeposit;
    //                                 if((int) $cfAmount->carry_forward_amount >= (int) $pendingAmount){
    //                                     $cfdAmount = (int) $cfAmount->carry_forward_amount-(int) $pendingAmount;
    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
    //                                     $collection = (int) $totalDeposit+(int) $pendingAmount;
    //                                 }elseif((int) $cfAmount->carry_forward_amount == (int) $pendingAmount){
    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
    //                                     $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
    //                                 }elseif((int) $pendingAmount > (int) $cfAmount->carry_forward_amount){
    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
    //                                     $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
    //                                 }
    //                                 $checkAmount = $collection+($totalDeposit-$val->deposite_amount*($monthDiff-1));
    //                                 if($checkAmount > 0){
    //                                   $aviAmount = $checkAmount;
    //                                   $total = $total+$checkAmount;
    //                                     if($monthDiff % 3 == 0 && $monthDiff != 0){
    //                                         $total = $total+$regularInterest;
    //                                         $cInterest = $regularInterest;
    //                                     }else{
    //                                         $total = $total;
    //                                         $cInterest = 0;
    //                                     }
    //                                     $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
    //                                     $addInterest = ($cuurentInterest-$defaulterInterest);
    //                                     $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
    //                                     $interest = number_format((float)$a, 2, '.', '');
    //                                 }else{
    //                                     $aviAmount = 0;
    //                                     $total = 0;
    //                                     $cuurentInterest = 0;
    //                                     $interest = 0;
    //                                     $addInterest = 0;
    //                                 }
    //                                 $totalInterestDeposit = $totalInterestDeposit+$interest;
    //                             }
    //                     }
    //                     $interstAmount = round($totalInterestDeposit);
    //                     if($request->type == 2){
    //                         $finalAmount = round(($totalDeposit+$totalInterestDeposit)-(1.5*($totalDeposit+$totalInterestDeposit)/100));
    //                     }else{
    //                         $finalAmount = round($totalDeposit+$totalInterestDeposit);
    //                         $investAmount = $totalDeposit;
    //                     }
    //                 }else{
    //                     $interstAmount =  0;
    //                     $isDefaulter = 1;
    //                     $finalAmount = 0;
    //                     $investAmount = 0;
    //                 }
    //             }elseif(in_array($mInvestment->plan_id, $daily)){
    //                 if($investmentData){
    //                         $cMonth = date('m');
    //                         $cYear = date('Y');
    //                         $cuurentInterest = $mInvestment->interest_rate;
    //                         $tenureMonths = $mInvestment->tenure*12;
    //                         $i = 0;
    //                         for ($i = 0; $i <= $tenureMonths; $i++){
    //                             /*$integer = $i+1;
    //                             $createdMonth = date("m", strtotime($mInvestment->created_at));
    //                             $createdYear = date("Y", strtotime($mInvestment->created_at));
    //                             if($createdMonth > $integer){
    //                                 $month = $createdMonth+$i;
    //                                 $year = $createdYear;
    //                             }elseif($integer == $createdMonth){
    //                                 $month = 1;
    //                                 $year = $createdYear+1;
    //                             }elseif(($i+1) > $createdMonth){
    //                                 $month = ($integer-$createdMonth)+1;
    //                                 $year = $createdYear+1;
    //                             }*/
    //                             //$month = date("m", strtotime("".$i." month", strtotime($mInvestment->created_at)));
    //                             //$year = date("Y", strtotime("".$i." month", strtotime($mInvestment->created_at)));
    //                             $newdate = date("Y-m-d", strtotime("".$i." month", strtotime($mInvestment->created_at)));
    //                             $implodeArray = explode('-',$newdate);
    //                             $year = $implodeArray[0];
    //                             //$month = $implodeArray[1];
    //                             $cdate = $mInvestment->created_at;
    //                             $cexplodedate = explode('-',$mInvestment->created_at);
    //                             if(($cexplodedate[1]+$i) > 12){
    //                                 $month = ($cexplodedate[1]+$i)-12;
    //                             }else{
    //                                 $month = $cexplodedate[1]+$i;
    //                             }
    //                             if(($i+1) == 13){
    //                                 $fRecord = Daybook::where('investment_id', $mInvestment->id)
    //                                 ->whereMonth('created_at', $month)->whereYear('created_at', $year)->first();
    //                                 if($fRecord){
    //                                     $total = Daybook::where('investment_id', $mInvestment->id)->whereIn('transaction_type', [2,4])->where('id','>=',$fRecord->id)->sum('deposit');
    //                                 }else{
    //                                   $total = Daybook::where('investment_id', $mInvestment->id)
    //                                 ->whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('deposit');
    //                                 }
    //                             }else{
    //                                 $total = Daybook::where('investment_id', $mInvestment->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                             }
    //                             $totalDeposit = $totalDeposit+$total;
    //                             $countDays = Daybook::where('investment_id', $mInvestment->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->whereIn('transaction_type', [2,4])->count();
    //                             /*if($cMonth > $month && $cYear > $year){
    //                                 if($countDays < 25 && ($mInvestment->deposite_amount*25) > $total){
    //                                     $defaulterInterest = 1.50;
    //                                     $isDefaulter = 1;
    //                                 }else{
    //                                     $defaulterInterest = 0;
    //                                     $isDefaulter = 0;
    //                                 }
    //                             }else{
    //                                 $defaulterInterest = 0;
    //                                 $isDefaulter = 0;
    //                             }*/
    //                             if(($mInvestment->deposite_amount*25) > $total){
    //                                 $defaulterInterest = 1.50;
    //                                 $isDefaulter = 1;
    //                             }else{
    //                                 $defaulterInterest = 0;
    //                                 $isDefaulter = 0;
    //                             }
    //                             if($tenureMonths == 12){
    //                                 $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/117800;
    //                             }elseif($tenureMonths == 24){
    //                                 $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/115000;
    //                             }elseif($tenureMonths == 36){
    //                                 $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/111900;
    //                             }elseif($tenureMonths == 60){
    //                                 $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/106100;
    //                             }
    //                             if(($tenureMonths-$i) == 0){
    //                                 $interest = 0;
    //                             }
    //                             $totalInterestDeposit = $totalInterestDeposit+$interest;
    //                         }
    //                     $interstAmount = round($totalInterestDeposit);
    //                     $finalAmount = round($totalDeposit+$totalInterestDeposit);
    //                     $investAmount = $totalDeposit;
    //                 }else{
    //                     $interstAmount =  0;
    //                     $finalAmount = 0;
    //                     $isDefaulter = 1;
    //                     $investAmount = 0;
    //                 }
    //             }elseif(in_array($mInvestment->plan_id, $preMaturity)){
    //                 $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                 if($investmentData){
    //                     $cDate = date('Y-m-d');
    //                     $ts1 = strtotime($mInvestment->created_at);
    //                     $ts2 = strtotime($cDate);
    //                     $year1 = date('Y', $ts1);
    //                     $year2 = date('Y', $ts2);
    //                     $month1 = date('m', $ts1);
    //                     $month2 = date('m', $ts2);
    //                     $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
    //                     if($mInvestment->plan_id == 4){
    //                         if($monthDiff >= 0 && $monthDiff <= 36){
    //                             $cuurentInterest = 8;
    //                         }else if($monthDiff >= 37 && $monthDiff <= 48){
    //                             $cuurentInterest = 8.25;
    //                         }else if($monthDiff >= 49 && $monthDiff <= 60){
    //                             $cuurentInterest = 8.50;
    //                         }else if($monthDiff >= 61 && $monthDiff <= 72){
    //                             $cuurentInterest = 8.75;
    //                         }else if($monthDiff >= 73 && $monthDiff <= 84){
    //                             $cuurentInterest = 9;
    //                         }else if($monthDiff >= 85 && $monthDiff <= 96){
    //                             $cuurentInterest = 9.50;
    //                         }else if($monthDiff >= 97 && $monthDiff <= 108){
    //                             $cuurentInterest = 10;
    //                         }else if($monthDiff >= 109 && $monthDiff <= 120){
    //                             $cuurentInterest = 11;
    //                         }else{
    //                             $cuurentInterest = 11;
    //                         }
    //                     }elseif($mInvestment->plan_id == 5){
    //                         if($monthDiff >= 0 && $monthDiff <= 12){
    //                             $cuurentInterest = 5;
    //                         }else if($monthDiff >= 12 && $monthDiff <= 24){
    //                             $cuurentInterest = 6;
    //                         }else if($monthDiff >= 24 && $monthDiff <= 36){
    //                             $cuurentInterest = 6.50;
    //                         }else if($monthDiff >= 36 && $monthDiff <= 48){
    //                             $cuurentInterest = 7;
    //                         }else if($monthDiff >= 48 && $monthDiff <= 60){
    //                             $cuurentInterest = 9;
    //                         }else{
    //                             $cuurentInterest = 9;
    //                         }
    //                     }
    //                     if($mInvestment->plan_id == 4){
    //                         /*if($cDate < $maturity_date && $monthDiff != 120){
    //                             $defaulterInterest = 1.50;
    //                             $isDefaulter = 1;
    //                         }else{
    //                             $defaulterInterest = 0;
    //                             $isDefaulter = 0;
    //                         }*/
    //                         $defaulterInterest = 0;
    //                         $isDefaulter = 0;
    //                         $irate = ($cuurentInterest-$defaulterInterest) / 1;
    //                         $year = $monthDiff / 12;
    //                         $result =  ( $totalInvestmentAmount*(pow((1 + $irate / 100), $year)))-($totalInvestmentAmount);
    //                     }else{
    //                         if($cDate < $maturity_date && $monthDiff != 60){
    //                             $defaulterInterest = 1.50;
    //                             $isDefaulter = 1;
    //                         }else{
    //                             $defaulterInterest = 0;
    //                             $isDefaulter = 0;
    //                         }
    //                         $irate = ($cuurentInterest-$defaulterInterest) / 1;
    //                         $year = $monthDiff / 12;
    //                         $maturity=0;
    //                         $freq = 4;
    //                         for($i=1; $i<=$monthDiff;$i++){
    //                             $rmaturity = ($mInvestment->deposite_amount*(pow((1+(($irate/100)/$freq)), $freq*(($monthDiff-$i+1)/12))));
    //                             $maturity = $maturity+$rmaturity;
    //                         }
    //                         if($maturity > ($mInvestment->deposite_amount*$monthDiff)){
    //                             $result =  $maturity-($mInvestment->deposite_amount*$monthDiff);
    //                         }else{
    //                             $result =  $maturity;
    //                         }
    //                     }
    //                     $interstAmount = round($result);
    //                     $finalAmount = round($totalInvestmentAmount+$interstAmount);
    //                     $investAmount = $totalInvestmentAmount;
    //                 }else{
    //                     $interstAmount =  0;
    //                     $finalAmount = 0;
    //                     $isDefaulter = 1;
    //                     $investAmount = 0;
    //                 }
    //             }elseif(in_array($mInvestment->plan_id, $fixed)){
    //                 if($investmentData){
    //                         $cDate = date('Y-m-d');
    //                         $cYear = date('Y');
    //                         $cuurentInterest = $mInvestment->interest_rate;
    //                         if($cDate < $maturity_date){
    //                             $defaulterInterest = 1.50;
    //                             $isDefaulter = 1;
    //                         }else{
    //                             $defaulterInterest = 0;
    //                             $isDefaulter = 0;
    //                         }
    //                     $irate = ($cuurentInterest-$defaulterInterest) / 1;
    //                     $year = $mInvestment->tenure*12;
    //                     $interstAmount =  round(( $mInvestment->deposite_amount*(pow((1 + $irate / 100), $year)))-($mInvestment->deposite_amount));
    //                     $finalAmount = round($mInvestment->deposite_amount+$interstAmount);
    //                     $investAmount = $mInvestment->deposite_amount;
    //                 }else{
    //                     $interstAmount =  0;
    //                     $finalAmount = 0;
    //                     $isDefaulter = 1;
    //                     $investAmount = 0;
    //                 }
    //             }elseif(in_array($mInvestment->plan_id, $samraddhJeevan)){
    //                 if($investmentData){
    //                     $cDate = date('Y-m-d');
    //                     $investmentMonths = $mInvestment->tenure*12;
    //                     $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                     $isDefaulter = 0;
    //                     if($cDate >= $maturity_date){
    //                         $defaulterInterest = 6;
    //                         $isDefaulter = 0;
    //                         $depositAmount = ($mInvestment->deposite_amount*12)*$mInvestment->tenure;
    //                         $result = $defaulterInterest*($depositAmount) / 100;
    //                         $interstAmount = number_format((float)$result, 2, '.', '');
    //                         $finalAmount = round($depositAmount+$result);
    //                         $investAmount = $depositAmount;
    //                     }elseif($cDate < $maturity_date){
    //                         for ($i=1; $i <= $investmentMonths ; $i++){
    //                             $val = $mInvestment;
    //                             $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
    //                             $cMonth = date('m');
    //                             $cYear = date('Y');
    //                             $cMonth = date('m');
    //                             $cYear = date('Y');
    //                             if($mInvestment->plan_id == 2){
    //                                 $cuurentInterest = $val->interest_rate;
    //                             }elseif($mInvestment->plan_id == 6){
    //                                 $cuurentInterest = 11;
    //                             }
    //                             $totalDeposit = $totalInvestmentAmount;
    //                             $d1 = explode('-',$val->created_at);
    //                             $d2 = explode('-',$nDate);
    //                             $ts1 = strtotime($val->created_at);
    //                             $ts2 = strtotime($nDate);
    //                             $year1 = date('Y', $ts1);
    //                             $year2 = date('Y', $ts2);
    //                             $month1 = date('m', $ts1);
    //                             $month2 = date('m', $ts2);
    //                             $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
    //                             $cfAmount = Memberinvestments::where('id',$val->id)->first();
    //                             if($val->deposite_amount*$monthDiff <= $totalDeposit){
    //                                 $extraAmount = (int) $totalDeposit-(int) ($val->deposite_amount*$monthDiff);
    //                                 $cfdAmount = (int) $extraAmount+(int) $cfAmount->carry_forward_amount;
    //                                 Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
    //                                 $aviAmount = $val->deposite_amount;
    //                                 $total = $total+$val->deposite_amount;
    //                                 if($monthDiff % 12 == 0 && $monthDiff != 0){
    //                                     $total = $total+$regularInterest;
    //                                     $cInterest = $regularInterest;
    //                                 }else{
    //                                     $total = $total;
    //                                     $cInterest = 0;
    //                                 }
    //                                 $regularInterest = $regularInterest+($cuurentInterest*$total/1200);
    //                                 $a = -$aviAmount+$aviAmount*(pow((1+$cuurentInterest/100), (1*$i/12)));
    //                                 $interest = number_format((float)$a, 2, '.', '');
    //                                 $totalInterestDeposit = $totalInterestDeposit+($interest);
    //                             }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
    //                                 $pendingAmount =  ($val->deposite_amount*12)-$totalDeposit;
    //                                 if((int) $cfAmount->carry_forward_amount >= (int) $pendingAmount){
    //                                     $cfdAmount = (int) $cfAmount->carry_forward_amount-(int) $pendingAmount;
    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
    //                                     $collection = (int) $totalDeposit+(int) $pendingAmount;
    //                                 }elseif((int) $cfAmount->carry_forward_amount == (int) $pendingAmount){
    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
    //                                     $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
    //                                 }elseif((int) $pendingAmount > (int) $cfAmount->carry_forward_amount){
    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
    //                                     $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
    //                                 }
    //                                 $checkAmount = $collection+($totalDeposit-$val->deposite_amount*($monthDiff-1));
    //                                 if($checkAmount > 0){
    //                                   $aviAmount = $checkAmount;
    //                                   $total = $total+$checkAmount;
    //                                     if($monthDiff % 12 == 0 && $monthDiff != 0){
    //                                         $total = $total+$regularInterest;
    //                                         $cInterest = $regularInterest;
    //                                     }else{
    //                                         $total = $total;
    //                                         $cInterest = 0;
    //                                     }
    //                                     $regularInterest = $regularInterest+($cuurentInterest*$total/1200);
    //                                     $a = -$aviAmount+$aviAmount*(pow((1+$cuurentInterest/100), (1*$i/12)));
    //                                     $interest = number_format((float)$a, 2, '.', '');
    //                                 }else{
    //                                     $aviAmount = 0;
    //                                     $total = 0;
    //                                     $cuurentInterest = 0;
    //                                     $interest = 0;
    //                                     $a = 0;
    //                                 }
    //                                 $totalInterestDeposit = $totalInterestDeposit+($interest);
    //                             }
    //                         }
    //                         $interstAmount = round($totalInterestDeposit);
    //                         $finalAmount = round($totalDeposit+$totalInterestDeposit);
    //                         $investAmount = $totalDeposit;
    //                     }
    //                 }else{
    //                     $finalAmount = 0;
    //                     $isDefaulter = 1;
    //                     $investAmount = 0;
    //                     $interstAmount = 0;
    //                 }
    //             }elseif(in_array($mInvestment->plan_id, $moneyBack)){
    //                 if($investmentData){
    //                     $diff = abs(strtotime($cDate) - strtotime($mInvestment->last_deposit_to_ssb_date));
    //                     $years = floor($diff / (365*60*60*24));
    //                     if($cDate >= $maturity_date){
    //                         $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                         $maturityAmount = getMoneyBackAmount($mInvestment->id);
    //                         $fAmount = $maturityAmount->available_amount;
    //                         $isDefaulter = 0;
    //                         $refundAmount = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$mInvestment->id)->where('plan_type_id',3)->sum('yearly_deposit_amount');
    //                         $interstAmount = ($fAmount+$refundAmount)-$totalInvestmentAmount;
    //                         $finalAmount = $fAmount-$interstAmount;
    //                         $investAmount = $finalAmount;
    //                     }elseif($cDate < $maturity_date){
    //                         $totaldepositAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                         $depositInterest = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$mInvestment->id)->where('plan_type_id',3)->orderby('date', 'desc')->first();
    //                         $investmentMonths = $mInvestment->tenure*12;
    //                         if($mInvestment->last_deposit_to_ssb_date){
    //                             $sDate = date("Y-m-d", strtotime(convertDate($mInvestment->last_deposit_to_ssb_date)));
    //                             $ts1 = strtotime($sDate);
    //                             $ts2 = strtotime($cDate);
    //                             $year1 = date('Y', $ts1);
    //                             $year2 = date('Y', $ts2);
    //                             $month1 = date('m', $ts1);
    //                             $month2 = date('m', $ts2);
    //                             $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
    //                             $investmentMonths = $monthDiff;
    //                             $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereIn('transaction_type', [2,4])->whereDate('created_at','>=',$mInvestment->last_deposit_to_ssb_date)->sum('deposit');
    //                         }else{
    //                             $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                             $sDate = date("Y-m-d", strtotime(convertDate($mInvestment->created_at)));
    //                             $ts1 = strtotime($sDate);
    //                             $ts2 = strtotime($cDate);
    //                             $year1 = date('Y', $ts1);
    //                             $year2 = date('Y', $ts2);
    //                             $month1 = date('m', $ts1);
    //                             $month2 = date('m', $ts2);
    //                             $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
    //                             $investmentMonths = $monthDiff;
    //                         }
    //                         for ($i=1; $i <= $investmentMonths ; $i++){
    //                             $val = $mInvestment;
    //                             $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
    //                             $cMonth = date('m');
    //                             $cYear = date('Y');
    //                             $cuurentInterest = $mInvestment->interest_rate;
    //                             $totalDeposit = $totalInvestmentAmount;
    //                             $ts1 = strtotime($mInvestment->created_at);
    //                             $ts2 = strtotime($nDate);
    //                             $year1 = date('Y', $ts1);
    //                             $year2 = date('Y', $ts2);
    //                             $month1 = date('m', $ts1);
    //                             $month2 = date('m', $ts2);
    //                             $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
    //                             $defaulterInterest = 0;
    //                             if($val->deposite_amount*$monthDiff <= $totalDeposit){
    //                                 $aviAmount = $val->deposite_amount;
    //                                 $total = $total+$val->deposite_amount;
    //                                 if($monthDiff % 3 == 0 && $monthDiff != 0){
    //                                     $total = $total+$regularInterest;
    //                                     $cInterest = $regularInterest;
    //                                 }else{
    //                                     $total = $total;
    //                                     $cInterest = 0;
    //                                 }
    //                                 $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
    //                                 $addInterest = ($cuurentInterest-$defaulterInterest);
    //                                 $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
    //                                 $interest = number_format((float)$a, 2, '.', '');
    //                                 $totalInterestDeposit = $totalInterestDeposit+$interest;
    //                             }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
    //                                 $checkAmount = ($totalDeposit-$val->deposite_amount*($monthDiff-1));
    //                                 if($checkAmount > 0){
    //                                   $aviAmount = $checkAmount;
    //                                   $total = $total+$checkAmount;
    //                                     if($monthDiff % 3 == 0 && $monthDiff != 0){
    //                                         $total = $total+$regularInterest;
    //                                         $cInterest = $regularInterest;
    //                                     }else{
    //                                         $total = $total;
    //                                         $cInterest = 0;
    //                                     }
    //                                     $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
    //                                     $addInterest = ($cuurentInterest-$defaulterInterest);
    //                                     $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
    //                                     $interest = number_format((float)$a, 2, '.', '');
    //                                 }else{
    //                                     $aviAmount = 0;
    //                                     $total = 0;
    //                                     $cuurentInterest = 0;
    //                                     $interest = 0;
    //                                     $addInterest = 0;
    //                                 }
    //                                 $totalInterestDeposit = $totalInterestDeposit+$interest;
    //                             }
    //                         }
    //                         if($depositInterest){
    //                             $availableAmountFd = ($depositInterest->available_amount*9/100)+$depositInterest->available_amount;
    //                             $fAmount = round($totalDeposit+$totalInterestDeposit+$availableAmountFd);
    //                         }else{
    //                             $fAmount = round($totalDeposit+$totalInterestDeposit);
    //                         }
    //                         $isDefaulter = 0;
    //                         $refundAmount = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$mInvestment->id)->where('plan_type_id',3)->sum('yearly_deposit_amount');
    //                         if($refundAmount){
    //                             $interstAmount = round(($fAmount+$refundAmount)-$totaldepositAmount);
    //                             $finalAmount = $fAmount-$interstAmount;
    //                             $investAmount = $finalAmount;
    //                         }else{
    //                             $interstAmount = $fAmount-$totaldepositAmount;
    //                             $finalAmount = $fAmount-$interstAmount;
    //                             $investAmount = $finalAmount;
    //                         }
    //                     }
    //                 }else{
    //                     $finalAmount = 0;
    //                     $isDefaulter = 1;
    //                     $investAmount = 0;
    //                     $interstAmount = 0;
    //                 }
    //             }
    //         }
    //     } else{
    //             $isDefaulter = 0;
    //             $finalAmount = 0;
    //     }
    //     $return_array = compact('investmentDetails','isDefaulter','finalAmount','message','status');
    //     return json_encode($return_array);
    // }
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
        // $mDetails = \App\Models\MemberCompany::with('savingAccount')->where('member_id', $mId)->whereCompanyId($request->company_id)->first();
        // $count = \App\Models\MemberCompany::with('savingAccount')->where('member_id', $mId)->whereCompanyId($request->company_id)->count();
        $mDetails = \App\Models\Member::with([
            'savingAccount_Customnew' => function ($q) use ($companyId) {
                $q->whereCompanyId($companyId);
            }
        ])->where('member_id', $mId)->first();
        $count = \App\Models\Member::with([
            'savingAccount_Customnew' => function ($q) use ($companyId) {
                $q->whereCompanyId($companyId);
            }
        ])->where('member_id', $mId)->count();
        $return_array = compact('mDetails', 'count');
        return json_encode($return_array);
    }
    /*Call death help maturity page*/
    public function demandAdvicematurity()
    {
        if (check_my_permission(Auth::user()->id, "85") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Maturity Management';
        return view('templates.admin.demand-advice.demand_advice_maturity', $data);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function demandAdvicematurityList(Request $request)
    {
        if ($request->ajax() && check_my_permission(Auth::user()->id, "85") == "1") {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = DemandAdvice::select('id', 'date', 'maturity_payment_mode', 'investment_id', 'voucher_number', 'mobile_number', 'account_number', 'status', 'payment_type', 'sub_payment_type', 'is_mature', 'branch_id', 'letter_photo_id', 'company_id')
                ->with([
                    'investment' => function ($q) {
                        $q->select('id', 'member_id', 'plan_id', 'customer_id');
                    }
                ])
                ->with([
                    'expenses' => function ($q) {
                        $q->select('id', 'demand_advice_id');
                    }
                ])
                ->with([
                    'company' => function ($q) {
                        $q->select('id', 'name');
                    }
                ])
                ->with([
                    'branch' => function ($q) {
                        $q->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                    }
                ])
                ->where('is_mature', 1)->where('is_deleted', 0);
            if (Auth::user()->branch_id > 0) {
                $id = Auth::user()->branch_id;
                $data = $data->where('branch_id', '=', $id);
            }
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
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $branchId = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', '=', $branchId);
                }
                if (isset($arrFormData['company_id']) && $arrFormData['company_id'] != '') {
                    $companyId = $arrFormData['company_id'];
                    $data = $data->getCompanyRecords('CompanyId', $companyId);
                }
                if ($arrFormData['advice_type'] != '') {
                    $advice_id = $arrFormData['advice_type'];
                    if ($advice_id == 0 || $advice_id == 1 || $advice_id == 2) {
                        $data = $data->where('payment_type', '=', $advice_id);
                    } elseif ($advice_id == 3) {
                        $data = $data->where('payment_type', '=', 3)->where('death_help_catgeory', '=', 0);
                    } elseif ($advice_id == 4) {
                        $data = $data->where('payment_type', '=', 3)->where('death_help_catgeory', '=', 1);
                    }
                }
            } else {
                $data = $data->where('payment_type', 5);
            }
            $count = $data->count('id');
            $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                // echo '<pre>';
                // print_r($row['company']->name);
                // die;
                // if(!isset($row['investment']->plan_id))
                // {
                //     dd($row->id);
                // }
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['maturity_payment_mode'] = $row->maturity_payment_mode;
                $val['company_name'] = $row['company']->name;
                // dd( $val['company_name']);
                $val['branch_name'] = $row['branch']->name;
                $val['branch_code'] = $row['branch']->branch_code;
                // $val['sector'] = $row['branch']->sector;
                // $val['regan'] = $row['branch']->regan;
                // $val['zone'] = $row['branch']->zone;
                $val['date'] = date("d/m/Y", strtotime($row->date));
                if ($row['investment']) {
                    $val['member_name'] = $row['investment']->memberCompany->member->first_name . ' ' . $row['investment']->memberCompany->member->last_name;
                } else {
                    $val['member_name'] = 'N/A';
                }
                $val['maturity_amount_tds'] = '<input type="text" name="maturity_amount_tds[' . $row->id . ']" class="' . $row->investment_id . '_maturity_amount_tds maturity_amount_tds" value="" style="width:80%" readonly> &#8377 <input type="hidden" name="tds_interest[' . $row->id . ']" class="' . $row->investment_id . '_tds_interest"><input type="hidden" name="tds_interest_on_amount[' . $row->id . ']" class="' . $row->investment_id . '_tds_interest_on_amount"> <input type="hidden" name="total_deposit_amount[' . $row->id . ']" class="' . $row->investment_id . '_total_deposit_amount">';
                $val['maturity_amount_till_date'] = '<input type="text" name="maturity_amount_till_date[' . $row->id . ']" class="' . $row->investment_id . '_maturity_amount_till_date maturity_amount_till_date" value="" style="width:80%" readonly> &#8377';
                $val['maturity_amount_payable'] = '<input type="text" name="maturity_amount_payable[' . $row->id . ']" class="' . $row->investment_id . '_maturity_amount_payable maturity_amount_payable" data-investment-id="' . $row->investment_id . '" value="" readonly style="width:80%"> &#8377';
                $val['voucher_number'] = $row->voucher_number;
                $val['mobile_number'] = $row->mobile_number;
                if ($row->account_number) {
                    $val['account_number'] = $row->account_number;
                } else {
                    $val['account_number'] = 'N/A';
                }
                if ($row->status == 0) {
                    $status = 'Pending';
                } else {
                    $status = 'Approved';
                }
                $val['status'] = $status;
                $img = '';
                if (isset($row->letter_photo_id)) {
                    $file_name = getFileData($row->letter_photo_id);
                    $fname = $file_name['0']->file_name;
                    $folderName = 'demand-advice/maturity-prematurity/' . $fname;
                    $url = ImageUpload::generatePreSignedUrl($folderName);
                    $img = '<a href= "' . $url . '" target="blank">' . $file_name['0']->file_name . '</a>';
                } else {
                    $img = '';
                }
                $val['letter_photos'] = $img;
                $url = URL::to("admin/demand-advice/edit-demand-advice/" . $row->id . "");
                $btn = '';
                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                if ($row->is_mature == 1 && $row->status == 0) {
                    $btn .= '<a class="dropdown-item" href="' . $url . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                }
                if (!isset($row['investment']->plan_id)) {
                    // dd($row->id);
                }
                if (isset($row['investment']->plan_id)) {
                    $investmentPLanID = $row['investment']->plan_id;
                } else {
                    $investmentPLanID = "";
                }
                if ($row->is_mature == 1) {
                    $injvestmentdata = Memberinvestments::where('account_number',$row->account_number)->where('id',$row->investment_id)->select('tenure','created_at','deposite_amount','account_number')->first();
                    $btn .= '<a class="dropdown-item ' . $row->investment_id . '-calculate-maturity calculate-maturity" href="javascript:void(0);" data-val="0" data-payment-type="' . $row->payment_type . '" data-sub-payment-type="' . $row->sub_payment_type . '" data-id="' . $row->investment_id . '"  data-advice-id="' . $row->id . '" data-investmentType="' . $investmentPLanID . '" data-tenure="'.($injvestmentdata->tenure*12).'" data-creation="'.date("d/m/Y", strtotime($injvestmentdata->created_at)).'" data-deno="'.$injvestmentdata->deposite_amount.'" data-acc="'.$injvestmentdata->account_number.'"><i class="fas fa-recycle"></i>Calculate Maturity</a>';
                }
                $btn .= '</div></div></div>';
                $val['calculate_maturity'] = $btn;
                $rowReturn[] = $val;
            }
            $output = array(
                "branch_id" => Auth::user()->branch_id,
                "draw" => $_POST['draw'],
                "recordsTotal" => $count,
                "recordsFiltered" => $count,
                "data" => $rowReturn,
            );
            return json_encode($output);
        }
    }
    
    public function getInvestmentData(Request $request)
    {
        $investmentId = $request->investmentId;
        $demandId = $request->demandId;
        $demadAdvice = DemandAdvice::where('id', $demandId)->where('is_deleted', 0)->first();
        $branchId = $demadAdvice->branch_id;
        $state_id = getBranchDetail($branchId)->state_id;
        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $state_id);
        $mInvestment = Memberinvestments::where('id', $investmentId)->first();
        $lastRecord = Daybook::whereIn('transaction_type', [2, 4])->where('account_no', $mInvestment->account_number)->where('is_deleted', 0)->orderBy('id', 'desc')->first('created_at');
        $to = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $mInvestment->created_at);
        $Transactionenddate = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $lastRecord->created_at);
        $investmentId = $request->investmentId;
        $to = ($globaldate >= $mInvestment->maturity_date) ? \Carbon\Carbon::parse($mInvestment->maturity_date) : \Carbon\Carbon::parse($globaldate);
        $paymentType = $request->paymentType;
        $subPaymentType = $request->subPaymentType;
       
       
        $interestData = '';
        $ActualInterest = 0;
        $from = \Carbon\Carbon::parse($mInvestment->created_at);
        // $investmentMonths = round($from->floatDiffInMonths($to));
        if ($request->paymentType == 1) { 
            /***For maturity case i have done this on 29-12-2023 on saying of mukesh sir and anoop sir on one to one conversation between mukesh sir and anoop sir i have done this Regards Mahesh */
            $investmentMonths = round($from->floatDiffInMonths($to));
        } else {
            $investmentMonths = floor($from->floatDiffInMonths($to));
        }
        // $investmentMonths = $to->diffInMonths($from,true);
        // $interestData = getplanroi($mInvestment->plan_id); Updated on 11 january 2024 by mahesh
        $interestData = getplanroi($mInvestment->plan_id,$mInvestment->created_at,$mInvestment->tenure);
        
        if ($mInvestment->plan->plan_sub_category_code != 'I') {
            $checkRoi = getRoi($interestData, $investmentMonths, $mInvestment, $subPaymentType);
            $ActualInterest = $checkRoi['ActualInterest'];
            if (!$checkRoi['roiExist']) {
                $response = [
                    'message' => 'Maturity Setting Not Updated for this Plan!',
                    'status' => 400,
                ];
                return response()->json($response);
            }
        }
        if ($subPaymentType == 4 && $mInvestment->plan->death_help == 0) {
            $response = [
                'message' => 'Death Help Permission is not Allowed on this Plan  !',
                'status' => 400,
            ];
            return response()->json($response);
        }
        // if ($subPaymentType == 5 && $mInvestment->plan->death_help == 0) {
        //     $response = [
        //         'message' => 'Death Claim Permission is not Allowed on this Plan  !',
        //         'status' => 400,
        //     ];
        //     return response()->json($response);
        // }
        $maturity_date = date('Y-m-d', strtotime($mInvestment->created_at . ' + ' . ($mInvestment->tenure) . ' year'));
        $globalDate = date('Y-m-d', strtotime($globaldate));
        $idProofDetail = \App\Models\MemberIdProof::where('member_id', $mInvestment->member_id)->first();
        $formGShow = \App\Models\Form15G::where('member_id', $mInvestment->member_id)->whereStatus('1')->where('is_deleted','0')->orderBy('created_at', 'DESC')->first();
        $investmentData = Daybook::select('investment_id', 'deposit', 'created_at')->where('investment_id', $investmentId)->whereIn('transaction_type', [2, 4])->orderby('created_at', 'asc')->get();
        $view = view("templates.admin.demand-advice.maturity_calculation", compact('investmentData', 'mInvestment', 'maturity_date', 'paymentType', 'subPaymentType', 'demadAdvice', 'interestData', 'globalDate', 'Transactionenddate', 'idProofDetail', 'formGShow', 'ActualInterest', 'investmentMonths'))->render();
        return response()->json(['html' => $view]);
    }
    // public function saveInvestmentMaturityAmount(Request $request)
    // {
    //     $demandAdviceIds = $request->demand_advice_id;
    //     DB::beginTransaction();
    //     try {
    //         foreach ($demandAdviceIds as $key => $value) {
    //             if($request->maturity_amount_till_date[$key]){
    //                 $daData = [
    //                     'maturity_amount_till_date' => $request->maturity_amount_till_date[$key],
    //                     //'maturity_amount_payable' => $request->maturity_amount_payable[$key],
    //                     'maturity_amount_payable' => $request->maturity_amount_payable[$key]-$request->maturity_amount_tds[$key],
    //                     'tds_percentage' => $request->tds_interest[$key],
    //                     'tds_per_amount' => $request->tds_interest_on_amount[$key],
    //                     'tds_amount' => $request->maturity_amount_tds[$key],
    //                     'final_amount' => $request->maturity_amount_payable[$key]-$request->maturity_amount_tds[$key],
    //                     'is_mature' => 0,
    //                 ];
    //                 $demandAdvice = DemandAdvice::find($key);
    //                 $demandAdvice->update($daData);
    //             }
    //         }
    //     DB::commit();
    //     } catch (\Exception $ex) {
    //         DB::rollback();
    //         return back()->with('alert', $ex->getMessage());
    //     }
    //     return back()->with('success', 'Successfully created maturity');
    // }
    public function saveInvestmentMaturityAmount(Request $request)
    {
        $demandAdviceIds = $request->demand_advice_id;
        DB::beginTransaction();
        try {
            foreach ($demandAdviceIds as $key => $value) {
                if ($request->maturity_amount_till_date[$key] >= 0) {
                    $daData = [
                        'maturity_amount_till_date' => $request->maturity_amount_till_date[$key],
                        //'maturity_amount_payable' => $request->maturity_amount_payable[$key],
                        'maturity_amount_payable' => $request->maturity_amount_payable[$key] - $request->maturity_amount_tds[$key],
                        'tds_percentage' => $request->tds_interest[$key],
                        'tds_per_amount' => $request->tds_interest_on_amount[$key],
                        'tds_amount' => $request->maturity_amount_tds[$key],
                        'final_amount' => $request->maturity_amount_payable[$key],
                        'total_deposit' => $request->total_deposit_amount[$key] ?? 0,
                        'is_mature' => 0,
                        'interestAmount' => $request->interest[$key],
                    ];
                    $demandAdvice = DemandAdvice::find($key);
                    $demandAdvice->update($daData);
                }
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
        if (check_my_permission(Auth::user()->id, "86") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Demand Advice Application';
        return view('templates.admin.demand-advice.application', $data);
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
        if ($request->ajax() && $arrFormData['advice_type'] && $arrFormData['company_id']) {
            $data = DemandAdvice::select('id', 'investment_id', 'maturity_payment_mode', 'tds_amount', 'maturity_amount_till_date', 'maturity_prematurity_amount', 'payment_mode', 'payment_type', 'opening_date', 'sub_payment_type', 'date', 'voucher_number', 'maturity_amount_payable', 'final_amount', 'account_number', 'bank_account_number', 'bank_ifsc', 'is_print', 'status', 'employee_id', 'branch_id', 'owner_id', 'letter_photo_id', 'is_mature', 'advanced_amount', 'interestAmount', 'company_id', 'maturity_payment_mode')
                ->with([
                    'investment:id,member_id,associate_id,created_at,customer_id',
                    'investment.member:id,member_id,first_name,last_name,associate_code',
                    'investment.member.memberNomineeDetails:id,member_id,name',
                    'investment.associateMember:id,associate_no,first_name,last_name,associate_code',
                    'investment.ssb:id,account_no',
                    'branch:id,name,branch_code,sector,regan,zone',
                    'company:id,name',
                    'demandAmountHead:id,type_id,amount,head_id',
                    'sumdeposite:id,investment_id,deposit,transaction_type',
                    'expensess'
                ])->where('is_deleted', '0')
                ->where('is_reject', '0')
            ;
            if (Auth::user()->branch_id > 0) {
                $id = Auth::user()->branch_id;
                $data = $data->where('branch_id', '=', $id);
            }
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
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $branchId = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', '=', $branchId);
                }
                if (isset($arrFormData['account_number']) && $arrFormData['account_number'] != '') {
                    $account_number = $arrFormData['account_number'];
                    $data = $data->where('account_number', '=', $account_number);
                }
                if (isset($arrFormData['company_id']) && $arrFormData['company_id'] != '') {
                    $companyId = $arrFormData['company_id'];
                    // $data = $data->getCompanyRecords('CompanyId', $companyId);
                    $data = $data->where('company_id', $companyId);
                }
                if ($arrFormData['advice_type'] != '') {
                    $advice_id = $arrFormData['advice_type'];
                    $advice_type_id = $arrFormData['expense_type'];
                    if ($advice_id == 1 || $advice_id == 2 || $advice_id == 3 || $advice_id == 4) {
                        $data = $data->where('is_mature', 0);
                    }
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
                if (isset($arrFormData['requested_payment_mode']) && $arrFormData['requested_payment_mode'] != '') {
                    $requestedPaymentMode = $arrFormData['requested_payment_mode'];
                    // $data = $data->getCompanyRecords('CompanyId', $companyId);
                    $data = $data->where('maturity_payment_mode', 'LIKE', '%' . $requestedPaymentMode . '%');
                }
            } else {
                $data = $data->where('payment_type', 1);
            }
            $data = $data->where('is_mature', 0)->where('status', 0);
            //$data1 = $data->orderby('id','DESC')->get();
            $count = $data->count('id');
            $total = $count;
            $data = $data->offset($_POST['start'])->limit($_POST['length']);
            $data = $data->orderby('id', 'DESC')->get();
            $totalCount = ($total);
            $rowReturn = array();
            $sno = $_POST['start'];
            foreach ($data as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['maturity_payment_mode'] = $row->maturity_payment_mode;
                $val['checkbox'] = '<input type="checkbox" name="demand_advice_record" value="' . $row->id . '" id="demand_advice_record">';
                $val['branch_name'] = $row['branch'] ? $row['branch']->name : 'N/A';
                $val['company_name'] = $row['company'] ? $row['company']->name : 'N/A';
                // $val['branch_code'] = $row['branch']->branch_code;
                // $val['zone'] = $row['branch']->sector;
                if (isset($row->account_number)) {
                    $acc = $row->account_number;
                    // $account_number = $row->account_number;
                    $account_number = ($row['investment'] ? $row['investment']['member'] ? '('.$row['investment']['member']->member_id.') ' : '' : ''). $row->account_number;
                } elseif (isset($row->investment_id)) {
                    $acc = $row['investment']->account_number;
                    // $account_number = $row['investment']->account_number; //getInvestmentDetails($row->investment_id)->account_number;
                    $account_number = ($row['investment'] ? $row['investment']['member'] ? '('.$row['investment']['member']->member_id.') ' : '' : '').  $row['investment']->account_number;
                } else {
                    $account_number = 'N/A';
                }
                $val['account_number']  = $account_number;
                if ($row->investment_id) {
                    $associate_id = $row['investment']->member_id;
                    getInvestmentDetails($row->investment_id)->member_id;
                    $memberName = $row['investment']['member']->first_name . ' ' . $row['investment']['member']->last_name; 
                    $val['member_name'] = '<a target="_blank" href="'.route("admin.member.detail",$row['investment']['member']->id).'?type=0" >'.$memberName.'</a>';
                } else {
                    $val['member_name'] = "N/A";
                }
                if ($row->investment_id) {
                    $associate_code = "N/A";
                    if ($row['investment']) // getInvestmentDetails($row->investment_id)
                    {
                        $associate_id = $row['investment']->associate_id; //getInvestmentDetails($row->investment_id)->associate_id;
                        $associate_code = "N/A";
                        if ($associate_id) {
                            $associate_code = $row['investment']['associateMember']->associate_no; //getMemberData($associate_id)->associate_no;
                        }
                    }
                } else {
                    $associate_code = "N/A";
                }
                // $val['associate_code'] = $associate_code;
                $loanDetail = '';
                if ($row['investment']) //getInvestmentDetails($row->investment_id)
                {
                    $member_id = $row['investment']->member_id; //getInvestmentDetails($row->investment_id)->member_id;
                    $customer_id = $row['investment']->customer_id; //getInvestmentDetails($row->investment_id)->member_id;
                    $loanDetail = '';
                    /*
                    if ($member_id) {
                        $loanDetail = $this->getData(new Memberloans(), $member_id, 0);
                        if (!$loanDetail) {
                            $loanDetail = $this->getData(new Grouploans(), $member_id, 1);
                        }
                    }
                    */
                    if ($customer_id) {
                        $loanDetail = $this->getDatabyCustomer(new Memberloans(), $customer_id, 0);
                        if ($loanDetail == 'No') {
                            $loanDetail = $this->getDatabyCustomer(new Grouploans(), $customer_id, 1);
                        }
                    }
                }
                $val['is_loan'] = $loanDetail;
                if ($row->investment_id) {
                    $associate_name = "N/A";
                    if ($row['investment']) //getInvestmentDetails($row->investment_id)
                    {
                        $associate_id = $row['investment']->associate_id; //getInvestmentDetails($row->investment_id)->associate_id;
                        $associate_name = "N/A";
                        if ($associate_id) {
                            $associate_name = $row['investment']['associateMember']->first_name . ' ' . $row['investment']['associateMember']->last_name; //getMemberData($associate_id)->first_name.' '.getMemberData($associate_id)->last_name;
                        }
                    }
                } else {
                    $associate_name = "N/A";
                }
                $val['associate_name'] = $associate_name;
                if ($row->investment_id) {
                    $investmentAmountDeposit = Daybook::where('transaction_type', '<>', 19)
                        ->where('account_no', $acc)
                        ->where('is_deleted', 0)
                        ->sum('deposit');
                    $investmentAmountwithdrawal = Daybook::where('transaction_type', '<>', 19)
                        ->where('account_no', $acc)
                        ->where('is_deleted', 0)
                        ->sum('withdrawal');
                    $tAmount = round($investmentAmountDeposit - $investmentAmountwithdrawal) . ' &#8377';
                } else {
                    $tAmount = $row['expenses']->sum('amount');
                }
                $val['total_amount'] = $tAmount;
                if ($row->tds_amount) {
                    $val['tds_amount'] = round($row->tds_amount) . ' &#8377';
                } else {
                    $val['tds_amount'] = 'N/A';
                }
                $amount = '';
                if ($row->investment_id) {
                    // $investmentAmount = Daybook::where('investment_id',$row->investment_id)->whereIn('transaction_type',[2,4])->sum('deposit');
                    $amount = round(number_format((float) $row->final_amount, 2, '.', '')
                        - number_format((float) $tAmount, 2, '.', '')) . ' &#8377';
                }
                $val['interest_amount'] = $row->interestAmount;
                if ($row->payment_type == 2) {
                    $val['total_payable_amount'] = round($row->final_amount) . ' &#8377';
                } else if ($row->maturity_amount_payable) {
                    $val['total_payable_amount'] = round($row->maturity_amount_payable + $row->tds_amount) . ' &#8377';
                } else {
                    if ($row->payment_type == 0 && $row->sub_payment_type == 3) {
                        $val['total_payable_amount'] = round($row->amount) . ' &#8377';
                    } else {
                        $val['total_payable_amount'] = $row['expenses']->sum('amount');
                    }
                }
                if ($row->payment_type == 2) {
                    $val['final_amount'] = round($row->final_amount) . ' &#8377';
                } else {
                    if ($row->final_amount) {
                        $val['final_amount'] = round($row->final_amount) . ' &#8377';
                    } elseif ($row->maturity_amount_payable) {
                        $val['final_amount'] = round($row->maturity_amount_payable - $row->tds_amount) . ' &#8377';
                    } else if (isset($row['expenses']->amount)) {
                        $val['final_amount'] = $row['expenses']->amount;
                    } else if (isset($row->advanced_amount)) {
                        $val['final_amount'] = $row->advanced_amount;
                    } else {
                        $val['final_amount'] = 'N/A';
                    }
                }
                $val['date'] = date("d/m/Y", strtotime($row->date));
                if (isset($row->investment_id)) {
                    $investmentDetail = $row['investment']->created_at; // getInvestmentDetails($row->investment_id)->created_at;
                    $val['created_at'] = date("d/m/Y", strtotime($investmentDetail));
                } else {
                    $val['created_at'] = 'N/A';
                }
                if ($row->payment_type == 0) {
                    $val['advice_type'] = 'Expenses';
                } elseif ($row->payment_type == 1) {
                    $val['advice_type'] = 'Maturity';
                } elseif ($row->payment_type == 2) {
                    $val['advice_type'] = 'Prematurity';
                } elseif ($row->payment_type == 3) {
                    if ($row->sub_payment_type == '4') {
                        $val['advice_type'] = 'Death Help';
                    } elseif ($row->sub_payment_type == '5') {
                        $val['advice_type'] = 'Death Claim';
                    }
                }
                if ($row->sub_payment_type == '0') {
                    $val['expense_type'] = 'Fresh Expense';
                } elseif ($row->sub_payment_type == '1') {
                    $val['expense_type'] = 'TA Advanced';
                } elseif ($row->sub_payment_type == '2') {
                    $val['expense_type'] = 'Advanced salary';
                } elseif ($row->sub_payment_type == '3') {
                    $val['expense_type'] = 'Advanced Rent';
                } elseif ($row->sub_payment_type == '4') {
                    $val['expense_type'] = 'N/A';
                } elseif ($row->sub_payment_type == '5') {
                    $val['expense_type'] = 'N/A';
                } else {
                    $val['expense_type'] = 'N/A';
                }
                $val['voucher_number'] = $row->voucher_number;
                $val['advice_number'] = '';
                if ($row->status == 0) {
                    $status = 'Pending';
                } else {
                    $status = 'Approved';
                }
                $img = '';
                if (isset($row->letter_photo_id)) {
                    $file_name = getFileData($row->letter_photo_id);
                    $fname = $file_name['0']->file_name;
                    $folderName = 'demand-advice/maturity-prematurity/' . $fname;
                    $url = ImageUpload::generatePreSignedUrl($folderName);
                    $img = '<a href="' . $url . '" target="blank">' . $file_name['0']->file_name . '</a>';
                } else {
                    $img = '';
                }
                $val['letter_photos'] = $img;
                $val['status'] = $status;
                $url = URL::to("admin/demand-advice/edit-demand-advice/" . $row->id . "");
                $deleteurl = URL::to("admin/delete-demand-advice/" . $row->id . "");
                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                if ($row->is_mature == 1 && $row->status == 0 || $row->is_reject == 0) {
                    $btn .= '<a class="dropdown-item" href="' . $url . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                }
                $btn .= '<a class="dropdown-item reject-demand-advice" href="javascript:void(0);" data-toggle="modal" data-target="#formmodal" modal-title = "Cause of Rejection" demandId = "' . $row->id . '" ><i class="icon-paperplane" ></i>Reject </a>';
                $btn .= '<a class="dropdown-item delete-demand-advice" href="' . $deleteurl . '"><i class="fas fa-trash-alt"></i>Delete </a>';
                $btn .= '</div></div></div>';
                $val['action'] = $btn;
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
            return json_encode($output);
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
    /**public function approveDemandAdviceView(Request $request)
    {
        if ($request['selected_records'] && isset($request['selected_records'])) {
            $sRecord = explode(',', $request['selected_records']);
            $data['demandAdvice'] = DemandAdvice::with('expenses', 'branch', 'company')->with([
                'investment' => function ($q) {
                    $q->with(['investmentNominee', 'ssb']);
                }
            ])->whereIn('id', $sRecord)->where('is_deleted', 0)->get();
            $data['selectedRecords'] = $request['selected_records'];
        } else {
            $data['demandAdvice'] = array();
            $data['selectedRecords'] = 0;
        }
        $data['cBanks'] = SamraddhBank::with('bankAccount')->where("status", "1")->get();
        $data['branch'] = \App\Models\CompanyBranch::with('branch:id,name,branch_code')->where('company_id', $data['demandAdvice'][0]->company_id)->where('status', 1)->get(['id', 'branch_id', 'status', 'company_id']);
        $data['cheques'] = SamraddhCheque::select('cheque_no', 'account_id')->where('status', 1)->get();
        $data['assets_category'] = AccountHeads::whereIn('parent_id', [9])->where('status', 0)->get();
        $head_ids = array(9);
        $subHeadsIDS = AccountHeads::where('head_id', 9)->where('status', 0)->pluck('head_id')->toArray();
        if (count($subHeadsIDS) > 0) {
            $head_id = array_merge($head_ids, $subHeadsIDS);
            $return_array = get_change_sub_account_head($head_ids, $subHeadsIDS, true);
        }
        foreach ($return_array as $key => $value) {
            $ids[] = $value;
        }
        $data['assets_subcategory'] = AccountHeads::whereIn('parent_id', $ids)->where('status', 0)->get();
        if ($data['demandAdvice'][0]->payment_type == 0 && $data['demandAdvice'][0]->sub_payment_type == 0) {
            $data['title'] = 'Demand Advice | Approve';
            $data['type'] = 0;
            $data['subType'] = 0;
            return view('templates.admin.demand-advice.fresh_expense_approve', $data);
        }
        if ($data['demandAdvice'][0]->payment_type == 4) {
            $data['title'] = 'Emergency Maturity I Transfer';
            $data['type'] = $data['demandAdvice'][0]->payment_type;
            $data['subType'] = $data['demandAdvice'][0]->sub_payment_type;
            return view('templates.admin.demand-advice.approve-emergancy-maturity', $data);
        } else {
            $data['title'] = 'Demand Advice | Approve';
            $data['type'] = $data['demandAdvice'][0]->payment_type;
            $data['subType'] = $data['demandAdvice'][0]->sub_payment_type;
            $data['ssb'] = $data['demandAdvice'][0]->ssb_account;
            return view('templates.admin.demand-advice.approve', $data);
        }
    } */
    /**Updated by Mahesh on 9 january */
    public function approveDemandAdviceView(Request $request)
    {
        if ($request['selected_records'] && isset($request['selected_records'])) {
            $max_date = [];
            $sRecord = explode(',', $request['selected_records']);
            $data['demandAdvice'] = DemandAdvice::with('expenses', 'branch', 'company')->with([
                'investment' => function ($q) {
                    $q->with(['investmentNominee', 'ssb']);
                }
            ])->whereIn('id', $sRecord)->where('is_deleted', 0)->get();
            $data['selectedRecords'] = $request['selected_records'];
            foreach ($data['demandAdvice'] as $datas) {
                array_push($max_date,$datas['date']);
            }
            $data['highestDate'] = max($max_date);
        } else {
            $data['demandAdvice'] = array();
            $data['selectedRecords'] = 0;
        }
        $data['cBanks'] = SamraddhBank::with('bankAccount')->where("status", "1")->get();
        $data['branch'] = \App\Models\CompanyBranch::with('branch:id,name,branch_code')->where('company_id', $data['demandAdvice'][0]->company_id)->where('status', 1)->get(['id', 'branch_id', 'status', 'company_id']);
        $data['cheques'] = SamraddhCheque::select('cheque_no', 'account_id')->where('status', 1)->get();
        $data['assets_category'] = AccountHeads::whereIn('parent_id', [9])->where('status', 0)->get();
        $head_ids = array(9);
        $subHeadsIDS = AccountHeads::where('head_id', 9)->where('status', 0)->pluck('head_id')->toArray();
        if (count($subHeadsIDS) > 0) {
            $head_id = array_merge($head_ids, $subHeadsIDS);
            $return_array = get_change_sub_account_head($head_ids, $subHeadsIDS, true);
        }
        foreach ($return_array as $key => $value) {
            $ids[] = $value;
        }
        $data['assets_subcategory'] = AccountHeads::whereIn('parent_id', $ids)->where('status', 0)->get();
        if ($data['demandAdvice'][0]->payment_type == 0 && $data['demandAdvice'][0]->sub_payment_type == 0) {
            $data['title'] = 'Demand Advice | Approve';
            $data['type'] = 0;
            $data['subType'] = 0;
            return view('templates.admin.demand-advice.fresh_expense_approve', $data);
        }
        if ($data['demandAdvice'][0]->payment_type == 4) {
            $data['title'] = 'Emergency Maturity I Transfer';
            $data['type'] = $data['demandAdvice'][0]->payment_type;
            $data['subType'] = $data['demandAdvice'][0]->sub_payment_type;
            return view('templates.admin.demand-advice.approve-emergancy-maturity', $data);
        } else {
            $data['title'] = 'Demand Advice | Approve';
            $data['type'] = $data['demandAdvice'][0]->payment_type;
            $data['subType'] = $data['demandAdvice'][0]->sub_payment_type;
            $data['ssb'] = $data['demandAdvice'][0]->ssb_account;
            return view('templates.admin.demand-advice.approve', $data);
        }
    }
    public function getBankDayBookAmount(Request $request)
    {
        $fromBankId = $request->fromBankId;
        $date = date("Y-m-d", strtotime(convertDate($request->date)));
        // $bankRes = SamraddhBankClosing::where('bank_id',$fromBankId)->whereDate('entry_date',$date)/*->orderBy('entry_date', 'desc')*/->first();
        // if($bankRes){
        //     $bankDayBookAmount = (int)$bankRes->balance;
        // }else{
        //     $bankRes = SamraddhBankClosing::where('bank_id',$fromBankId)->whereDate('entry_date','<',$date)->orderby('entry_date','DESC')->first();
        //     $bankDayBookAmount = (int)$bankRes->balance;
        // }
        $return_array = compact('bankDayBookAmount');
        return json_encode($return_array);
    }
    // Edit Branch to ho
    public function getBranchDayBookAmount(Request $request)
    {
        $branch_id = $request->branch_id;
        $date = date("Y-m-d", strtotime(convertDate($request->date)));
        $microLoanRes = BranchCash::select('balance', 'loan_balance')->where('branch_id', $branch_id)->whereDate('entry_date', $date) /*->orderBy('entry_date', 'desc')*/->first();
        if ($microLoanRes) {
            $loanDayBookAmount = (int) $microLoanRes->loan_balance;
            $microDayBookAmount = (int) $microLoanRes->balance;
        } else {
            $microLoan = BranchCash::select('balance', 'loan_balance')->where('branch_id', $branch_id)->whereDate('entry_date', '<', $date)->orderby('entry_date', 'DESC')->first();
            $loanDayBookAmount = (int) $microLoan->loan_balance;
            $microDayBookAmount = (int) $microLoan->balance;
        }
        $return_array = compact('microDayBookAmount', 'loanDayBookAmount');
        return json_encode($return_array);
    }
    
    public function approvePayment(Request $request)
    {
        DB::beginTransaction();
        try {
            $entryTime = date("H:i:s");
            $tdsAmountonInterest = 0;
            $interest = 0;
            $ssbArray = array();
            $demandAdviceIds = explode(',', $request->selected_fresh_expense_records);
            $encodeDate = json_encode($demandAdviceIds);
            $arrs = array("investmentId" => 0, "type" => "13", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "All Payment ID", "data" => $encodeDate);
            foreach ($demandAdviceIds as $key => $value) {
                if ($request->type == 1 || $request->type == 2 || $request->type == 3 || $request->type == 4) {
                    $demandAdviceExpenses = DemandAdvice::with('investment')->where('id', $value)->first();
                    if ($demandAdviceExpenses->status == 1) {
                        return redirect()->route('admin.demand.application')->with('alert', 'Already approved!');
                    }
                    $companyId = $demandAdviceExpenses['investment']->company_id;
                    $planDetail = getPlanDetailCheck($demandAdviceExpenses['investment']->plan_id,$companyId);
                    $demandAdviceExpenses->payment_date = date('Y-m-d', strtotime(convertDate($request->payment_date)));
                    $demandAdviceExpenses->save();
                    Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($request->payment_date))));
                    $request['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request->payment_date)));
                    $request['branch_id'] = $demandAdviceExpenses->branch_id;
                    $keyVal = 0;
                    $cInterest = 0;
                    $regularInterest = 0;
                    $total = 0;
                    $collection = 0;
                    $monthly = array(10, 11);
                    $daily = array(7);
                    $preMaturity = array(4, 5);
                    $fixed = array(8, 9);
                    $samraddhJeevan = array(2, 6);
                    $moneyBack = array(3);
                    $totalDeposit = 0;
                    $totalInterestDeposit = 0;
                    $mInvestment = $demandAdviceExpenses['investment'];
                    $maturity_date = date('Y-m-d', strtotime($mInvestment->created_at . ' + ' . ($mInvestment->tenure) . ' year'));
                    $investmentData = Daybook::select('investment_id', 'deposit', 'created_at')->where('investment_id', $mInvestment->id)->whereIn('transaction_type', [2, 4])->where('is_deleted', 0)->whereDate('created_at', '<=', $maturity_date)->orderby('created_at', 'asc')->get();
                    
                    if (strpos($demandAdviceExpenses['investment']->account_number, 'R-') !== false && $mInvestment->plan_id == 3 && $mInvestment->last_deposit_to_ssb_date != '') {
                        $getMbTrsAmount = getMbTrsAmount($demandAdviceExpenses['investment']->id);
                        $cInterest = 0;
                        $regularInterest = 0;
                        $total = 0;
                        $totalDeposit = 0;
                        $totalInterestDeposit = 0;
                        $date1 = $getMbTrsAmount->created_at;
                        $date2 = date("Y-m-d");
                        $ts1 = strtotime($date1);
                        $ts2 = strtotime($date2);
                        $year1 = date('Y', $ts1);
                        $year2 = date('Y', $ts2);
                        $month1 = date('m', $ts1);
                        $month2 = date('m', $ts2);
                        $investmentMonths = (($year2 - $year1) * 12) + ($month2 - $month1);
                        $totalInvestmentAmount = $getMbTrsAmount->mb_fd_amount;
                        for ($i = 1; $i <= $investmentMonths; $i++) {
                            $mInvestment = $demandAdviceExpenses['investment'];
                            $nDate = date('Y-m-d', strtotime($mInvestment->created_at . ' + ' . $i . ' months'));
                            $cMonth = date('m');
                            $cYear = date('Y');
                            $cuurentInterest = $mInvestment->interest_rate;
                            $totalDeposit = $totalInvestmentAmount;
                            $ts1 = strtotime($mInvestment->created_at);
                            $ts2 = strtotime($nDate);
                            $year1 = date('Y', $ts1);
                            $year2 = date('Y', $ts2);
                            $month1 = date('m', $ts1);
                            $month2 = date('m', $ts2);
                            $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                            $defaulterInterest = 0;
                            $aviAmount = $totalDeposit;
                            $total = $total + $aviAmount;
                            if ($monthDiff % 3 == 0 && $monthDiff != 0) {
                                $total = $total + $regularInterest;
                                $cInterest = $regularInterest;
                            } else {
                                $total = $total;
                                $cInterest = 0;
                            }
                            $regularInterest = $regularInterest + (($cuurentInterest - $defaulterInterest) * $total / 1200);
                            $addInterest = ($cuurentInterest - $defaulterInterest);
                            $a = -$aviAmount + $aviAmount * (pow((1 + $addInterest / 400), (4 * $i / 12)));
                            $interest = number_format((float) $a, 2, '.', '');
                            $totalInterestDeposit = $totalInterestDeposit + $interest;
                        }
                        $eliAmount = round($totalInvestmentAmount + $totalInterestDeposit);
                        $iseliAmount = round($totalInvestmentAmount + $totalInterestDeposit);
                        $moneyBackAmount = $getMbTrsAmount->mb_amount;
                    } elseif (strpos($demandAdviceExpenses['investment']->account_number, 'R-') !== false) {
                        $eliAmount = 0;
                        $moneyBackAmount = 0;
                        //$iseliAmount = investmentEliAmount($mInvestment->id);
                        $iseliAmount = 0;
                    } else {
                        $moneyBackAmount = 0;
                        $eliAmount = 0;
                        $iseliAmount = 0;
                    }
                    
                    $sumAmount = $demandAdviceExpenses->maturity_amount_payable;
                    if ($demandAdviceExpenses->maturity_prematurity_amount && $request->type != 1) {
                        $investAmount = $demandAdviceExpenses->maturity_prematurity_amount;
                    } else {
                        // $investAmount = Daybook::where('investment_id', $demandAdviceExpenses['investment']->id)->whereIn('transaction_type', [2, 4])->where('is_deleted', 0)->sum('deposit');
                        /**Updated By Mahesh on 29 dec 2023 amount was going wrong in emergency maturity */
                        $investDAmount = Daybook::where('investment_id', $demandAdviceExpenses['investment']->id)->whereIn('transaction_type', [2, 4])->where('is_deleted', 0)->sum('deposit');
                        $investWAmount = Daybook::where('investment_id', $demandAdviceExpenses['investment']->id)->whereIn('transaction_type', [18])->where('is_deleted', 0)->sum('withdrawal');
                        $investAmount = $investDAmount - $investWAmount;
                         /**Updated By Mahesh on 16 jan 2024 amount was going wrong in  maturity */
                        $investAmount = InvestmentBalance::where('investment_id',$demandAdviceExpenses['investment']->id)->value('totalBalance');
                    }
                    if ($request->subtype == 5) {
                        $extraInterest = 0;
                        $interstAmount = $demandAdviceExpenses->final_amount - $demandAdviceExpenses->deposited_amount;
                    } else {
                        if ($investAmount == 0) {
                            $investAmount = $demandAdviceExpenses['investment']->deposite_amount + $iseliAmount;
                            $interstAmount = round($demandAdviceExpenses->maturity_amount_payable - $investAmount);
                        }
                        if ($investAmount < $demandAdviceExpenses->maturity_amount_payable) {
                            $extraInterest = 0;
                            $interstAmount = $demandAdviceExpenses->maturity_amount_payable - $investAmount;
                        } elseif ($investAmount > $demandAdviceExpenses->maturity_amount_payable) {
                            $extraInterest = 0;
                            $interstAmount = 0;
                        } elseif ($investAmount == $demandAdviceExpenses->maturity_amount_payable) {
                            $extraInterest = 0;
                            $interstAmount = 0;
                        }
                    }
                    
                    $tdsAmountonInterest = $demandAdviceExpenses->tds_amount;
                    $generatedInterest = MemberInvestmentInterest::where('investment_id', $demandAdviceExpenses['investment']->id)->sum('interest_amount');
                    $checkAmount = (($interstAmount + $extraInterest + $tdsAmountonInterest) - round($generatedInterest));
                    if (strpos($demandAdviceExpenses['investment']->account_number, 'R-') !== false && $mInvestment->plan_id == 3 && $mInvestment->last_deposit_to_ssb_date != '') {
                        $interstAmount = ($demandAdviceExpenses->maturity_amount_payable + $iseliAmount + $moneyBackAmount) - $investAmount;
                        $getMbTrsAmount = getMbTrsAmount($demandAdviceExpenses['investment']->id);
                        $fdAmount = $getMbTrsAmount->mb_fd_amount;
                        $fdInterest = $iseliAmount - $fdAmount;
                        $extraInterest = $extraInterest + $fdInterest;
                    }
                    
                    /************* TDS **************/
                    if ($request->type == 4) {
                        $totalDepositeAmount = Daybook::where('investment_id', $demandAdviceExpenses->investment_id)->whereIn('transaction_type', [2, 4])->where('is_deleted', 0)->sum('deposit');
                        $newInterest = $demandAdviceExpenses->final_amount - $totalDepositeAmount;
                    } else {
                        $newInterest = $demandAdviceExpenses->maturity_amount_till_date - $demandAdviceExpenses->maturity_prematurity_amount;
                    }
                    
                    $newInterest = $demandAdviceExpenses->interestAmount;
                    if (round($newInterest) > 0) {
                        $getLastRecord = MemberInvestmentInterestTds::where('member_id', $demandAdviceExpenses['investment']->member_id)->where('investment_id', $demandAdviceExpenses['investment']->id)->orderby('id', 'desc')->first();
                        MemberInvestmentInterestTds::create([
                            'member_id' => $demandAdviceExpenses['investment']->member_id,
                            'investment_id' => $demandAdviceExpenses['investment']->id,
                            'plan_type' => $demandAdviceExpenses['investment']->plan_id,
                            'branch_id' => $demandAdviceExpenses['investment']->branch_id,
                            'interest_amount' => $newInterest,
                            'date_from' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($demandAdviceExpenses->date))),
                            'date_to' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($demandAdviceExpenses->date))),
                            'tdsamount_on_interest' => $tdsAmountonInterest ?? 0,
                            'tds_amount' => $demandAdviceExpenses->tds_per_amount,
                            'tds_percentage' => $demandAdviceExpenses->tds_percentage,
                            'created_at' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($demandAdviceExpenses->date))),
                        ]);
                    }
                    
                    /************* TDS **************/
                    Memberinvestments::where('id', $demandAdviceExpenses->investment_id)->update(['is_mature' => 0, 'maturity_payable_amount' => $demandAdviceExpenses->maturity_amount_till_date, 'maturity_payable_interest' => $generatedInterest, 'tds_per' => $demandAdviceExpenses->tds_percentage, 'tds_amount' => $demandAdviceExpenses->tds_per_amount, 'tds_deduct_amount' => $demandAdviceExpenses->tds_amount, 'investment_interest_date' => $demandAdviceExpenses->date, 'investment_interest_tds_date' => $demandAdviceExpenses->date]);
                    if ($request->subtype == 5) {
                        $ssbAccountDetails = SavingAccount::with('ssbcustomerDataGet')->select('id', 'balance', 'branch_id', 'branch_code', 'member_id', 'account_no', 'customer_id')->where('account_no', $demandAdviceExpenses->ssb_account)->first();
                    } else {
                        $ssbAccountDetails = SavingAccount::with('ssbcustomerDataGet')->select('id', 'balance', 'branch_id', 'branch_code', 'member_id', 'account_no', 'customer_id')->where('member_id', $demandAdviceExpenses['investment']->member_id)->where('company_id', $companyId)->first();
                    }
                    if ($request->amount_mode == 1 && $ssbAccountDetails == '') {
                        array_push($ssbArray, $demandAdviceExpenses['investment']->memberCompany->member_id);
                    } else {
                        $amount = $demandAdviceExpenses->final_amount + $iseliAmount;
                        $dayBookRef = CommanController::createBranchDayBookReference($amount);
                        if ($request->amount_mode == 0) {
                            
                            $branch_id = $request->branch_id;
                            $type = 13;
                            $jv_unique_id = NULL;
                            
                            if ($request->type == 1) {
                                $sub_type = 133;
                                $description = $planDetail->name . ' Maturity';
                                $description_dr = 'Maturity Amount A/C Dr ' . ($demandAdviceExpenses->final_amount + $iseliAmount);
                            } elseif ($request->type == 2) {
                                $sub_type = 134;
                                $description = $planDetail->name . ' PreMaturity';
                                $description_dr = 'PreMaturity Amount A/C Dr ' . ($demandAdviceExpenses->final_amount + $iseliAmount);
                            } elseif ($request->type == 3) {
                                if ($demandAdviceExpenses->death_help_catgeory == 0) {
                                    $sub_type = 135;
                                    $description = $planDetail->name . ' Death Help';
                                    $description_dr = 'Death Help Amount A/C Dr  ' . ($demandAdviceExpenses->maturity_amount_payable + $iseliAmount);
                                } elseif ($demandAdviceExpenses->death_help_catgeory == 1) {
                                    $sub_type = 136;
                                    $description = $planDetail->name . ' Death Claim';
                                    $description_dr = 'Death Claim Amount A/C Dr  ' . ($demandAdviceExpenses->final_amount + $iseliAmount);
                                }
                            } elseif ($request->type == 4) {
                                $sub_type = 137;
                                $description = $planDetail->name . ' Emergancy Maturity';
                                $description_dr = 'Emergancy Maturity Amount A/C Dr  ' . ($demandAdviceExpenses->final_amount + $iseliAmount);
                                $newInterest = $demandAdviceExpenses->interestAmount;
                            }
                           
                            $type_id = $value;
                            $type_transaction_id = $value;
                            $associate_id = NULL;
                            $member_id = $demandAdviceExpenses['investment']->member_id;
                            $branch_id_to = NULL;
                            $branch_id_from = $request->branch_id;
                            $opening_balance = $demandAdviceExpenses->final_amount + $iseliAmount;
                            $closing_balance = $demandAdviceExpenses->final_amount + $iseliAmount;
                            $description_cr = 'To Cash A/C Cr ' . ($demandAdviceExpenses->final_amount + $iseliAmount);
                            $payment_type = 'DR';
                            $payment_mode = 0;
                            $day_book_payment_mode = 0;
                            $currency_code = 'INR';
                            $amount_to_id = $demandAdviceExpenses['investment']->member_id;
                            $amount_to_name = $demandAdviceExpenses['investment']->memberCompany->first_name . ' ' . $demandAdviceExpenses['investment']->memberCompany->last_name;
                            $amount_from_id = $request->branch_id;
                            $amount_from_name = getBranchDetail($request->branch_id)->name;
                            $v_no = NULL;
                            $v_date = NULL;
                            $ssb_account_id_from = NULL;
                            $ssb_account_id_to = NULL;
                            $cheque_type = NULL;
                            $cheque_id = NULL;
                            $cheque_no = NULL;
                            $cheque_date = NULL;
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
                            $transction_bank_ac_from = NULL;
                            $transction_bank_ifsc_from = NULL;
                            $transction_bank_branch_from = NULL;
                            $transction_bank_to = NULL;
                            $transction_bank_ac_to = NULL;
                            $transction_bank_from_ac_id = NULL;
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
                        } elseif ($request->amount_mode == 1) {
                            $transType = "Saving";
                            $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                            $vno = "";
                            for ($i = 0; $i < 10; $i++) {
                                $vno .= $chars[mt_rand(0, strlen($chars) - 1)];
                            }
                            $branch_id = $demandAdviceExpenses['investment']->branch_id;
                            $type = 13;
                            
                            if ($request->type == 1) {
                                $sub_type = 133;
                                $description = $planDetail->name . ' Maturity';
                                $description_dr = 'Maturity Amount A/C Dr ' . ($demandAdviceExpenses->final_amount);
                            } elseif ($request->type == 2) {
                                $sub_type = 134;
                                $description = $planDetail->name . ' Death Help';
                                $description_dr = 'PreMaturity Amount A/C Dr ' . ($demandAdviceExpenses->final_amount);
                            } elseif ($request->type == 3) {
                                if ($demandAdviceExpenses->death_help_catgeory == 0) {
                                    $sub_type = 135;
                                    $description = $planDetail->name . ' Death Help';
                                    $description_dr = 'Death Help Amount A/C Dr  ' . ($demandAdviceExpenses->final_amount + $iseliAmount);
                                } elseif ($demandAdviceExpenses->death_help_catgeory == 1) {
                                    $sub_type = 136;
                                    $description = $planDetail->name . ' Death Claim';
                                    $description_dr = 'Death Claim Amount A/C Dr  ' . ($demandAdviceExpenses->final_amount + $iseliAmount);
                                }
                            } elseif ($request->type == 4) {
                                $sub_type = 137;
                                $description = $planDetail->name . ' Emergancy Maturity';
                                $description_dr = 'Emergancy Maturity Amount A/C Dr  ' . ((int)$demandAdviceExpenses->final_amount + $iseliAmount);
                            }
                            $type_id = $value;
                            $type_transaction_id = $value;
                            $associate_id = NULL;
                            $member_id = $demandAdviceExpenses['investment']->member_id;
                            $branch_id_to = NULL;
                            $branch_id_from = NULL;
                            $opening_balance = $demandAdviceExpenses->final_amount + $iseliAmount;
                            $amount = $demandAdviceExpenses->final_amount + $iseliAmount;
                            $closing_balance = $demandAdviceExpenses->final_amount + $iseliAmount;
                            $description_cr = 'To SSB A/C Cr ' . ($demandAdviceExpenses->final_amount + $iseliAmount);
                            $payment_type = 'CR';
                            $payment_mode = 3;
                            $day_book_payment_mode = 4;
                            $currency_code = 'INR';
                            $amount_to_id = $demandAdviceExpenses['investment']->member_id;
                            $amount_to_name = $demandAdviceExpenses['investment']->memberCompany->first_name . ' ' . $demandAdviceExpenses['investment']->memberCompany->last_name;
                            $amount_from_id = NULL;
                            $amount_from_name = NULL;
                            $jv_unique_id = NULL;
                            $v_no = $vno;
                            $v_date = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request->created_at)));
                            $ssb_account_id_from = NULL;
                            $ssb_account_id_to = $ssbAccountDetails->id;
                            $cheque_type = NULL;
                            $cheque_id = NULL;
                            $cheque_no = NULL;
                            $cheque_date = NULL;
                            $cheque_bank_to_name = NULL;
                            $cheque_bank_to_branch = NULL;
                            $cheque_bank_from = NULL;
                            $cheque_bank_from_id = NULL;
                            $cheque_bank_ac_from = NULL;
                            $cheque_bank_ac_from_id = NULL;
                            $cheque_bank_to_ac_no = NULL;
                            $cheque_bank_ifsc_from = NULL;
                            $cheque_bank_branch_from = NULL;
                            $cheque_bank_to = NULL;
                            $cheque_bank_ac_to = NULL;
                            $cheque_bank_to_ifsc = NULL;
                            $transction_no = NULL;
                            $transction_bank_from = NULL;
                            $transction_bank_from_id = NULL;
                            $transction_bank_ac_from = NULL;
                            $transction_bank_ifsc_from = NULL;
                            $transction_bank_branch_from = NULL;
                            $transction_bank_from_ac_id = NULL;
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
                        } elseif ($request->amount_mode == 2) {
                            $transType = "Bank";
                            $branch_id = $demandAdviceExpenses['investment']->branch_id;
                            $type = 13;
                            if ($request->type == 1) {
                                $sub_type = 133;
                                $chequeType = 63;
                            } elseif ($request->type == 2) {
                                $sub_type = 134;
                                $chequeType = 64;
                            } elseif ($request->type == 3) {
                                if ($demandAdviceExpenses->death_help_catgeory == 0) {
                                    $sub_type = 135;
                                } elseif ($demandAdviceExpenses->death_help_catgeory == 1) {
                                    $sub_type = 136;
                                }
                                $chequeType = 65;
                            } elseif ($request->type == 4) {
                                $sub_type = 137;
                                $chequeType = 66;
                            }
                            $type_id = $value;
                            $type_transaction_id = $value;
                            $associate_id = NULL;
                            $member_id = $demandAdviceExpenses['investment']->member_id;
                            $branch_id_to = NULL;
                            $branch_id_from = NULL;
                            $jv_unique_id = NULL;
                            $opening_balance = ($demandAdviceExpenses->final_amount + $iseliAmount);
                            $amount = ($demandAdviceExpenses->final_amount + $iseliAmount);
                            $closing_balance = ($demandAdviceExpenses->final_amount + $iseliAmount);
                            $newInterest = $demandAdviceExpenses->maturity_amount_till_date - $demandAdviceExpenses->maturity_prematurity_amount;
                            if ($request->type == 1) {
                                $description = $planDetail->name . ' Maturity';
                            } elseif ($request->type == 2) {
                                $description = $planDetail->name . ' PreMaturity';
                            } elseif ($request->type == 3) {
                                if ($demandAdviceExpenses->death_help_catgeory == 0) {
                                    $description = $planDetail->name . ' Death Help';
                                } elseif ($demandAdviceExpenses->death_help_catgeory == 1) {
                                    $description = $planDetail->name . ' Death Claim';
                                }
                            } elseif ($request->type == 4) {
                                $description = $planDetail->name . ' Emergancy Maturity';
                            }
                            $nefAmount = $request->neft_charge > 0 ? $request->neft_charge : 0;
                            $description_dr = 'A/C Dr ' . ($demandAdviceExpenses->final_amount + $iseliAmount + $nefAmount);
                            $description_cr = 'To Bank A/C Cr ' . ($demandAdviceExpenses->final_amount + $iseliAmount + $nefAmount);
                            $payment_type = 'DR';
                            $currency_code = 'INR';
                            $amount_to_id = $demandAdviceExpenses['investment']->member_id;
                            $amount_to_name = $demandAdviceExpenses['investment']->memberCompany->first_name . ' ' . $demandAdviceExpenses['investment']->memberCompany->last_name;
                            $amount_from_id = $request->bank;
                            $amount_from_name = getSamraddhBank($request->bank)->bank_name;
                            $v_no = NULL;
                            $v_date = NULL;
                            $ssb_account_id_from = NULL;
                            $ssb_account_id_to = NULL;
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
                                $day_book_payment_mode = 1;
                                $transction_bank_to_name = NULL;
                                $transction_bank_to_ac_no = NULL;
                                $transction_bank_to_branch = NULL;
                                $transction_bank_to_ifsc = NULL;
                                SamraddhCheque::where('cheque_no', $request->cheque_number)->update(['status' => 3, 'is_use' => 1]);
                                SamraddhChequeIssue::create([
                                    'cheque_id' => getSamraddhChequeData($request->cheque_number)->id,
                                    'type' => 6,
                                    'sub_type' => $chequeType,
                                    'type_id' => $value,
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
                                $day_book_payment_mode = 3;
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
                        }
                        $response = DemandAdvice::where('id', $value)->update(['status' => 1, 'payment_mode' => $payment_mode, 'daybook_ref_id' => $dayBookRef]);
                        if ($request->amount_mode == 1) {
                            $paymentMode = 4;
                            $amount_deposit_by_name = $ssbAccountDetails['ssbcustomerDataGet']->first_name . ' ' . $ssbAccountDetails['ssbcustomerDataGet']->last_name;
                            $ssb['saving_account_id'] = $ssbAccountDetails->id;
                            $ssb['account_no'] = $ssbAccountDetails->account_no;
                            $ssb['opening_balance'] = ($demandAdviceExpenses->final_amount) + ($ssbAccountDetails->balance + $eliAmount);
                            if ($request->subtype == 5) {
                                $am = $finalamount = $demandAdviceExpenses->final_amount;
                                $ssb['deposit'] = $am;
                            } else {
                                $ssb['deposit'] = $finalamount = $demandAdviceExpenses->final_amount;
                                $am = $ssb['deposit'];
                            }
                            // $investAmount  = $am;
                            $ssb['branch_id'] = $demandAdviceExpenses['investment']->branch_id;
                            $ssb['type'] = 10;
                            $ssb['withdrawal'] = 0;
                            $ssb['description'] = ($request->subtype == 5) ? 'Death Claim received from A/C No. ' . $demandAdviceExpenses['investment']->account_number : 'Redemption amount received from A/C No. ' . $demandAdviceExpenses['investment']->account_number;
                            $ssb['currency_code'] = 'INR';
                            $ssb['payment_type'] = 'CR';
                            $ssb['payment_mode'] = 3;
                            $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request->created_at)));
                            $ssb['daybook_ref_id'] = $dayBookRef;
                            $ssb['company_id'] = $request->company_id;
                            $ssbAccountTran = SavingAccountTranscation::create($ssb);
                            $ssbHead = \App\Models\Plans::where('plan_category_code', "S")->whereCompanyId($request->company_id)->first();
                            $ssbHead = $ssbHead->deposit_head_id;
                            $saTranctionId = $ssbAccountTran->id;
                            $saToId = $ssbAccountDetails->id;
                            $saTranctionToId = $ssbAccountTran->id;
                            $ssb_account_tran_id_to = $ssbAccountTran->id;
                            $balance_update = ($demandAdviceExpenses->final_amount) + ($ssbAccountDetails->balance + $eliAmount);
                            if ($request->type == 4) {
                                $newInterest = $demandAdviceExpenses->final_amount - $demandAdviceExpenses['investment']->current_balance;
                                ;
                            } else {
                                $newInterest = $demandAdviceExpenses->interestAmount;
                            }
                            $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                            $ssbBalance->balance = $balance_update;
                            $ssbBalance->save();
                            $data['saving_account_transaction_id'] = $saTranctionId;
                            $data['investment_id'] = $demandAdviceExpenses['investment']->id;
                            $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request->created_at)));
                            $satRef = NULL;
                            $satRefId = NULL;
                            $amountArraySsb = array('1' => $demandAdviceExpenses->final_amount + $eliAmount);
                            if ($request->subtype == 5) {
                                $description = 'Death Claim received from A/C No. ' . $demandAdviceExpenses['investment']->account_number;
                            } else {
                                $description = 'Redemption amount received from A/C No. ' . $demandAdviceExpenses['investment']->account_number;
                            }
                            $description = 'Redemption amount received from A/C No. ' . $demandAdviceExpenses['investment']->account_number;
                            $createDayBook = CommanController::createDayBook($dayBookRef, $satRefId, 1, $ssbAccountDetails->id, $demandAdviceExpenses['investment']->associate_id, $ssbAccountDetails->member_id, ($finalamount) + $ssbAccountDetails->balance, ($finalamount), $withdrawal = 0, $description, $ssbAccountDetails->account_no, $ssbAccountDetails->branch_id, $ssbAccountDetails->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name = NULL, $ssbAccountDetails->member_id, $ssbAccountDetails->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(convertDate($request->created_at))), NULL, $online_payment_by = NULL, $ssbAccountDetails->account_no, 'CR', $companyId);
                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $ssbHead, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $finalamount, $description, 'CR', $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from = NULL, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                        } else {
                            $saToId = NULL;
                            $saTranctionToId = NULL;
                            $ssb_account_tran_id_to = NULL;
                        }
                        $ssb_account_tran_id_from = NULL;
                        $head = $planDetail->deposit_head_id;
                        if ($request->amount_mode == 0) {
                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 28, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, 'CR', $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from = NULL, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                        }
                        if ($request->type = 1) {
                            $finalamount = $demandAdviceExpenses->interestAmount + $investAmount - $demandAdviceExpenses->tds_amount;
                        } else {
                            $finalamount = $demandAdviceExpenses->final_amount;
                        }
                        if ($request->neft_charge > 0) {
                            $finalamount = $finalamount + $request->neft_charge;
                        }
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $finalamount, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $head, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $investAmount, $description, 'DR', $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                        $invest = DemandAdvice::select('investment_id')->where('id', $request->demandAdviceIds)->first();
                        $accDetail = Daybook::select('is_eli')->where('investment_id', $invest->investment_id)->orderBy('created_at', 'ASC')->first();
                        if ($accDetail->is_eli == 1) {
                            if (($interstAmount + $extraInterest) > 0) {
                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 258, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, ($interstAmount + $extraInterest), 'INTEREST ON DEPOSITS', 'DR', $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                            }
                        } else {
                            if (($newInterest) > 0) {
                                if ($request->subtype == 5 && $request->type != 1) {
                                    $newInterest = $interstAmount;
                                } else {
                                    $newInterest = $demandAdviceExpenses->interestAmount;
                                }
                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 36, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, ($newInterest), 'INTEREST ON DEPOSITS', 'DR', $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                            }
                        }
                        if ($tdsAmountonInterest > 0) {
                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 62, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, ($tdsAmountonInterest), 'Tds on interest on deposit', 'CR', $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                        }
                        /************** Investment Transactions *********************/
                        $interestArraySsb = array('1' => ($interstAmount + $extraInterest));
                        $investAmountArraySsb = array('1' => $investAmount);
                        if ($cheque_no) {
                            $chequeId = getSamraddhChequeData($cheque_no)->id;
                        } else {
                            $chequeId = NULL;
                        }
                        if ($transction_bank_ac_from) {
                            $bankAccountId = getSamraddhBankAccount($transction_bank_ac_from)->id;
                        } else {
                            $bankAccountId = NULL;
                        }
                        $a = ($interstAmount + $extraInterest);
                        $totalbalance = $demandAdviceExpenses['investment']->current_balance + $a;
                        $sResult = Memberinvestments::find($demandAdviceExpenses['investment']->id);
                        $investData['current_balance'] = $totalbalance;
                        $sResult->update($investData);
                        $Interestdescription = 'Bonus amount received';
                        if ($accDetail->is_eli == 1) {
                            $interestDayBook = CommanController::createDayBookNew($dayBookRef, NULL, 16, $demandAdviceExpenses['investment']->id, NULL, $demandAdviceExpenses['investment']->member_id, $totalbalance, ($a), $withdrawal = 0, $Interestdescription, NULL, $branch_id, getBranchCode($branch_id)->branch_code, $interestArraySsb, $day_book_payment_mode, $amount_from_name, $amount_from_id, $demandAdviceExpenses['investment']->account_number, $cheque_no, $transction_bank_from, getBranchDetail($branch_id)->name, date("Y-m-d", strtotime(convertDate($request->payment_date))), $transction_no, $bank_id, NULL, 'CR', $chequeId, $transction_bank_ac_from, $bankAccountId, $transction_bank_ac_from, $bankAccountId, $companyId);
                        } else {
                            if (($a) > 0) {
                                if ($request->subtype == 5 && $request->type != 1) {
                                    $interest = $interstAmount;
                                    $totalbalance = $demandAdviceExpenses->deposited_amount + $interest;
                                } else {
                                    $interest = $demandAdviceExpenses->interestAmount;
                                    $totalbalance = $investAmount + $interest;
                                }
                                $interestDayBook = CommanController::createDayBookNew($dayBookRef, NULL, 16, $demandAdviceExpenses['investment']->id, NULL, $demandAdviceExpenses['investment']->member_id, $totalbalance, ($interest), $withdrawal = 0, $Interestdescription, NULL, $branch_id, getBranchCode($branch_id)->branch_code, $interestArraySsb, $day_book_payment_mode, $amount_from_name, $amount_from_id, $demandAdviceExpenses['investment']->account_number, $cheque_no, $transction_bank_from, getBranchDetail($branch_id)->name, date("Y-m-d", strtotime(convertDate($request->payment_date))), $transction_no, $bank_id, NULL, 'CR', $chequeId, $transction_bank_ac_from, $bankAccountId, $transction_bank_ac_from, $bankAccountId, $companyId);
                            }
                        }
                        if ($tdsAmountonInterest > 0) {
                            $interestDayBook = CommanController::createDayBookNew($dayBookRef, NULL, 23, $demandAdviceExpenses['investment']->id, NULL, $demandAdviceExpenses['investment']->member_id, $totalbalance - $tdsAmountonInterest, 0, $tdsAmountonInterest, 'TDS ' . $demandAdviceExpenses->tds_percentage . '% @', NULL, $branch_id, getBranchCode($branch_id)->branch_code, $interestArraySsb, $day_book_payment_mode, $amount_from_name, $amount_from_id, $demandAdviceExpenses['investment']->account_number, $cheque_no, $transction_bank_from, getBranchDetail($branch_id)->name, date("Y-m-d", strtotime(convertDate($request->payment_date))), $transction_no, $bank_id, NULL, 'DR', $chequeId, $transction_bank_ac_from, $bankAccountId, $transction_bank_ac_from, $bankAccountId, $companyId);
                        }
                        if ($request->amount_mode == 0) {
                            $investAmountdescription = 'Redemption amount transfer cash';
                        } elseif ($request->amount_mode == 1) {
                            if ($request->subtype == 5) {
                                $investAmountdescription = 'Death Claim transfer to saving account ' . $demandAdviceExpenses->ssb_account;
                            } else if (getMemberSsbAccountDetail($demandAdviceExpenses['investment']->member_id) && $request->subtype != 5) {
                                $investAmountdescription = 'Redemption amount transfer to saving account ' . $ssbAccountDetails->account_no;
                            } else {
                                $investAmountdescription = 'Redemption amount transfer to saving account';
                            }
                        } elseif ($request->amount_mode == 2) {
                            if ($request->mode == 3) {
                                $cheque_no = $request->cheque_number;
                                $investAmountdescription = 'Redemption amount transfer to Bank through the cheque (' . $transction_bank_from . ', ' . $transction_bank_ac_from . ', ' . $transction_bank_ifsc_from . ', ' . $cheque_no . ')';
                            } elseif ($request->mode == 4) {
                                $cheque_no = NULL;
                                $investAmountdescription = 'Redemption amount transfer to Bank through online (' . $transction_bank_from . ', ' . $transction_bank_ac_from . ', ' . $transction_bank_ifsc_from . ', ' . $transction_no . ')';
                            }
                        }
                        $reinvest = 'R-';
                        if (strpos($reinvest, 'R-') !== false) {
                            $totalInvestbalance = 0;
                        } else {
                            $totalInvestbalance = getInvestmentDetails($demandAdviceExpenses['investment']->id)->current_balance - ($investAmount + $generatedInterest + $extraInterest);
                        }
                        $sResult = Memberinvestments::find($demandAdviceExpenses['investment']->id);
                        $investData['current_balance'] = $totalInvestbalance;
                        $sResult->update($investData);
                        if ($request->subtype = 5 && $request->type != 1) {
                            $investAmount = $demandAdviceExpenses->final_amount;
                        }
                        if ($request->type = 1) {
                            $finalamount = $demandAdviceExpenses->interestAmount + $investAmount - $demandAdviceExpenses->tds_amount;
                        } else {
                            $finalamount = $investAmount - $demandAdviceExpenses->tds_amount;
                        }
                        $investAmountDayBook = CommanController::createDayBookNew($dayBookRef, NULL, 17, $demandAdviceExpenses['investment']->id, NULL, $demandAdviceExpenses['investment']->member_id, $totalInvestbalance, 0, ($finalamount), $investAmountdescription, NULL, $branch_id, getBranchCode($branch_id)->branch_code, $investAmountArraySsb, $day_book_payment_mode, $amount_from_name, $amount_from_id, $demandAdviceExpenses['investment']->account_number, $cheque_no, $transction_bank_from, getBranchDetail($branch_id)->name, date("Y-m-d", strtotime(convertDate($request->payment_date))), $transction_no, $bank_id, NULL, 'DR', $chequeId, $transction_bank_ac_from, $bankAccountId, $transction_bank_ac_from, $bankAccountId, $companyId);
                        /************** Investment Transactions *********************/
                        if ($request->amount_mode == 2) {
                            if ($request->amount_mode == 2 && $request->mode == 4) {
                                $bankAmount = $amount + $request->neft_charge;
                            } else {
                                $bankAmount = $amount;
                            }
                            $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($dayBookRef, $bank_id, $bank_ac_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bankAmount, $bankAmount, $bankAmount, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id, $cheque_type, $cheque_id, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $companyId);
                            if ($request->amount_mode == 2 && $request->mode == 4) {
                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, getSamraddhBank($request->bank)->account_head_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, ($amount + $request->neft_charge), $description, 'CR', $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                            } else {
                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, getSamraddhBank($request->bank)->account_head_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, ($amount), $description, 'CR', $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                            }
                        }
                        if ($request->amount_mode == 2 && $request->mode == 4 && $request->neft_charge > 0) {
                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, getSamraddhBank($request->bank)->account_head_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $request->neft_charge, 'NEFT Charge A/c Cr ' . $request->neft_charge . '', 'DR', $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                        }
                        if ($request->amount_mode != 0) {
                            $contactNumber = array();
                            $contactNumber[] = $demandAdviceExpenses['investment']['member']->mobile_no;
                            $text = " Dear Member Rs" . $demandAdviceExpenses->final_amount . ' credited in ';
                            $text .= "your " . $transType . " A/c on " . $request->payment_date . " for maturity of A/C " . $demandAdviceExpenses['investment']->account_number . " Samraddh Bestwin Microfinance";
                            $numberWithMessage = array();
                            $numberWithMessage['contactNumber'] = $contactNumber;
                            $numberWithMessage['message'] = $text;
                            $templateId = 1207167273283409437;
                            $sendToMember = new Sms();
                            $sendToMember->sendSms($contactNumber, $text, $templateId);
                        }
                    }
                }
            }
            if (isset($demandAdviceExpenses['investment']->id)) {
                \App\Models\MaturityCalculate::where('investment_id', $demandAdviceExpenses['investment']->id)->delete();
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        if (count($ssbArray) > 0) {
            $ssbString = implode(",", $ssbArray);
            if ($request->type == 4) {
                return redirect()->route('admin.emergancymaturity.index')->with('alert', $ssbString . ' MI Does Not Have Any SSB Account!');
            } else {
                return redirect()->route('admin.demand.application')->with('info', 'Approved demand advice successfully AND ' . $ssbString . ' not have any ssb account!');
            }
        } else {
            if ($request->type == 4) {
                return redirect()->route('admin.emergancymaturity.index')->with('success', 'Approved emergancy maturity successfully!');
            } else {
                // /route('admin.demand.application')
                return redirect()->route('admin.demand.application')->with('success', 'Approved demand advice successfully!');
            }
        }
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
            $getNextBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextBranchRecord) {
                foreach ($getNextBranchRecord as $key => $value) {
                    $sResult = \App\Models\BranchCash::find($value->id);
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
            $oldDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchCash::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $data1['loan_closing_balance'] = $oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data1RecordExists = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
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
                        $sResult = \App\Models\BranchCash::find($value->id);
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
        $transcation = \App\Models\EmployeeLedger::create($data);
        return $transcation->id;
    }
    public static function employeeLedgerBackDateCR($employee_id, $date, $amount)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $getNextrecord = \App\Models\EmployeeLedger::where('employee_id', $employee_id)->whereDate('created_at', '>', $entryDate)->orderby('created_at', 'ASC')->get();
        if ($getNextrecord) {
            foreach ($getNextrecord as $v1) {
                $ResultNext1 = \App\Models\EmployeeLedger::find($v1->id);
                $nextData1['opening_balance'] = $v1->opening_balance + $amount;
                $ResultNext1->update($nextData1);
                $insertid = $ResultNext1->id;
            }
        }
        return true;
    }
    public static function employeeLedgerBackDateDR($employee_id, $date, $amount)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
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
    public static function RentLiabilityLedger($rent_liability_id, $type, $type_id, $opening_balance, $deposit, $withdrawal, $description, $currency_code, $payment_type, $payment_mode, $status, $created_at, $updated_at, $v_no, $v_date, $ssb_account_id_to, $ssb_account_id_from, $to_bank_name, $to_bank_branch, $to_bank_ac_no, $to_bank_ifsc, $to_bank_id, $to_bank_account_id, $from_bank_name, $from_bank_branch, $from_bank_ac_no, $from_bank_ifsc, $from_bank_id, $from_bank_ac_id, $cheque_id, $cheque_no, $cheque_date, $transaction_no, $transaction_date, $transaction_charge, $daybook_ref_id = NULL)
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
        $data['daybook_ref_id'] = $transaction_charge;
        $transcation = \App\Models\RentLiabilityLedger::create($data);
        return $transcation->id;
    }
    public static function rentLedgerBackDateCR($rent_liability_id, $date, $amount)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $getNextrecord = \App\Models\RentLiabilityLedger::where('rent_liability_id', $rent_liability_id)->whereDate('created_at', '>', $entryDate)->orderby('created_at', 'ASC')->get();
        if ($getNextrecord) {
            foreach ($getNextrecord as $v1) {
                $ResultNext1 = \App\Models\RentLiabilityLedger::find($v1->id);
                $nextData1['opening_balance'] = $v1->opening_balance + $amount;
                $ResultNext1->update($nextData1);
                $insertid = $ResultNext1->id;
            }
        }
        return true;
    }
    public function print_demand_advice()
    {
        $data['title'] = 'Print Demand Advice ';
        return view('templates.admin.demand-advice.print-demand-advice', $data);
    }
    public function printDemandAdvice(Request $request)
    {
        $id = $request->demandId;
        $demand = DemandAdvice::where('id', $id)->update(['is_print' => 0]);
        $return_array = compact('demand');
        return json_encode($return_array);
    }
    public function createAllTransaction($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head1, $head2, $head3, $head4, $head5, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from)
    {
        $globaldate = Session::get('created_at');
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
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $transcation = \App\Models\AllTransaction::create($data);
        return true;
    }
    public static function createMemberTransaction($daybook_ref_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $account_id, $amount, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id)
    {
        $globaldate = Session::get('created_at');
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
        $data['transction_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $data['jv_unique_id'] = $jv_unique_id;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;
        $transcation = \App\Models\MemberTransaction::create($data);
        return true;
    }
    public function get_head_details(Request $request)
    {
        $response = array();
        $head_id = $request->head_id;
        $data_row_id = $request->data_row_id;
        $data_row_id = (int) $data_row_id + 1;
        $accountHead = AccountHeads::where('parent_id', $head_id)->where('status', 0)->get();
        if (count($accountHead) > 0) {
            $html = '<div class="col-md-4 is-assets MainHead' . $data_row_id . '"><div class="form-group row"><label class="col-form-label col-lg-12">Asset Categories<sup>*</sup></label><div class="col-lg-12 error-msg"><select class="form-control assets_category" id="assets_category' . $data_row_id . '" name="assets_category' . $data_row_id . '" data-row-id="' . $data_row_id . '"><option value="">---- Please Select ----</option>';
            foreach ($accountHead as $val) {
                $html .= '<option value="' . $val->head_id . '" >' . $val->sub_head . '</option>';
            }
            $html .= '</select></div></div></div>';
            $response["status"] = "1";
            $response["heads"] = $html;
        } else {
            $response["status"] = "0";
            $response["heads"] = "";
        }
        echo json_encode($response);
    }
    public function checkAccountNumber(Request $request)
    {
        $account = $request->account;
        $data = Memberinvestments::where('account_number', $account);
        // $mi_code = array();
        if (!is_null(Auth::user()->branch_ids)) {
            $id = Auth::user()->branch_ids;
            $data = $data->whereIn('branch_id', explode(",", $id));
            $data = $data->first();
            if ($data) {
                $message = '';
                $status = 200;
            } else {
                $message = 'Account Number is not related to this branch!';
                $status = 500;
            }
        }
        // foreach($data as $branch)
        //         {
        //           $branch_code[] = $branch->branch_code;
        //           // $mi_code[] =
        //         }
        //        $strt_account  =   substr($account,0,4);
        //        $reinvest_strt_account  =   substr($account,2,4);
        //        //dd($branch_code);
        //         if(!(in_array($strt_account,$branch_code)))
        //         {
        //            $message = 'Account Number is not related to this branch!';
        //            $status = 500;
        //         }
        //         // if(!(in_array($reinvest_strt_account,$branch_code)))
        //         // {
        //         //    $message = 'Account Number is not related to this branch!';
        //         //    $status = 500;
        //         // }
        //         else{
        //                $message = '';
        //            $status = 200;
        //         }
        //   }
        else {
            $message = '';
            $status = 200;
        }
        $return_array = compact('message', 'status');
        return json_encode($return_array);
    }
    public function getTds(Request $request)
    {
        $investmentId = $request->investmentId;
        $payableAmount = $request->payableAmount;
        $cYear = date('Y');
        $cDate = date('Y-m-d');
        $mInvestment = Memberinvestments::where('id', $investmentId)->first();
        $investmentTds = \App\Models\MemberInvestmentInterestTds::where('investment_id', $mInvestment->id)->sum('tdsamount_on_interest');
        $existsInterst = \App\Models\MemberInvestmentInterest::where('investment_id', $mInvestment->id)->sum('interest_amount');
        $checkYear = $cYear;
        $formG =\App\Models\Form15G::where('member_id', $mInvestment->member_id)
        ->where(function ($query) use ($checkYear) {
            $query->where('year', $checkYear)
                ->orWhere('max_year', $checkYear);
        })
        ->where('status', 1)
        ->where('is_deleted', 0)
        ->whereNotNull('file')
        ->first();
        if ($formG) {
            $tdsAmount = 0;
            $tdsPercentage = 0;
            $investmentTds = 0;
        } else {
            $memberData = getMemberData($mInvestment->member_id);
            $diff = abs(strtotime($cDate) - strtotime($memberData['dob']));
            $years = floor($diff / (365 * 60 * 60 * 24));
            if ($years >= 60) {
                $tdsDetail = \App\Models\TdsDeposit::where('type', 2)->where('start_date', '<', $cDate)->first();
            } else {
                $penCard = get_member_id_proof($mInvestment->member_id, 5);
                if ($penCard) {
                    $tdsDetail = \App\Models\TdsDeposit::where('type', 1)->where('start_date', '<', $cDate)->first();
                } else {
                    $tdsDetail = \App\Models\TdsDeposit::where('type', 5)->where('start_date', '<', $cDate)->first();
                }
            }
            if ($tdsDetail) {
                $tdsAmount = $tdsDetail->tds_amount;
                $tdsPercentage = $tdsDetail->tds_per;
                $deposit = Daybook::where('investment_id', $mInvestment->id)->where('account_no', $mInvestment->account_number)->where('transaction_type', '>', 1)->whereNotIn('transaction_type', [3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 19])->sum('deposit');
                $withdrawal = Daybook::where('investment_id', $mInvestment->id)->where('account_no', $mInvestment->account_number)->where('transaction_type', '>', 1)->whereNotIn('transaction_type', [3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 19])->sum('withdrawal');
                $investmentAmount = $deposit - $withdrawal;
                if ($payableAmount > $investmentAmount) {
                    $currentInterst = $payableAmount - $investmentAmount;
                } else {
                    $currentInterst = 0;
                }
                if ($currentInterst > $tdsAmount) {
                    $tdsAmountonInterest = $tdsPercentage * $currentInterst / 100;
                    $investmentTds = round(($investmentTds + $tdsAmountonInterest), 2);
                } else {
                    $investmentTds = 0;
                }
            } else {
                $tdsAmount = 0;
                $tdsPercentage = 0;
                $investmentTds = 0;
            }
        }
        $tdsPercentageAmount = $tdsAmount;
        $return_array = compact('tdsPercentageAmount', 'tdsPercentage', 'investmentTds');
        return json_encode($return_array);
    }
    public function rejectDemand(Request $request)
    {
        $record = $sRecord = explode(',', $request['select_rejected_records']);
        $reject = DemandAdvice::whereIn('id', $record)->update(['is_reject' => '1', 'is_redemand' => '1']);
        return back()
            ->with('success', 'Demand advice Rejected successfully!');
    }
    public function reDemand_advice(Request $request)
    {
        $response['status'] = '';
        $id = $request->id;
        try {
            $demandData = DemandAdvice::find($id);
            $redemandData = RedemandDemandAdvice::where('demand_id', $demandData->id)
                ->first();
            if (!empty($redemandData)) {
                $redemandData->update(['redemand_times' => $redemandData->redemand_times + 1]);
            } else {
                $data = ['demand_id' => $demandData->id, 'redemand_times' => 1,];
                $createdData = RedemandDemandAdvice::create($data);
                $response['status'] = 1;
            }
            $response = array(
                'status' => 'success',
                'msg' => 'Redemand Successfully',
            );
            $demandData->update(['is_reject' => '0', 'is_redemand' => '1']);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            $response = array(
                'status' => 'alert',
                'msg' => $ex->getMessage(),
            );
        }
        return response()->json($response);
    }
    public function rejectReport(Request $req)
    {
        if (check_my_permission(Auth::user()->id, "299") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Rejected Demands List';
        $datas = Branch::where('status', 1);
        if (Auth::user()->branch_id > 0) {
            $ids = $this->getDataRolewise(new Branch());
            $datas = $datas->whereIn('id', $ids);
        }
        $data['branch'] = $datas->get(['id', 'name']);
        return view('templates.admin.demand-advice.reject_demand_report', $data);
    }
    /**
     * Fetch Rejected Demand From demand Advice
     * Show data in dataTable
     * @request is Post
     */
    public function reportRejectListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            // Store filter in array
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                //Fetch Rejected demand Data and using eloquent relationShip also (Model name DemandAdvice)
                $data = DemandAdvice::select('id', 'investment_id', 'tds_amount', 'maturity_amount_till_date', 'maturity_prematurity_amount', 'payment_mode', 'payment_type', 'opening_date', 'sub_payment_type', 'date', 'voucher_number', 'maturity_amount_payable', 'final_amount', 'account_number', 'bank_account_number', 'bank_ifsc', 'is_print', 'status', 'employee_id', 'branch_id', 'owner_id', 'letter_photo_id', 'is_reject', 'maturity_payment_mode', 'company_id')
                    ->with([
                        'investment' => function ($q) {
                            $q->select('id', 'member_id', 'associate_id', 'created_at', 'customer_id')->with('member:id,member_id,first_name,last_name')
                                ->with([
                                    'member' => function ($q) {
                                                $q->select('id', 'member_id', 'first_name', 'last_name', 'associate_code', 'member_id')->with([
                                                    'memberNomineeDetails' => function ($q) {
                                                        $q->select('id', 'member_id', 'name');
                                                    }
                                                ]);
                                            }
                                ])->with([
                                    'associateMember' => function ($q) {
                                                $q->select('id', 'associate_no', 'first_name', 'last_name', 'associate_code');
                                            }
                                ])->with([
                                    'ssb' => function ($q) {
                                                $q->select('id', 'account_no');
                                            }
                                ]);
                        }
                    ])->with([
                        'branch' => function ($q) {
                            $q->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                        }
                    ])->with([
                        'demandAmountHead' => function ($q) {
                            $q->select('id', 'type_id', 'amount', 'head_id');
                        }
                    ])
                    ->with(['sumdeposite', 'demandReason','sumdeposite2'])
                    ->where('is_deleted', 0)
                    ->where('is_reject', '1')
                    ->where('company_id', $arrFormData['company_id']);
                if (Auth::user()->branch_id > 0) {
                    $ids = $this->getDataRolewise(new Branch());
                    $data = $data->whereIn('branch_id', $ids);
                }
                //search filter (Filter data from data)
                //Filter From demand date
                if ($arrFormData['date_from'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));
                    if ($arrFormData['date_to'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(date)'), [$startDate, $endDate]);
                }
                //Filter from demand Branch
                if (isset($arrFormData['filter_branch']) && $arrFormData['filter_branch'] != '') {
                    $branchId = $arrFormData['filter_branch'];
                    $data = $data->where('branch_id', '=', $branchId);
                }
                //Filter from Investment Account Number
                // if (isset($arrFormData['account_number']) && $arrFormData['account_number'] != '') {
                //     $account_number = $arrFormData['account_number'];
                //     $data = $data->where('account_number', '=', $account_number);
                // }
                //Filter from Demand Type (Expense,Death Help,Maturity,Prematurity,Emergancy Maturity)
                if ($arrFormData['advice_type'] != '') {
                    $advice_id = $arrFormData['advice_type'];
                    $advice_type_id = $arrFormData['expense_type'];
                    if ($advice_id == 1 || $advice_id == 2) {
                        if ($advice_type_id != '') {
                            $data = $data->where('payment_type', '=', $advice_id)->where('sub_payment_type', $advice_type_id);
                        } else {
                            $data = $data->where('payment_type', '=', $advice_id);
                        }
                    } elseif ($advice_id == 3) {
                        $data = $data->where('payment_type', '=', 3)->where('death_help_catgeory', '=', 0);
                    } elseif ($advice_id == 4) {
                        $data = $data->where('payment_type', '=', 3)->where('death_help_catgeory', '=', 1); // not working
                    } elseif ($advice_id == 5) {
                        $data = $data->where('payment_type', '=', 4); // not working
                    }
                }
                //Filter From voucher Number
                if ($arrFormData['voucher_number'] != '') {
                    $voucher_number = $arrFormData['voucher_number'];
                    $data = $data->where('voucher_number', '=', $voucher_number);
                }
                //Count Rejected Data From Demand Advice
                $count = $data->count('id');
                //Fetch Data in 20 slot
                $data = $data->offset($_POST['start'])->limit($_POST['length'])->orderBy('created_at', 'DESC')->get();
                $totalCount = $count;
                $sno = $_POST['start'];
                $rowReturn = array();
                //Show All data in dataTable
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['ac_opening'] = (isset($row['investment']->created_at)) ? date("d/m/Y", strtotime($row['investment']->created_at)) : 'N/A';
                    if (isset($row['company_id'])) {
                        $company_name = Companies::where('id', $row->company_id)->first(['id', 'name']);
                        $val['company_id'] = $company_name->name;
                        ;
                    } else {
                        $val['company_id'] = 'N/A';
                    }
                    if (isset($row['branch']->name)) {
                        $val['branch_name'] = $row['branch']->name;
                    } else {
                        $val['branch_name'] = 'N/A';
                    }
                    if (isset($row->is_reject)) {
                        // $val['reason'] = 'Yes';
                        /** this below code commented by sourab on 12-10-2023 */
                        $val['reason'] = $row->demandReason ? $row->demandReason[0] ? preg_replace( "/\r|\n/", "",$row->demandReason[0]['reason']) : 'Yes' : 'Yes';
                    } else {
                        $val['reason'] = 'No';
                    }
                    $member_name = 'N/A';
                    $member_id = 'N/A';
                    // echo '<pre>';
                    // print_r($row->toArray());
                    // die();
                    if (isset($row->investment_id)) {
                        $member_id = $row['investment']->member_id;
                        if ($member_id) {
                            $member_name = $row['investment']['member']->first_name . ' ' . $row['investment']['member']->last_name; //getMemberData
                        }
                    }
                    $val['member_name'] = $member_name;
                    $nominee_name = 'N/A';
                    if (isset($row->investment_id)) {
                        $associate_id = $row['investment']->member_id; //getInvestmentDetails($row->investment_id)->member_id;
                        $associate_code = 'N/A';
                        if ($associate_id) {
                            $associate_code = $row['investment']['member']->associate_code; //getMemberData($associate_id)->associate_code;
                        }
                        $val['associate_code'] = $associate_code;
                    } else {
                        $val['associate_code'] = 'N/A';
                    }
                    if (isset($row->tds_amount)) {
                        $val['tds_amount'] = round($row->tds_amount);
                    } else {
                        $val['tds_amount'] = 'N/A';
                    }
                    $amount = '0';
                    if (isset($row['sumdeposite'])) {
                        $total_amount = $row['sumdeposite']->sum('deposit');
                    }
                    if (isset($row['sumdeposite2'])) {
                        $total_amount = $row->sumdeposite2->sum('deposit');
                    } else {
                        $total_amount = 0;
                    }
                    if (isset($row['maturity_payment_mode'])) {
                        $val['maturity_payment_mode'] = $row['maturity_payment_mode'];
                    } else {
                        $val['maturity_payment_mode'] = "";
                    }
                    if (isset($row->investment_id)) {
                        $associate_id = $row['investment']->member_id;
                        $associate_code = 'N/A';
                        $associate_name = 'N/A';
                        if ($associate_id) {
                            $associate_code = $row['investment']['member']->associate_code; //getMemberData($associate_id)->associate_code;
                            $associate_name = $row['investment']['associateMember'];
                            if (isset($associate_name->first_name) && isset($associate_name->last_name)) {
                                $associate_name = $associate_name->first_name . ' ' . $associate_name->last_name;
                            } elseif (isset($associate_name->first_name)) {
                                $associate_name = $associate_name->first_name;
                            } else {
                                $associate_name = 'N/A';
                            }
                        }
                    } else {
                        $associate_name = 'N/A';
                    }
                    $val['associate_name'] = $associate_name;
                    $opening_date = 'N/A';
                    if (isset($row->payment_type)) {
                        if ($row->investment_id) {
                            $date = $row['investment']; //getInvestmentDetails($row->investment_id);
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
                        $type = 'Expenses';
                    } elseif ($row->payment_type == 1) {
                        $type = 'Maturity';
                    } elseif ($row->payment_type == 2) {
                        $type = 'Prematurity';
                    } elseif ($row->payment_type == 3) {
                        if ($row->sub_payment_type == '4') {
                            $type = 'Death Help';
                        } elseif ($row->sub_payment_type == '5') {
                            $type = 'Death Claim';
                        }
                    } elseif ($row->payment_type == 4) {
                        $type = "Emergency Maturity";
                    }
                    $val['advice_type'] = $type;
                    $sub_type = '';
                    if ($row->sub_payment_type == '0') {
                        $sub_type = 'Fresh Expense';
                    } elseif ($row->sub_payment_type == '1') {
                        $sub_type = 'TA Advanced';
                    } elseif ($row->sub_payment_type == '2') {
                        $sub_type = 'Advanced salary';
                    } elseif ($row->sub_payment_type == '3') {
                        $sub_type = 'Advanced Rent';
                    } elseif ($row->sub_payment_type == '4') {
                        $sub_type = 'N/A';
                    } elseif ($row->sub_payment_type == '5') {
                        $sub_type = 'N/A';
                    } else {
                        $sub_type = 'N/A';
                    }
                    $val['expense_type'] = $sub_type;
                    $val['date'] = date("d/m/Y", strtotime($row->date));
                    $val['voucher_number'] = $row->voucher_number;
                    $val['total_amount'] = $total_amount;
                    if ($row->payment_type == 2) {
                        $paymenttrf = round($row->final_amount) . ' &#8377';
                    } else {
                        if ($row->final_amount) {
                            $paymenttrf = round($row->final_amount) . ' &#8377';
                        } elseif ($row->maturity_amount_payable) {
                            $paymenttrf = round($row->maturity_amount_payable - $row->tds_amount) . ' &#8377';
                        } else if (isset($row['expenses']->amount)) {
                            $paymenttrf = $row['expenses']->amount;
                        } else if (isset($row->advanced_amount)) {
                            $paymenttrf = $row->advanced_amount;
                        } else {
                            $paymenttrf = 'N/A';
                        }
                    }
                    $val['final_amount'] = $paymenttrf;
                    $amount = '';
                    if ($row->investment_id) {
                        $amount = round(number_format((float) $paymenttrf, 2, '.', '')
                            - $total_amount) . ' &#8377';
                    }
                    //  $val['interest_amount'] =$amount;
                    if ($row->payment_type == 4) {
                        if ($row->investment_id) {
                            $data = $row['investment']; //getInvestmentDetails($row->investment_id);
                            $account = $data->account_number;
                        }
                    } else {
                        if ($row->account_number) {
                            $account = $row->account_number;
                        } else {
                            $account = 'N/A';
                        }
                    }
                    $val['account_number'] = $account;
                    if (isset($row->investment_id)) {
                        $member_id = $row['investment']->member_id;
                        $ac = $row['investment']['ssb'];
                        if ($ac) {
                            $val['ssb_account_number'] = $ac->account_no;
                        } else {
                            $val['ssb_account_number'] = 'N/A';
                        }
                    } else {
                        $val['ssb_account_number'] = 'N/A';
                    }
                    if (isset($row->bank_account_number)) {
                        $val['bank_account_number'] = $row->bank_account_number;
                    } else {
                        $val['bank_account_number'] = "N/A";
                    }
                    if (isset($row->bank_ifsc)) {
                        $val['ifsc_code'] = $row->bank_ifsc;
                    } else {
                        $val['ifsc_code'] = "N/A";
                    }
                    if ($row->is_print == 0) {
                        $print = 'Yes';
                    } else {
                        $print = 'No';
                    }
                    $val['print'] = $print;
                    if ($row->is_reject == 1) {
                        $status = 'Rejected';
                    } else {
                        $status = 'Approved';
                    }
                    $val['status'] = $status;
                    // $url = URL::to("admin/demand-advice/edit-demand-advice/".$row->id."");
                    //     $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    //   //  $btn .= '<a class="dropdown-item" href="'.$url.'"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                    //     $btn .= '<a class="dropdown-item re_demNS" data-row-id="' . $row->id . '"><i class="fa fa-retweet mr-2"></i>Re-Demand</a>';
                    //     $btn .= '</div></div></div>';
                    //     $val['action'] = $btn;
                    $rowReturn[] = $val;
                }
                $value = Cache::put('demand_advices_data', $data);
                Cache::put('demand_advices_data_count', $totalCount);
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
    /**
     * Export Reject Report in Excel
     * using cache of reportRejectListing
     * @params cache key is demand_advices_data
     */
    public function exportRejectReport(Request $request)
    {
        $data = Cache::get('demand_advices_data');
        $count = Cache::get('demand_advices_data_count');
        if ($request['demand_advice_report_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/demand_advice_redemand_report.csv";
            $fileName = env('APP_EXPORTURL') . "asset/demand_advice_redemand_report.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        if ($request['demand_advice_report_export'] == 0) {
            $totalResults = $count;
            $results = $data;
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
            foreach ($results as $row) {
                $sno++;
                $val['S.No'] = $sno;
                $val['Account Opening'] = (isset($row['investment']->created_at)) ? date("d/m/Y", strtotime($row['investment']->created_at)) : 'N/A';
                if (isset($row->company_id)) {
                    $company_name = Companies::where('id', $row->company_id)->first(['id', 'name']);
                    $val['Company Name'] = $company_name->name;
                } else {
                    $val['Company Name'] = 'N/A';
                }
                if (isset($row['branch']->name)) {
                    $val['Branch Name'] = $row['branch']->name;
                } else {
                    $val['Branch Name'] = 'N/A';
                }
                if ($row->is_reject) {
                    // $val['Reason'] = 'Rejected';
                    $val['Reason'] =  $row->demandReason ? $row->demandReason[0] ? preg_replace( "/\r|\n/", " ",$row->demandReason[0]['reason']) : 'Rejected' : 'Rejected';
                } else {
                    $val['Reason'] = 'Approved';
                }
                $member_name = 'N/A';
                $member_id = 'N/A';
                if (isset($row->investment_id)) {
                    $member_id = $row['investment']->member_id;
                    if ($member_id) {
                        $member_name = $row['investment']['member']->first_name . ' ' . $row['investment']['member']->last_name; //getMemberData
                    }
                }
                $val['Member Name'] = $member_name;
                $nominee_name = 'N/A';
                if (isset($row->investment_id)) {
                    $associate_id = $row['investment']->member_id; //getInvestmentDetails($row->investment_id)->member_id;
                    $associate_code = 'N/A';
                    if ($associate_id) {
                        $associate_code = $row['investment']['member']->associate_code; //getMemberData($associate_id)->associate_code;
                    }
                    $val['Associate Code'] = $associate_code;
                } else {
                    $val['Associate Code'] = 'N/A';
                }
                if (isset($row->tds_amount)) {
                    $val['TDS Amount'] = round($row->tds_amount);
                } else {
                    $val['TDS Amount'] = 'N/A';
                }
                $amount = '0';
                if (isset($row['sumdeposite'])) {
                    $total_amount = $row['sumdeposite']->sum('deposit');
                }
                if (isset($row['sumdeposite2'])) {
                    $total_amount = $row->sumdeposite2->sum('deposit');
                } else {
                    $total_amount = 0;
                }
                if (isset($row->investment_id)) {
                    $associate_id = $row['investment']->member_id;
                    $associate_code = 'N/A';
                    $associate_name = 'N/A';
                    if ($associate_id) {
                        $associate_code = $row['investment']['member']->associate_code; //getMemberData($associate_id)->associate_code;
                        $associate_name = $row['investment']['associateMember'];
                        if (isset($associate_name->first_name) && isset($associate_name->last_name)) {
                            $associate_name = $associate_name->first_name . ' ' . $associate_name->last_name;
                        } elseif (isset($associate_name->first_name)) {
                            $associate_name = $associate_name->first_name;
                        } else {
                            $associate_name = 'N/A';
                        }
                    }
                } else {
                    $associate_name = 'N/A';
                }
                $val['Associate Name'] = $associate_name;
                $type = '';
                if ($row->payment_type == 0) {
                    $type = 'Expenses';
                } elseif ($row->payment_type == 1) {
                    $type = 'Maturity';
                } elseif ($row->payment_type == 2) {
                    $type = 'Prematurity';
                } elseif ($row->payment_type == 3) {
                    if ($row->sub_payment_type == '4') {
                        $type = 'Death Help';
                    } elseif ($row->sub_payment_type == '5') {
                        $type = 'Death Claim';
                    }
                } elseif ($row->payment_type == 4) {
                    $type = "Emergency Maturity";
                }
                $val['Advice Type'] = $type;
                $sub_type = '';
                if ($row->sub_payment_type == '0') {
                    $sub_type = 'Fresh Expense';
                } elseif ($row->sub_payment_type == '1') {
                    $sub_type = 'TA Advanced';
                } elseif ($row->sub_payment_type == '2') {
                    $sub_type = 'Advanced salary';
                } elseif ($row->sub_payment_type == '3') {
                    $sub_type = 'Advanced Rent';
                } elseif ($row->sub_payment_type == '4') {
                    $sub_type = 'N/A';
                } elseif ($row->sub_payment_type == '5') {
                    $sub_type = 'N/A';
                } else {
                    $sub_type = 'N/A';
                }
                $val['Expense Type'] = $sub_type;
                $val['Date'] = date("d/m/Y", strtotime($row->date));
                $val['Voucher Number'] = $row->voucher_number;
                $val['Total Payment'] = $total_amount;
                if ($row->payment_type == 2) {
                    $paymenttrf = round($row->final_amount);
                } else {
                    if ($row->final_amount) {
                        $paymenttrf = round($row->final_amount);
                    } elseif ($row->maturity_amount_payable) {
                        $paymenttrf = round($row->maturity_amount_payable - $row->tds_amount);
                    } else if (isset($row['expenses']->amount)) {
                        $paymenttrf = $row['expenses']->amount;
                    } else if (isset($row->advanced_amount)) {
                        $paymenttrf = $row->advanced_amount;
                    } else {
                        $paymenttrf = 'N/A';
                    }
                }
                $val['Pay Transfer Amount'] = $paymenttrf;
                $amount = '';
                if ($row->investment_id) {
                    $amount = round(number_format((float) $paymenttrf, 2, '.', '')
                        - $total_amount);
                }
                //$val['Interest Amount'] =$amount;
                if ($row->payment_type == 4) {
                    if ($row->investment_id) {
                        $data = $row['investment']; //getInvestmentDetails($row->investment_id);
                        $account = $data->account_number;
                    }
                } else {
                    if ($row->account_number) {
                        $account = $row->account_number;
                    } else {
                        $account = 'N/A';
                    }
                }
                $val['Account Number'] = $account;
                if ($row->is_reject == 1) {
                    $status = 'Rejected';
                } else {
                    $status = 'Approved';
                }
                $val['status'] = $status;
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
                $percentage = number_format((float) $percentage, 1, '.', '');
            }
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
        }
    }
    public function rejectDemandReason(Request $demand)
    {
        $demandId = $demand->demandId;
        $reason = $demand->rejectreason;
        $response['status'] = '';
        try {
            $demandData = DemandAdvice::findOrFail($demandId);
            $redemandData = RedemandDemandAdvice::where('demand_id', $demandData->id)->first();
            if (!empty($redemandData)) {
                $redemandData->update(['redemand_times' => $redemandData->redemand_times + 1, 'reason' => $reason]);
            } else {
                $data = ['demand_id' => $demandData->id, 'redemand_times' => 1, 'reason' => $reason];
                $createdData = RedemandDemandAdvice::create($data);
                $response['status'] = 1;
            }
            $demandData->update(['is_reject' => '1', 'is_redemand' => '0']);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            $response = array(
                'status' => 'alert',
                'msg' => $ex->getMessage(),
            );
        }
        return redirect()->back()->with('success', 'Demand Request Rejected Successfully!', 'success');
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
    public static function updateBankClosingDR($bank_id, $account_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $currentDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            $Result = \App\Models\SamraddhBankClosing::find($currentDateRecord->id);
            $data['balance'] = $currentDateRecord->balance - $amount;
            $data['updated_at'] = $date;
            $Result->update($data);
            $insertid = $currentDateRecord->id;
            $getNextBankClosingRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextBankClosingRecord) {
                foreach ($getNextBankClosingRecord as $key => $value) {
                    $sResult = \App\Models\SamraddhBankClosing::find($value->id);
                    $sData['opening_balance'] = $value->closing_balance;
                    $sData['balance'] = $value->balance - $amount;
                    if ($value->closing_balance > 0) {
                        $sData['closing_balance'] = $value->closing_balance - $amount;
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                    $sResult->update($sData);
                }
            }
        } else {
            $oldDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\SamraddhBankClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data1RecordExists = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($type == 0) {
                    $data['balance'] = $oldDateRecord->balance - $amount;
                } else {
                    $data['balance'] = $oldDateRecord->balance;
                }
                $data['opening_balance'] = $oldDateRecord->balance;
                if ($data1RecordExists) {
                    $data['closing_balance'] = $oldDateRecord->balance - $amount;
                    foreach ($data1RecordExists as $key => $value) {
                        $sResult = \App\Models\BranchClosing::find($value->id);
                        $sData['opening_balance'] = $value->closing_balance;
                        $sData['balance'] = $value->balance - $amount;
                        if ($value->closing_balance > 0) {
                            $sData['closing_balance'] = $value->closing_balance - $amount;
                        }
                        $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                        $sResult->update($sData);
                    }
                } else {
                    $data['closing_balance'] = 0;
                }
                $data['bank_id'] = $bank_id;
                $data['account_id'] = $account_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\SamraddhBankClosing::create($data);
                $insertid = $transcation->id;
            } else {
                $data['balance'] = $amount;
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                $data['bank_id'] = $bank_id;
                $data['account_id'] = $account_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\SamraddhBankClosing::create($data);
                $insertid = $transcation->id;
            }
        }
        return $insertid;
    }

    public function getInvestmentDetails(Request $request)
    {
        $investmentAccount = $request->val;
        $type = $request->type;
        $subtype = $request->subtype;
        $companyId = $request->company_id;
        $cDate = date("Y-m-d");
        $state_id = getBranchDetail($request->branch)->state_id;
        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $state_id);
        $investmentDetails = Memberinvestments::select('account_number', 'associate_id', 'branch_id', 'current_balance', 'deposite_amount', 'interest_rate', 'id', 'plan_id', 'created_at', 'member_id', 'customer_id', 'tenure', 'ssb_account_number','maturity_date')
            ->with([
                'investmentNomiees:id,investment_id,name,phone_number',
                'plan:id,name,plan_category_code,plan_sub_category_code,prematurity,death_help',
                'member:id,member_id,first_name,last_name,mother_name,father_husband,mobile_no,signature,photo,status,is_block',
                'ssb:id,member_id,account_no',
                'memberBankDetail',
                'investmentNomiees'
            ])
            ->where('account_number', $investmentAccount)
            ->where('account_number', 'not like', "%R-%")
            ->where('is_mature', 1)
            ->where('investment_correction_request', 0)
            ->where('renewal_correction_request', 0)
            ->where('company_id', $companyId)
            ->where('branch_id', $request->branch)
            ->first();
        if ($investmentDetails) {
            $date = date('Y-m-d', strtotime($investmentDetails->created_at));
            $newdate = date('Y-m-d', strtotime($date . '+ 1 year'));
            $requestDate = date('Y-m-d', strtotime(convertDate($request->date)));
            // dd($date, $newdate,$requestDate);
            $to = ($globaldate >= $investmentDetails->maturity_date) ? \Carbon\Carbon::parse($investmentDetails->maturity_date) : \Carbon\Carbon::parse($globaldate);
            /** Mahesh has added ->startOfDay() on 5 jan 2024 because without this we were unable to make demand on same day */
            $from = \Carbon\Carbon::parse($investmentDetails->created_at)->startOfDay();
            $investmentMonths = $to->diffInMonths($from, true);
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
                        if ($subtype != '1') {
                            if (((($investmentDetails->plan->prematurity == '0' && $type != 4)) && $subtype != '0' && $subtype != '1') || $investmentDetails->plan->plan_category_code == 'S') {
                                $message = 'Prematurity option not available for this plan!';
                                $status = 500;
                            } else {
                                if ($investmentDetails->plan->plan_category_code != 'S') {
                                    if (($type == 2 && $subtype == 1) || ($subtype != 4)) {
                                        if ($requestDate < $newdate && $investmentDetails->plan->plan_sub_category_code == 'X') {
                                            $message = 'You Cannot Mature Plan before 1 Year  !';
                                            $status = 500;
                                        } else {
                                            if($requestDate < $investmentDetails->maturity_date){
                                                $mat_date = date('d/m/Y', strtotime($investmentDetails->maturity_date));
                                                // $message = "The Maturity Date of This Plan is $mat_date So Please Have Patience!";
                                                $message = "You Cannot Mature Plan before Maturity Date ($mat_date) !";
                                                $status = 500;
                                            } else if ($investmentDetails) {
                                                $message = '';
                                                $status = 200;
                                            } else {
                                                $message = 'Record Not Found!';
                                                $status = 400;
                                            }
                                        }
                                    } else {
                                        $maturityDate = date('Y-m-d', strtotime($investmentDetails->created_at . ' + ' . ($investmentDetails->tenure) . ' year'));
                                        $currentDate = date_create($cDate);
                                        $diff = strtotime($maturityDate) - strtotime($cDate);
                                        $daydiff = abs(round($diff / 86400));
                                        if ($cDate < $maturityDate && $type != 4) {
                                            $message = 'You Cannot Mature Plan before Maturity Date!';
                                            $status = 500;
                                        } else {
                                            $message = '';
                                            $status = 200;
                                        }
                                    }
                                    if($subtype == 0 && $type == 4 && $investmentDetails->plan_id != 6){
                                        $message = 'Death Help is only for Samraddh Jeevan!';
                                        $status = 500;
                                    }
                                } else {
                                    $message = 'Record Not Found!';
                                    $status = 400;
                                }
                            }
                        } else if ($requestDate < $newdate && $type == 2 && $subtype == 1) {
                            $message = 'You Cannot Mature Plan before 1 Year  !';
                            $status = 500;
                        } else {
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
                                    if ($subtype != 1) {
                                        $interestData = getplanroi($investmentDetails->plan_id);
                                        $checkRoi = getRoi($interestData, $investmentMonths, $investmentDetails);
                                        $ActualInterest = $checkRoi['ActualInterest'];
                                        if (!$checkRoi['roiExist']) {
                                            $message = 'Maturity Setting Not Updated for this Plan!';
                                            $status = 400;
                                        }
                                        $isDefaulter = 0;
                                        $finalAmount = 0;
                                        // if ($investmentDetails->plan->plan_category_code == 'D' ||  $investmentDetails->plan->plan_category_code == 'M') {
                                        //     $result = maturityCalculation($investmentDetails, 'demand_create',$investmentMonths,$ActualInterest);
                                        //     $isDefaulter = $result['defaulter'];
                                        //     $finalAmount = $result['final_amount'];
                                        // }
                                    } else {
                                        $isDefaulter = 0;
                                        $finalAmount = 0;
                                    }
                                }
                            }
                        } else {
                            $isDefaulter = 0;
                            $finalAmount = 0;
                        }
                    }
                }
            } else {
                $isDefaulter = '';
                $finalAmount = 0;
                $message = 'Please upload Photo and Signature of Customer and demand again!';
                $status = 400;
            }
            $finalAmount = number_format((float) $finalAmount, 2, '.', '');
        } else {
            $isDefaulter = '';
            $finalAmount = 0;
            $message = 'Record Not Found!';
            $status = 400;
        }
        $signature = !empty($investmentDetails) ? $investmentDetails['member'] ? $investmentDetails['member']['signature'] ? ImageUpload::fileExists('profile/member_signature/' . $investmentDetails['member']['signature']) ? ImageUpload::generatePreSignedUrl('profile/member_signature/' . $investmentDetails['member']['signature']) : '' : '' : '' : '';
        $photo = !empty($investmentDetails) ? $investmentDetails['member'] ? $investmentDetails['member']['signature'] ? ImageUpload::fileExists('profile/member_avatar/' . $investmentDetails['member']['photo']) ? ImageUpload::generatePreSignedUrl('profile/member_avatar/' . $investmentDetails['member']['photo']) : '' : '' : '' : '';
        if(isset($investmentDetails) && isset($investmentDetails['current_balance'])){
            $investmentDetails['current_balance'] = InvestmentBalance::where('investment_id',$investmentDetails['id'])->value('totalBalance');
        }
        $return_array = compact('investmentDetails', 'isDefaulter', 'finalAmount', 'message', 'status', 'signature', 'photo');
        return json_encode($return_array);
    }
}