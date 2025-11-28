<?php
namespace App\Http\Controllers\Admin\AccountHeadReport;

use Illuminate\Http\Request;
use Auth;
use App\Models\Settings;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\SamraddhBank;
use App\Models\Member;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Models\AccountHeads;
use App\Models\AllTransaction;
use App\Models\AllHeadTransaction;
use App\Models\TransactionType;
use App\Models\ShareHolder;
use Illuminate\Support\Facades\Schema;
use DB;
use URL;

class AccountHeadReportController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index($head_id, $label)
    {
        if (check_my_permission(Auth::user()->id, "146") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = "Account Head Management | Account Head Ledger Report";
        $date_filter = '';
        $branch_filter = '';
        $end_date = '';
        if (isset($_GET['date'])) {
            $date_filter = $_GET['date'];
        }
        if (isset($_GET['end_date'])) {
            $end_date = $_GET['end_date'];
        }
        if (isset($_GET['branch'])) {
            $branch_filter = $_GET['branch'];
        }

        $data['head'] = $head_id;
        $data['label'] = $label;
        $info = 'head' . $label;
        $data['branch'] = $branch_filter;
        $data['date'] = $date_filter;
        $data['end_date'] = $end_date;
        $CRTotal = AllHeadTransaction::where('head_id', $head_id)->where('payment_type', 'CR')->where('is_deleted', 0)->sum('amount');
        $DRTotal = AllHeadTransaction::where('head_id', $head_id)->where('payment_type', 'CR')->where('is_deleted', 0)->sum('amount');
        $data['total'] = $CRTotal - $DRTotal;
        $data['detail'] = \App\Models\AccountHeads::where('head_id', $head_id)->where('labels', $label)->where('status', 0)->first();
        return view('templates.admin.account_head_report.account_head_report', $data);
    }

    public function ledgerListing(Request $request)
    {

        if ($request->ajax()) {


            $head_id = $request->head_id;
            $label = $request->label;
            $info = 'head' . $label;
            $date = $request->date;
            $end_date = $request->end_date;
            $branch = $request->branch;
            $head_ids = array($head_id);
            $subHeadsIDS = AccountHeads::where('head_id', $head_id)->where('status', 0)->pluck('head_id')->toArray();
            $subHeadsIDS1 = AccountHeads::where('head_id', $head_id)->where('status', 0)->first();
            // dd($subHeadsIDS1);
            if (count($subHeadsIDS) > 0) {
                $headIDS = array_merge($head_ids, $subHeadsIDS);
                $a = get_change_sub_account_head($headIDS, $subHeadsIDS, true);

            }

            foreach ($a as $key => $row) {
                $ids[] = $row;
            }
            if (count($ids) > 0) {
                $data = $data = \App\Models\AllHeadTransaction::with(['branch' => function ($query) {
                    $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone'); }])->whereIn('head_id', $ids)->where('is_deleted', 0);
            } else {
                $data = $data = \App\Models\AllHeadTransaction::with(['branch' => function ($query) {
                    $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone'); }])->whereIn('head_id', [$head_id])->where('is_deleted', 0);
            }

            if ($date != '') {

                $startDate = date("Y-m-d", strtotime(convertDate($date)));

                if ($end_date != '') {

                    $endDate = date("Y-m-d ", strtotime(convertDate($end_date)));

                } else {

                    $endDate = '';

                }
                $data = $data->whereBetween('entry_date', [$startDate, $endDate]);
            }


            if ($branch != '') {
                $data = $data->whereHas('branch', function ($query) use ($branch) {
                    $query->where('branch.id', $branch);
                });
            }

            $data1 = $data->count('id');
            $count = $data1;

            $data = $data->offset($_POST['start'])->limit($_POST['length'])->orderBy('created_at', 'DESC')->get();

            $totalCount = count($data);

            $sno = $_POST['start'];
            $rowReturn = array();
            $types = getTransactionTypeCustom();

            foreach ($data as $row) {
                // pd($row);
                $sno++;

                $val['DT_RowIndex'] = $sno;
                if (isset($row['branch']->name)) {
                    $val['branch'] = $row['branch']->name;
                } else {
                    $val['branch'] = 'N/A';
                }
                if (isset($row['branch']->branch_code)) {
                    $val['branch_code'] = $row['branch']->branch_code;
                } else {
                    $val['branch_code'] = 'N/A';
                }
                // if(isset($row['sector']->sector))
                // {
                //     $val['sector']=$row['branch']->sector;
                // }
                // else{
                //     $val['sector']='N/A';
                // }
                // if(isset($row['regan']->sector))
                // {
                //     $val['regan']=$row['branch']->regan;
                // }
                // else{
                //     $val['regan']='N/A';
                // }
                //  if(isset($row['zone']->sector))
                // {
                //     $val['zone']=$row['branch']->zone;
                // }
                // else{
                //     $val['zone']='N/A';
                // }



                if ($row->type != 21) {
                    if (array_key_exists($row->type . '_' . $row->sub_type, $types)) {
                        $type = $types[$row->type . '_' . $row->sub_type];
                    }
                }

                if ($row->type == 21 && $row->sub_type == '') {
                    $record = ReceivedVoucher::where('id', $row->type_id)->first();
                    if ($record) {
                        $type = $record->particular;

                    } else {
                        $type = "N/A";
                    }

                }

                if ($row->type == 22 || $row->type == 23) {
                    if ($row->sub_type == 222) {
                        $type = $row->description;
                    }


                }


                $val['type'] = $type;
                $val['amount'] = number_format((float) $row->amount, 2, '.', '');
                $val['description'] = $row->description;

                $account_number = 'N/A';
                if ($row->type == 2 || $row->type == 3) {
                    $account_number = getInvestmentDetails($row->type_id);
                    if (isset($account_number->account_number)) {
                        $account_number = $account_number->account_number;
                    } else {
                        $account_number = 'N/A';
                    }
                }
                if ($row->type == 4) {
                    $account_number = getSavingAccountMemberId($row->type_id);
                    if (isset($account_number->account_no)) {
                        $account_number = $account_number->account_no;
                    } else {
                        $account_number = 'N/A';
                    }
                }
                if ($row->type == 5) {
                    $account_number = getLoanDetail($row->type_id);
                    if (isset($account_number->account_number)) {
                        $account_number = $account_number->account_number;
                    } else {
                        $account_number = 'N/A';
                    }
                }
                if ($row->type == 13) {
                    $v_no = \App\Models\DemandAdvice::where('id', $row->type_id)->first();

                    if (isset($v_no->account_number)) {
                        $account_number = $v_no->account_number;
                    } else {
                        $account_number = "N/A";
                    }
                }
                if ($row->type == 15) {
                    $ac = getSavingAccountMemberId($row->ssb_account_id_from);
                    // dd($ac->account_no);
                    if (isset($ac->account_no)) {
                        $account_number = $ac->account_no;
                    } else {
                        $account_number = "N/A";
                    }
                } else {
                    $account_number = "N/A";
                }
                $val['ac'] = $account_number;


                if ($row->head_id) {

                    $member_name = $subHeadsIDS1->sub_head;

                } else {
                    $member_name = 'N/A';
                }
                $val['member_name'] = $member_name;


                if ($row->type == 1 || $row->type == 2) {
                    $associate_name = getSeniorData($row->type_id, 'first_name') . ' ' . getSeniorData($row->type_id, 'last_name');
                } else {
                    $associate_name = "N/A";
                }
                $val['associate_name'] = $associate_name;



                $payment_type = 'N/A';
                if ($row->payment_type == 'DR') {
                    $payment_type = 'Debit';
                }
                if ($row->payment_type == 'CR') {
                    $payment_type = 'Credit';
                }
                $val['payment_type'] = $payment_type;



                $payment_mode = 'N/A';
                if ($row->payment_mode == 0) {
                    $payment_mode = 'Cash';
                }
                if ($row->payment_mode == 1) {
                    $payment_mode = 'Cheque';
                }
                if ($row->payment_mode == 2) {
                    $payment_mode = 'Online Transfer';
                }
                if ($row->payment_mode == 3) {
                    $payment_mode = 'SSB Transfer Through JV';
                }
                if ($row->payment_mode == 4) {
                    if ($row->payment_type == 'CR') {
                        $payment_mode = "Auto Credit";
                    } else {
                        $payment_mode = "Auto Debit";
                    }
                }
                if ($row->payment_mode == 6) {
                    $payment_mode = "JV";
                }
                $val['payment_mode'] = $payment_mode;



                if ($row->v_no) {
                    $v_no = $row->v_no;
                    $voucher_no = $v_no;
                }
                if ($row->type == 13) {
                    $v_no = \App\Models\DemandAdvice::where('id', $row->type_id)->first();
                    $voucher_no = $v_no->voucher_number;
                } else {
                    $voucher_no = "N/A";
                }
                $val['voucher_no'] = $voucher_no;



                if ($row->v_date) {
                    $voucher_date = date("d/m/Y", strtotime(convertDate($row->v_date)));
                    ;
                } else {
                    $voucher_date = "N/A";
                }
                $val['voucher_date'] = $voucher_date;


                if ($row->cheque_no) {
                    $cheque_no = $row->cheque_no;
                } else {
                    $cheque_no = "N/A";
                }
                $val['cheque_no'] = $cheque_no;


                if ($row->cheque_id) {
                    if ($row->cheque_type == 1) {
                        $cheque_data = \App\Models\SamraddhChequeIssue::select('id', 'updated_at', 'cheque_id', 'cheque_issue_date', 'created_at')->where('cheque_id', $row->cheque_id)->first();

                        $cheque_date = date('d/m/Y', strtotime(convertDate($cheque_data->cheque_issue_date)));
                    } else if ($row->cheque_type == 0) {
                        $cheque_data = \App\Models\ReceivedChequePayment::select('id', 'cheque_id', 'created_at', 'updated_at')->where('cheque_id', $row->cheque_id)->first();
                        $cheque_date = date('d/m/Y', strtotime(convertDate($cheque_data->created_at)));
                    } else {
                        $cheque_date = "N/A";
                    }
                } else {
                    $cheque_date = "N/A";
                }
                $val['cheque_date'] = $cheque_date;



                if ($row->transction_no) {
                    $transction_no = $row->transction_no;
                } else {
                    $transction_no = "N/A";
                }
                $val['utr_transaction_number'] = $transction_no;



                $val['transaction_date'] = date("d/m/Y", strtotime(convertDate($row->entry_date)));



                if ($row->bank_id) {
                    $transction_bank_to_name = getSamraddhBank($row->bank_id);
                    $transction_bank_to_name = $transction_bank_to_name->bank_name;
                } else {
                    $transction_bank_to_name = "N/A";
                }
                $val['received_bank'] = $transction_bank_to_name;


                if ($row->bank_ac_id) {
                    $transction_bank_to_ac_no = getSamraddhBankAccountId($row->bank_ac_id);
                    $transction_bank_to_ac_no = $transction_bank_to_ac_no->account_no;
                } else {
                    $transction_bank_to_ac_no = "N/A";
                }
                $val['received_bank_account'] = $transction_bank_to_ac_no;


                if ($row->entry_date) {
                    $date = date("d/m/Y", strtotime(convertDate($row->entry_date)));
                } else {
                    $date = "N/A";
                }
                $val['date'] = $date;

                $rowReturn[] = $val;

            }



        }

        $output = array("draw" => $_POST['draw'], "recordsTotal" => $count, "recordsFiltered" => $count, "data" => $rowReturn);

        return json_encode($output);

    }

    public function transaction($head_id, $label)
    {
        if (check_my_permission(Auth::user()->id, "146") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $data['title'] = "Account Head Management | Account Head Ledger Report";
        $date_filter = '';
        $end_date = '';
        $branch_filter = '';
        if (isset($_GET['date'])) {
            $date_filter = $_GET['date'];
        }
        if (isset($_GET['end_date'])) {
            $end_date = $_GET['end_date'];
        }
        if (isset($_GET['branch'])) {
            $branch_filter = $_GET['branch'];
        }
        $data['head'] = $head_id;
        $data['label'] = $label;
        $info = 'head' . $label;
        $data['branch'] = $branch_filter;
        $data['date'] = $date_filter;
        $data['end_date'] = $end_date;
        $CRTotal = AllHeadTransaction::where('head_id', $head_id)->where('payment_type', 'CR')->where('is_deleted', 0)->sum('amount');
        $DRTotal = AllHeadTransaction::where('head_id', $head_id)->where('payment_type', 'CR')->where('is_deleted', 0)->sum('amount');
        $data['total'] = $CRTotal - $DRTotal;
        $data['detail'] = \App\Models\AccountHeads::where('head_id', $head_id)->where('labels', $label)->first();
        return view('templates.admin.account_head_report.transaction_report', $data);
    }
    public function transaction_list(Request $request)
    {



        if ($request->ajax()) {


            $id = $request->head_id;
            $label = $request->label;
            $info = 'head' . $label;
            $date = $request->date;
            $end_date = $request->end_date;
            $branch = $request->branch;


            $data = \App\Models\AllHeadTransaction::with(['branch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone'); }])->where('head_id', $id)->where('is_deleted', 0);

            if ($date != '' && $end_date == "") {
                $data = $data->whereDate('entry_date', '<=', $date);

            }
            if ($date != '' && $end_date != "") {
                $date = date("Y-m-d", strtotime(convertDate($date)));
                $end_date = date("Y-m-d", strtotime(convertDate($end_date)));

                $data = $data->whereBetween('entry_date', [$date, $end_date]);
            }
            if ($branch != '') {
                $data = $data->whereHas('branch', function ($query) use ($branch) {
                    $query->where('branch.id', $branch);
                });
            }
            $data = $data->orderBy('created_at', 'DESC')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('branch', function ($row) {
                    if ($row['branch']) {
                        $branch = $row['branch']->name;
                        return $branch;
                    } else {
                        return "N/A";
                    }

                })
                ->rawColumns(['branch'])

                ->addColumn('branch_code', function ($row) {
                    if ($row['branch']) {
                        $branch_code = $row['branch']->branch_code;
                        return $branch_code;
                    } else {
                        return "N/A";
                    }
                })
                ->rawColumns(['branch_code'])
                ->addColumn('sector', function ($row) {
                    if ($row['branch']) {
                        $sector = $row['branch']->sector;
                        return $sector;
                    } else {
                        return "N/A";
                    }
                })
                ->rawColumns(['sector'])
                ->addColumn('regan', function ($row) {
                    if ($row['branch']) {
                        $regan = $row['branch']->regan;
                        return $regan;
                    } else {
                        return "N/A";
                    }
                })
                ->rawColumns(['regan'])
                ->addColumn('zone', function ($row) {
                    if ($row['branch']) {
                        $zone = $row['branch']->zone;
                        return $zone;
                    } else {
                        return "N/A";
                    }
                })
                ->rawColumns(['zone'])
                ->addColumn('type', function ($row) {
                    $getTransType = \App\Models\TransactionType::where('type', $row->type)->where('sub_type', $row->sub_type)->first();
                    $type = '';
                    if (isset($getTransType->type)) {
                        if ($row->type == $getTransType->type) {
                            if ($row->sub_type == $getTransType->sub_type) {
                                $type = $getTransType->title;
                            }
                        }
                    }

                    if ($row->type == 21) {
                        $record = ReceivedVoucher::where('id', $row->type_id)->first();
                        if ($record) {
                            $type = $record->particular;
                        } else {
                            $type = "N/A";
                        }
                    }


                    return $type;
                })
                ->rawColumns(['type'])
                ->addColumn('amount', function ($row) {
                    return number_format((float) $row->amount, 2, '.', '');
                })
                ->rawColumns(['amount'])
                ->addColumn('description', function ($row) {
                    if ($row) {
                        return $row->description;
                    } else {
                        return "N/A";
                    }
                })
                ->rawColumns(['description'])

                ->addColumn('ac', function ($row) {
                    if ($row->type == 2 || $row->type == 3) {
                        $account_number = getInvestmentDetails($row->type_id);
                        return $account_number->account_number;
                    }

                    if ($row->type == 4) {
                        $account_number = getSavingAccountMemberId($row->type_id);
                        if ($account_number) {
                            return $account_number->account_no;
                        } else {
                            return 'N/A';
                        }
                    }
                    if ($row->type == 5) {
                        $account_number = getLoanDetail($row->type_id);
                        if ($account_number) {
                            return $account_number->account_number;
                        } else {
                            return 'N/A';
                        }
                    }

                    if ($row->type == 13) {
                        $v_no = \App\Models\DemandAdvice::where('id', $row->type_id)->first();
                        return $v_no->account_number;
                    } else {
                        return "N/A";
                    }

                })
                ->rawColumns(['ac'])
                ->addColumn('member_name', function ($row) {
                    if ($row->member_id) {

                        return getMemberData($row->member_id)->first_name . ' ' . getMemberData($row->member_id)->last_name;
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['member_name'])
                ->addColumn('associate_name', function ($row) {
                    if ($row->type == 1 || $row->type == 2) {
                        return getSeniorData($row->type_id, 'first_name') . ' ' . getSeniorData($row->type_id, 'last_name');
                    } else {
                        return "N/A";
                    }
                })
                ->rawColumns(['associate_name'])
                ->addColumn('payment_type', function ($row) {
                    $payment_type = 'N/A';
                    if ($row->payment_type == 'DR') {
                        $payment_type = 'Debit';
                    }
                    if ($row->payment_type == 'CR') {
                        $payment_type = 'Credit';
                    }
                    return $payment_type;

                })
                ->rawColumns(['payment_type'])
                ->addColumn('payment_mode', function ($row) {
                    $payment_type = 'N/A';
                    if ($row->payment_mode == 0) {
                        $payment_mode = 'Cash';
                    }
                    if ($row->payment_mode == 1) {
                        $payment_mode = 'Cheque';
                    }
                    if ($row->payment_mode == 2) {
                        $payment_mode = 'Online Transfer';
                    }
                    if ($row->payment_mode == 3) {
                        $payment_mode = 'SSB Transfer Through JV';
                    }
                    if ($row->payment_mode == 4) {
                        if ($row->payment_type == 'CR') {
                            $payment_mode = "Auto Credit";
                        } else {
                            $payment_mode = "Auto Debit";
                        }
                    }
                    if ($row->payment_mode == 6) {
                        $payment_mode = 'JV';
                    }

                    return $payment_mode;

                })
                ->rawColumns(['payment_mode'])
                ->addColumn('voucher_no', function ($row) {
                    if ($row->v_no) {
                        $v_no = $row->v_no;
                        return $v_no;
                    }
                    if ($row->type == 13) {
                        $v_no = \App\Models\DemandAdvice::where('id', $row->type_id)->first();
                        return $v_no->voucher_number;
                    } else {
                        return "N/A";
                    }

                })
                ->rawColumns(['voucher_no'])

                ->addColumn('cheque_no', function ($row) {
                    if ($row->cheque_no) {
                        $cheque_no = $row->cheque_no;
                        return $cheque_no;
                    } else {
                        return "N/A";
                    }

                })
                ->rawColumns(['cheque_no'])
                ->addColumn('cheque_date', function ($row) {
                    if ($row->cheque_date) {
                        $cheque_date = date("d/m/Y", strtotime(convertDate($row->cheque_date)));
                        return $cheque_date;
                    } else {
                        return "N/A";
                    }

                })
                ->rawColumns(['cheque_date'])
                ->addColumn('utr_transaction_number', function ($row) {
                    if ($row->transction_no) {
                        $transction_no = $row->transction_no;
                        return $transction_no;
                    } else {
                        return "N/A";
                    }

                })
                ->rawColumns(['utr_transaction_number'])
                ->addColumn('transaction_date', function ($row) {
                    if ($row->transaction_date) {
                        $transaction_date = date("d/m/Y", strtotime(convertDate($row->transction_date)));
                        return $transaction_date;
                    } else {
                        return "N/A";
                    }
                })
                ->rawColumns(['transaction_date'])
                ->addColumn('received_bank', function ($row) {
                    if ($row->transction_bank_to) {
                        $transction_bank_to_name = getSamraddhBank($row->transction_bank_to);
                        return $transction_bank_to_name->bank_name;
                    } else {
                        return "N/A";
                    }

                })
                ->rawColumns(['received_bank'])
                ->addColumn('received_bank_account', function ($row) {
                    if ($row->transction_bank_to) {
                        $transction_bank_to_ac_no = getSamraddhBankAccountId($row->transction_bank_to);
                        return $transction_bank_to_ac_no->account_no;
                    } else {
                        return "N/A";
                    }
                })
                ->rawColumns(['received_bank_account'])
                ->addColumn('date', function ($row) {
                    if ($row->entry_date) {

                        $date = date("d/m/Y", strtotime(convertDate($row->entry_date)));
                        return $date;
                    } else {
                        return "N/A";
                    }

                })
                ->rawColumns(['date'])

                ->make(true);
        }
    }
}