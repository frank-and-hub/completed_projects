<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Request1;
use Validator;
use App\Models\Branch;
use App\Models\SavingAccount;
use App\Models\SavingAccountTranscation;
use App\Models\Memberinvestments;
use App\Models\Memberinvestmentsnominees;
use App\Models\SavingAccountTransactionView;
use App\Models\Daybook;
use App\Models\AllHeadTransaction;
use App\Http\Controllers\Branch\CommanTransactionsController;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Scopes\ActiveScope;

/*
    |---------------------------------------------------------------------------
    | Branch Panel -- Member Management MemberController
    |--------------------------------------------------------------------------
    |
    | This controller handles members all functionlity.
*/
class PassbookController extends Controller
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
     * All Investment Transactions.
     * Route: investment/passbook/transaction
     * Method: get
     * @return  array()  Response
     */
    public function passbookTransaction($id, $code)
    {
        $data['accountTranscation'] = array();
        $data['accountDetail'] = array();
        $data['code'] = $code;
        $data['title'] = 'Investment | Transactions';
        if ($code == 'S') {
            $data['accountDetail'] = SavingAccount::where('member_investments_id', $id)->where('is_deleted', 0)->first();
        } else {
            $data['accountDetail'] = Memberinvestments::with([
                'plan' => function ($q) {
                    $q->withoutGlobalScope(ActiveScope::class);
                }
            ])->where('id', $id)->where('is_deleted', 0)->first();
        }
        return view('templates.admin.investment_management.passbook.transaction', $data);
    }
    /**
     * All Investment Transactions List.
     * Route: tran_list
     * Method: get
     * @return  array()  Response
     */
    public function transactionList(Request $request)
    {
        if ($request->ajax()) {
            $code = $request->code;
            if ($request->code == 'S') {
                // $account_no = SavingAccount::select('id')->where('id',$request->id)->first();
                $id = $request->id;
                $data = SavingAccountTransactionView::where('saving_account_id', $id)->orderby('opening_date', 'DESC')->orderby('id', 'DESC')->get();
                $amount = 0;
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('tranid', function ($row) {
                        $tranid = $row->transaction_id;
                        return $tranid;
                    })
                    ->rawColumns(['tranid'])
                    ->addColumn('date', function ($row) {
                        $date = date("d/m/Y", strtotime($row->opening_date));
                        return $date;
                    })
                    ->rawColumns(['date'])
                    ->addIndexColumn()
                    ->addColumn('tran_by', function ($row) {
                        $tran_by = $row->transaction_by;
                        return $tran_by;
                    })
                    ->rawColumns(['tran_by'])
                    ->addIndexColumn()
                    ->addColumn('trans_date', function ($row) {
                        if (isset($row->transaction_default_date)) {
                            $tranid = date("d/m/Y H:i:s", strtotime($row->transaction_default_date));
                        } else {
                            $tranid = 'N/A';
                        }
                        return $tranid;
                    })
                    ->rawColumns(['trans_date'])
                    ->addColumn('description', function ($row) {
                        $description = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
                        return $description;
                    })
                    ->rawColumns(['description'])
                    ->addColumn('reference_no', function ($row) use ($code) {
                        return $reference_no = $row->reference_no;
                    })
                    ->rawColumns(['reference_no'])
                    ->addColumn('withdrawal', function ($row) {
                        $withdrawal = $row->withdrawal;
                        return $withdrawal;
                    })
                    ->rawColumns(['withdrawal'])
                    ->addColumn('deposit', function ($row) {
                        $deposit = $row->deposit;
                        return $deposit;
                    })
                    ->rawColumns(['deposit'])
                    ->addColumn('opening_balance', function ($row) {
                        $opening_balance = $row->opening_balance;
                        return $opening_balance;
                    })
                    ->rawColumns(['opening_balance'])
                    ->addColumn('action', function ($row) {
                        $url = URL::to("admin/investment/passbook/ssbtransaction/" . $row->id . "");
                        $btn = '<a class="dropdown-item" href="' . $url . '" title="Transaction Detail"><i class="icon-eye  mr-2"></i></a>  ';
                        /*$btn = '<a class="dropdown-item" href="javascript:void(0);" title="Member Detail"><i class="icon-eye  mr-2"></i>View</a>  ';*/
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            } else {
                // dd($request->id);
                $accountDetail = Memberinvestments::with('plan')
                    ->whereHas('plan', function ($query) {
                        $query->where('plan_category_code', '!=', 'S');
                    })
                    ->where('id', $request->id)
                    ->first();
                // $data = Daybook::where('account_no', $accountDetail->account_number)
                //     ->whereIn('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 29, 30])
                //     ->where('is_deleted', 0)
                //     ->orderByDesc('created_at')
                //     ->orderByDesc('id')
                //     ->get();
                $data = Daybook::selectRaw('*, (
                    SELECT SUM(IF(deposit > 0, deposit, -withdrawal))
                    FROM day_books AS sub
                    WHERE sub.account_no = day_books.account_no
                    AND (sub.created_at) <= (day_books.created_at)
                    AND sub.transaction_type IN (2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 29, 30)
                    AND sub.is_deleted = 0
                ) AS opening_balance')
                    ->where('account_no', $accountDetail->account_number)
                    ->whereIn('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 29, 30])
                    ->where('is_deleted', 0)
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->get();
                // pd($data->toArray());
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('tranid', function ($row) {
                        $tranid = $row->id;
                        return $tranid;
                    })
                    ->rawColumns(['tranid'])
                    ->addColumn('date', function ($row) {
                        $date = date("d/m/Y", strtotime($row->created_at));
                        return $date;
                    })
                    ->rawColumns(['date'])
                    ->addIndexColumn()
                    ->addColumn('tran_by', function ($row) {
                        $tran_by = (($row->is_app == 1) ? 'Associcate' : (($row->is_app == 2) ? 'E-Passbook' : 'Software'));
                        return $tran_by;
                    })
                    ->rawColumns(['tran_by'])
                    ->addColumn('trans_date', function ($row) {
                        if (isset($row->created_at_default)) {
                            $tranid = date("d/m/Y H:i:s", strtotime($row->created_at_default));
                        } else {
                            $tranid = 'N/A';
                        }
                        return $tranid;
                    })
                    ->rawColumns(['trans_date'])
                    ->addColumn('description', function ($row) {
                        $description = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
                        return $description;
                    })
                    ->rawColumns(['description'])
                    ->addColumn('reference_no', function ($row) use ($code) {
                        $reference_no = '';
                        if ($row->payment_mode == 1) {
                            $reference_no = $row->cheque_dd_no;
                        }
                        if ($row->payment_mode == 4 || $row->payment_mode == 5) {
                            $reference_no = $row->reference_no;
                        }
                        if ($row->payment_mode == 3) {
                            $reference_no = $row->online_payment_id;
                        }
                        return $reference_no;
                    })
                    ->rawColumns(['reference_no'])
                    ->addColumn('withdrawal', function ($row) {
                        if ($row->withdrawal > 0) {
                            $withdrawal = $row->withdrawal;
                        } else {
                            $withdrawal = '';
                        }
                        return $withdrawal;
                    })
                    ->rawColumns(['withdrawal'])
                    ->addColumn('deposit', function ($row) {
                        if ($row->deposit > 0) {
                            $deposit = $row->deposit;
                        } else {
                            $deposit = '';
                        }
                        return $deposit;
                    })
                    ->rawColumns(['deposit'])
                    ->addColumn('opening_balance', function ($row) {
                        $opening_balance = $row->opening_balance;
                        return $opening_balance;
                    })
                    ->rawColumns(['opening_balance'])
                    ->addColumn('action', function ($row) {
                        $url = URL::to("admin/investment/passbook/transaction/" . $row->id . "");
                        $btn = '<a class="dropdown-item" href="' . $url . '" title="Transaction Detail"><i class="icon-eye  mr-2"></i></a>  ';
                        $url2 = URL::to("admin/investment/renew/receipt/" . $row->id . "");
                        // if($row->payment_type == 'CR')
                        // {
                        if($row->transaction_type != 18){
                            $btn .= '<a class="dropdown-item" style="color:#2196F3;" href="' . $url2 . '" title="Receipt"><i class="fa fa-file  mr-2"></i></a>  ';
                        }
                        /*$btn = '<a class="dropdown-item" href="javascript:void(0);" title="Member Detail"><i class="icon-eye  mr-2"></i>View</a>  ';*/
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
        }
    }
    public function transactionStart(Request $request)
    {
        $data['title'] = 'Passbook | Transaction';
        $data['accountTranscation'] = array();
        $data['accountDetail'] = array();
        $data['code'] = $code = $request['code'];
        $start = $request['transaction_id_from'];
        $end = $request['transaction_id_to'];
        if (Request1::isMethod('get')) {
            return back()->with('alert', 'Please submit form');
        }
        $data['eliOpeningAmount'] = 0.00;
        $data['accountsNumber'] = array('R-066523000256', 'R-084511001514', 'R-066523000257', 'R-066523000620', 'R-084523000800', 'R-066523000510', 'R-066523000258', 'R-084504006762', 'R-066504006287', 'R-066523000509', 'R-066523000588', 'R-084504007930');
        try {
            if ($code == 'S') {
                $data['accountDetail'] = SavingAccount::where('id', $request['id'])->first();
                // $data['accountTranscation'] = SavingAccountTranscation::where('saving_account_id',$request['id'])->whereBetween(\DB::raw('id'), [$start, $end])->orderby('created_at','ASC')->get();
                $data['accountTranscation'] = SavingAccountTransactionView::where('saving_account_id', $request['id'])->whereBetween(\DB::raw('id'), [$start, $end])->orderby('opening_date', 'ASC')->get();
            } else {
                $data['accountDetail'] = Memberinvestments::with('plan')->whereHas('plan', function ($query) {
                    $query->where('plan_category_code', '!=', 'S');
                })->where('id', $request['id'])->first();
                $data['eliOpeningAmount'] = Daybook::where('investment_id', $request['id'])->whereIn('account_no', $data['accountsNumber'])->where('is_eli', 1)->where(function ($q) {
                    $q->where('transaction_type', 2)->orWhere('transaction_type', 4); })->where('is_deleted', 0)->pluck('amount')->first();
                $data['eliOpeningAmount'] = $data['eliOpeningAmount'] != null ? round($data['eliOpeningAmount'], 2) : 0.00;
                // $data['accountTranscation'] = Daybook::where('account_no',$data['accountDetail']->account_number)->where(function($q) { $q->whereIN('transaction_type', [2,4,15,16,17,18,20,21,22,23,24,25,29,30]); } )->whereBetween(\DB::raw('id'), [$start, $end])->where('is_deleted',0)->orderBy(\DB::raw('date(created_at)'),'asc')->orderBy('id','asc')->get();
                $data['accountTranscation'] = Daybook::selectRaw('*, (
                        SELECT SUM(IF(deposit > 0, deposit, -withdrawal))
                        FROM day_books AS sub
                        WHERE sub.account_no = day_books.account_no
                        AND (sub.created_at) <= (day_books.created_at)
                        AND sub.transaction_type IN (2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 29, 30)
                        AND sub.is_deleted = 0
                    ) AS opening_balance')
                    ->whereBetween(\DB::raw('id'), [$start, $end])
                    ->where('account_no', $data['accountDetail']->account_number)
                    ->whereIn('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 29, 30])
                    ->where('is_deleted', 0)
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();
            }
        } catch (\Exception $ex) {
            return back()->with('alert', $ex->getMessage());
        }
        return view('templates.admin.investment_management.passbook.transaction_print', $data);
    }
    public function viewTransaction($id)
    {
        $data['title'] = 'Transaction Detail';
        $data['tDetails'] = Daybook::where('id', $id)->first();
        $data['memberDetail'] = \App\Models\MemberCompany::find($data['tDetails']['member_id']);
        $data['dbranch'] = \App\Models\Branch::find($data['tDetails']['branch_id']);
        $data['associateDetail'] = \App\Models\Member::find($data['tDetails']['associate_id']);
        $data['tDetails']['type'] = 1;

        return view('templates.admin.investment_management.passbook.viewtransaction', $data);
    }
    public function viewssbTransaction($id)
    {
        $data['title'] = 'Transaction Detail';
        $data['tDetails'] = SavingAccountTranscation::where('id', $id)->where('is_deleted', 0)->first();
        // print_r($data['tDetails'] );die;
        $mId = SavingAccount::select('member_investments_id', 'member_id', 'account_no')->where('id', $data['tDetails']->saving_account_id)->first();
        $aId = Memberinvestments::select('associate_id')->where('id', $mId->member_investments_id)->first();
        $data['tDetails']['member_id'] = $mId->member_id;
        if ($data['tDetails']->associate_id > 0) {
            $data['tDetails']['associate_id'] = $data['tDetails']->associate_id;
        } else {
            $data['tDetails']['associate_id'] = $aId->associate_id;
        }
        $data['memberDetail'] = \App\Models\MemberCompany::find($data['tDetails']['member_id']);
        $data['associateDetail'] = \App\Models\Member::find($data['tDetails']['associate_id']);
        $data['tDetails']['account_no'] = $mId->account_no;
        $data['tDetails']['type'] = 0;
        return view('templates.admin.investment_management.passbook.viewtransaction', $data);
    }
    public function passbookTransactionNew($id, $code)
    {
        $data['accountTranscation'] = array();
        $data['accountDetail'] = array();
        $data['code'] = $code;
        $data['title'] = 'Investment | Transactions';
        if ($code == 'S') {
            $data['accountDetail'] = SavingAccount::where('member_investments_id', $id)->where('is_deleted', 0)->first();
        } else {
            $data['accountDetail'] = Memberinvestments::with([
                'plan' => function ($q) {
                    $q->withoutGlobalScope(ActiveScope::class);
                }
            ])->where('id', $id)->where('is_deleted', 0)->first();
        }
        return view('templates.admin.investment_management.passbook.new.transaction', $data);
    }
    /**
     * All Investment Transactions List.
     * Route: tran_list
     * Method: get
     * @return  array()  Response
     */
    public function transactionListNew(Request $request)
    {
        if ($request->ajax()) {
            $code = $request->code;
            if ($request->code == 'S') {
                $account_no = SavingAccount::select('id')->where('id', $request->id)->first();
                $id = $account_no->id;
                $data = SavingAccountTransactionView::where('saving_account_id', $id)->orderBy('opening_date', 'DESC')->orderby('id', 'DESC')->get();
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('tranid', function ($row) {
                        $tranid = $row->transaction_id;
                        return $tranid;
                    })
                    ->rawColumns(['tranid'])
                    ->addIndexColumn()
                    ->addColumn('trans_date', function ($row) {
                        if (isset($row->transaction_default_date)) {
                            $tranid = date("d/m/Y H:i:s", strtotime($row->transaction_default_date));
                        } else {
                            $tranid = 'N/A';
                        }
                        return $tranid;
                    })
                    ->addIndexColumn()
                    ->addColumn('tran_by', function ($row) {
                        $tran_by = $row->transaction_by;
                        return $tran_by;
                    })
                    ->rawColumns(['tran_by'])
                    ->rawColumns(['trans_date'])
                    ->addColumn('date', function ($row) {
                        $date = date("d/m/Y", strtotime($row->opening_date));
                        return $date;
                    })
                    ->rawColumns(['date'])
                    ->addColumn('description', function ($row) {
                        $description = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
                        return $description;
                    })
                    ->rawColumns(['description'])
                    ->addColumn('reference_no', function ($row) use ($code) {
                        $reference_no = $row->reference_no;
                        return $reference_no;
                    })
                    ->rawColumns(['reference_no'])
                    ->addColumn('withdrawal', function ($row) {
                        $withdrawal = $row->withdrawal;
                        return $withdrawal;
                    })
                    ->rawColumns(['withdrawal'])
                    ->addColumn('deposit', function ($row) {
                        $deposit = $row->deposit;
                        return $deposit;
                    })
                    ->rawColumns(['deposit'])
                    ->addColumn('opening_balance', function ($row) {
                        $opening_balance = $row->opening_balance;
                        return $opening_balance;
                    })
                    ->rawColumns(['opening_balance'])
                    ->addColumn('action', function ($row) {
                        $btn = '';
                        $url = URL::to("admin/investment/passbook/ssbtransaction/" . $row->id . "");
                        $url2 = URL::to("admin/investment/renew/ssbtransaction/receipt/" . $row->id . "");
                        $btn .= '<a class="dropdown-item" style="color:2196F3;" href="' . $url . '" title="Transaction Detail"><i class="icon-eye  mr-2"></i></a>  ';
                        // if($row->payment_type == 'CR')
                        // {
                        $btn .= '<a class="dropdown-item" style="color:#2196F3;" href="' . $url2 . '" title="Receipt"><i class="fa fa-file  mr-2"></i></a>  ';
                        // }
                        /*$btn = '<a class="dropdown-item" href="javascript:void(0);" title="Member Detail"><i class="icon-eye  mr-2"></i>View</a>  ';*/
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            } else {
                $accountDetail = Memberinvestments::with('plan')
                    ->whereHas('plan', function ($query) {
                        $query->where('plan_category_code', '!=', 'S');
                    })
                    ->where('id', $request->id)
                    ->first();
                // $data = Daybook::where('account_no', $accountDetail->account_number)
                //     // ->whereIn('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 29, 30])
                //     ->where('transaction_type', '<>',19)
                //     ->where('is_deleted', 0)
                //     ->orderByDesc('created_at')
                //     ->orderByDesc('id')
                //     ->get();
                $data = Daybook::selectRaw('*, (
                    SELECT SUM(IF(deposit > 0, deposit, -withdrawal))
                    FROM day_books AS sub
                    WHERE sub.account_no = day_books.account_no
                    AND (sub.created_at) <= (day_books.created_at)
                    AND sub.transaction_type IN (2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 29, 30)
                    AND sub.is_deleted = 0
                ) AS opening_balance')
                    ->where('account_no', $accountDetail->account_number)
                    ->whereIn('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 29, 30])
                    ->where('is_deleted', 0)
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->get();
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('tranid', function ($row) {
                        $tranid = $row->id;
                        return $tranid;
                    })
                    ->rawColumns(['tranid'])
                    ->addColumn('date', function ($row) {
                        $date = date("d/m/Y", strtotime($row->created_at));
                        return $date;
                    })
                    ->rawColumns(['date'])
                    ->addIndexColumn()
                    ->addColumn('tran_by', function ($row) {
                        $tran_by = (($row->is_app == 1) ? 'Associcate' : (($row->is_app == 2) ? 'E-Passbook' : 'Software'));
                        return $tran_by;
                    })
                    ->rawColumns(['tran_by'])
                    ->addIndexColumn()
                    ->addColumn('trans_date', function ($row) {
                        if (isset($row->created_at_default)) {
                            $tranid = date("d/m/Y H:i:s", strtotime($row->created_at_default));
                        } else {
                            $tranid = 'N/A';
                        }
                        return $tranid;
                    })
                    ->rawColumns(['trans_date'])
                    ->addColumn('description', function ($row) {
                        $description = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
                        return $description;
                    })
                    ->rawColumns(['description'])
                    ->addColumn('reference_no', function ($row) use ($code) {
                        $reference_no = '';
                        if ($row->payment_mode == 1) {
                            $reference_no = $row->cheque_dd_no;
                        }
                        if ($row->payment_mode == 4 || $row->payment_mode == 5) {
                            $reference_no = $row->reference_no;
                        }
                        if ($row->payment_mode == 3) {
                            $reference_no = $row->online_payment_id;
                        }
                        return $reference_no;
                    })
                    ->rawColumns(['reference_no'])
                    ->addColumn('withdrawal', function ($row) {
                        if ($row->withdrawal > 0) {
                            $withdrawal = $row->withdrawal;
                        } else {
                            $withdrawal = '';
                        }
                        return $withdrawal;
                    })
                    ->rawColumns(['withdrawal'])
                    ->addColumn('deposit', function ($row) {
                        if ($row->deposit > 0) {
                            $deposit = $row->deposit;
                        } else {
                            $deposit = '';
                        }
                        return $deposit;
                    })
                    ->rawColumns(['deposit'])
                    ->addColumn('opening_balance', function ($row) {
                        $opening_balance = $row->opening_balance;
                        return $opening_balance;
                    })
                    ->rawColumns(['opening_balance'])
                    ->addColumn('action', function ($row) {
                        $btn = '';
                        $url = URL::to("admin/investment/passbook/transaction/" . $row->id . "");
                        $btn .= '<a class="dropdown-item" style="color:#2196F3;" href="' . $url . '" title="Transaction Detail"><i class="icon-eye  mr-2"></i></a>  ';
                        $url2 = URL::to("admin/investment/renew/receipt/" . $row->id . "");
                        // Sachin g ne remove krne bola h date = 21-jan-2022
                        // if($row->payment_type == 'CR')
                        // {
                        if($row->transaction_type != 18){
                            $btn .= '<a class="dropdown-item" style="color:#2196F3;" href="' . $url2 . '" title="Receipt"><i class="fa fa-file  mr-2"></i></a>  ';
                        }
                        /*$btn = '<a class="dropdown-item" href="javascript:void(0);" title="Member Detail"><i class="icon-eye  mr-2"></i>View</a>  ';*/
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
        }
    }
    public function transactionStartNew(Request $request)
    {
        $data['title'] = 'Passbook | Transaction';
        $data['accountTranscation'] = array();
        $data['accountDetail'] = array();
        $data['code'] = $code = $request['code'];
        $start = $request['transaction_id_from'];
        $end = $request['transaction_id_to'];
        if (Request1::isMethod('get')) {
            return back()->with('alert', 'Please submit form');
        }
        $data['eliOpeningAmount'] = 0.00;
        $data['accountsNumber'] = array('R-066523000256', 'R-084511001514', 'R-066523000257', 'R-066523000620', 'R-084523000800', 'R-066523000510', 'R-066523000258', 'R-084504006762', 'R-066504006287', 'R-066523000509', 'R-066523000588', 'R-084504007930');
        try {
            if ($code == 'S') {
                $data['accountDetail'] = SavingAccount::where('id', $request['id'])->first();
                // $data['accountTranscation'] = SavingAccountTranscation::where('saving_account_id',$request['id'])->whereBetween(\DB::raw('id'), [$start, $end])->orderby('created_at','ASC')->get();
                $data['accountTranscation'] = SavingAccountTransactionView::where('saving_account_id', $request['id'])->whereBetween(\DB::raw('id'), [$start, $end])->orderby('opening_date', 'ASC')->get();
            } else {
                $data['accountDetail'] = Memberinvestments::with('plan')->whereHas('plan', function ($query) {
                    $query->where('plan_category_code', '!=', 'S');
                })->where('id', $request['id'])->first();
                $data['eliOpeningAmount'] = Daybook::where('investment_id', $request['id'])->whereIn('account_no', $data['accountsNumber'])->where('is_eli', 1)->where(function ($q) {
                    $q->where('transaction_type', 2)->where('is_deleted', 0)->orWhere('transaction_type', 4); })->pluck('amount')->first();
                $data['eliOpeningAmount'] = ($data['eliOpeningAmount'] != null) ? round($data['eliOpeningAmount'], 2) : 0.00;
                // $data['accountTranscation'] = Daybook::where('account_no',$data['accountDetail']->account_number)->where(function($q) { $q->whereIN('transaction_type', [2,4,15,16,17,18,20,21,22,23,24,25,29,30]); } )->whereBetween(\DB::raw('id'), [$start, $end])->where('is_deleted',0)->orderBy(\DB::raw('date(created_at)'),'asc')->orderBy('id','asc')->get();
                $data['accountTranscation'] = Daybook::selectRaw('*, (
                        SELECT SUM(IF(deposit > 0, deposit, -withdrawal))
                        FROM day_books AS sub
                        WHERE sub.account_no = day_books.account_no
                        AND (sub.created_at) <= (day_books.created_at)
                        AND sub.transaction_type IN (2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 29, 30)
                        AND sub.is_deleted = 0
                    ) AS opening_balance')
                    ->where('account_no', $data['accountDetail']->account_number)
                    ->whereBetween(\DB::raw('id'), [$start, $end])
                    ->whereIn('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 29, 30])
                    ->where('is_deleted', 0)
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();
            }
        } catch (\Exception $ex) {
            return back()->with('alert', $ex->getMessage());
        }
        return view('templates.admin.investment_management.passbook.new.transaction_print', $data);
    }
    public function passbookCover($id)
    {
        $data['title'] = 'Passbook | Cover';
        $data['passbook'] = Memberinvestments::with(['ssb_detail' => function ($query) {
            $query->select('id', 'passbook_no', 'member_investments_id', 'account_no'); }])->with(['branch' => function ($query) {
                $query->select('id', 'name'); }])->with(['member' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'member_id', 'father_husband', 'address', 'state_id', 'city_id', 'pin_code', 'district_id', 'village'); }])->with(['plan' => function ($query) {
                $query->select('id', 'name', 'plan_code'); }])->where('id', $id)->first(['id', 'account_number', 'tenure', 'deposite_amount', 'member_id', 'branch_id', 'plan_id', 'is_passbook_print', 'passbook_no', 'daughter_name', 'dob']);
        //print_r($data['passbook']);die;
        $data['correctionStatus'] = getCorrectionStatus(5, $id);
        $data['id'] = $id;
        return view('templates.admin.investment_management.passbook.cover', $data);
    }
    public function passbookMaturity($id)
    {
        $data['title'] = 'Passbook | Maturity';
        $data['passbook'] = Memberinvestments::with(['plan' => function ($query) {
            $query->select('id', 'name', 'plan_code'); }])->where('id', $id)->first(['id', 'account_number', 'tenure', 'deposite_amount', 'member_id', 'branch_id', 'plan_id', 'is_passbook_print', 'passbook_no', 'interest_rate']);
        $data['maturity'] = \App\Models\DemandAdvice::where('investment_id', $id)->first(['id', 'investment_id', 'maturity_amount_payable', 'date', 'is_mature', 'voucher_number']);
        //print_r($data['passbook']);die;
        return view('templates.admin.investment_management.passbook.new.maturity', $data);
    }
    public function passbookCoverNew($id)
    {
        $data['title'] = 'Passbook | Cover';
        $data['passbook'] = Memberinvestments::with(['ssb_detail' => function ($query) {
            $query->select('id', 'passbook_no', 'member_investments_id', 'account_no'); }])->with(['branch' => function ($query) {
                $query->select('id', 'name'); }])->with(['member' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'member_id', 'father_husband', 'address', 'state_id', 'city_id', 'pin_code', 'district_id', 'village'); }])->with(['plan' => function ($query) {
                $query->select('id', 'name', 'plan_code'); }])->where('id', $id)->first(['id', 'account_number', 'tenure', 'deposite_amount', 'member_id', 'branch_id', 'plan_id', 'is_passbook_print', 'passbook_no', 'daughter_name', 'dob']);
        //print_r($data['passbook']);die;
        return view('templates.admin.investment_management.passbook.new.cover', $data);
    }
}
