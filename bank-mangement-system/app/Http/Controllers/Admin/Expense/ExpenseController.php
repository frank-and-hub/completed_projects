<?php
namespace App\Http\Controllers\Admin\Expense;
use App\Http\Controllers\Admin\CommanController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountHeads;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\Files;
use App\Models\BillExpense;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use URL;
use DB;

use App\Services\ImageUpload;
use Session;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Demand Advice DemandAdviceController
    |--------------------------------------------------------------------------
    |
    | This controller handles demand advice all functionlity.
*/
class ExpenseController extends Controller
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
     * Display a listing of the account heads.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (check_my_permission(Auth::user()->id, "169") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Expense |  Expense Booking Form';
        // $data['branches'] = Branch::select('id', 'name')->where('status', 1)->get();
        $data['account_heads'] = AccountHeads::select('id', 'head_id', 'sub_head','company_id')->where('parent_id', 86)->get();
        $data['bank'] = \App\Models\SamraddhBank::select('id', 'bank_name')->where('status', 1)->get();
        return view('templates.admin.expense.add_expense', $data);
    }
    public function get_indirect_expense()
    {
        $account_heads = AccountHeads::where('parent_id', 86)->get();
        return response()->json($account_heads);
    }

    public function get_indirect_expense_sub_head(Request $request)
    {
        if (isset($request->jvVoucher)) {
            $myarray = [1 => 8, 8 => 20, 20 => 403,4 => 86];
            $companyId = $request->company_id;
            if (array_key_exists($request->head_id, $myarray)) {
                $account_heads = AccountHeads::where('head_id', $myarray[$request->head_id])
                    ->where(function ($query) use ($companyId) {
                        $query->when($companyId!='0',function($q) use ($companyId){ $q->where('company_id', 'like', '%'.$companyId.'%'); });
                    })
                    ->where('status', 0)
                    ->where('entry_everywhere', 1)
                    ->where('sub_head', 'not like', '%Accrued%')
                    ->get();
                $return_array = compact('account_heads');
            } else {
                $account_heads = AccountHeads::where('parent_id', $request->head_id)
                    ->where(function ($query) use ($companyId) {
                        $query->when($companyId!='0',function($q) use ($companyId){ $q->where('company_id', 'like', '%'.$companyId.'%'); });
                    })
                    ->where('status', 0)
                    ->where('entry_everywhere', 1)
                    ->where('sub_head','not like', '%Accrued%')
                    ->get();
                $return_array = compact('account_heads');
            }
        } else {
            $account_heads = AccountHeads::where('parent_id', $request->head_id)->where('status', 0);
            if (isset($request->company_id) && ($request->company_id != '0')) {
                $account_heads = $account_heads->where('company_id','like','%'.$request->company_id.'%');
            }
            $account_heads = $account_heads->get();
            $return_array = compact('account_heads');
        }
        return json_encode($return_array);
    }
    public function save(Request $request)
    {
        $rules = [
            'account_head' => ['required'],
            'branch_id' => ['required'],
            'company_id' => ['required'],
            'amount' => ['required'],
        ];
        $customMessages = [
            'required' => ':Attribute  is required.',
            'unique' => ' :Attribute already exists.'
        ];
        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {
            $entry_date = date("Y-m-d", strtotime(convertDate($request->created_at)));
            $entry_time = date("H:i:s", strtotime(convertDate($request->created_at)));
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($request->created_at)));
            $created_time = date("His", strtotime(convertDate($request->created_at)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($request->created_at)));
            Session::put('created_at', $created_at);
            $billNo = $created_time . random_int(0, 5000);
            $billNumber = $billNo;
            $bill['bill_no'] = $billNumber;
            $bill['payment_mode'] = $request->payment_mode;
            $bill['bank_id'] = $request->bank_id;
            $bill['account_id'] = $request->account_id;
            $bill['bank_balance'] =  $request->bank_balance;
            $bill['branch_balance'] = $request->branch_total_balance;
            $bill['cheque_id'] = $request->cheque_id;
            $bill['utr_no'] = $request->utr_no;
            $bill['neft_charge'] = $request->neft_charge;
            $bill['party_name'] = $request->party_name;
            $bill['party_bank_name'] = $request->party_bank_name;
            $bill['party_bank_ac_no'] = $request->party_bank_ac_no;
            $bill['party_bank_ifsc'] = $request->party_bank_ifsc;
            $bill['branch_id'] = $request->branch_id;
            $bill['company_id'] = $request->company_id;
            $bill['bill_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['bill_date'])));;
            $billDetail = \App\Models\BillExpense::create($bill);
            $data['account_head_id'] = $request->account_head;
            $data['sub_head1'] = $request->sub_head1;
            $data['sub_head2'] = $request->sub_head2;
            $data['particular'] = $request->particular;
            $data['payment_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['bill_date'])));;
            $data['bill_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['bill_date'])));;
            $data['amount'] = $request->amount;
            $data['bill_no'] = $billDetail->bill_no;
            $data['status'] = 0;
            $data['created_at'] = $request->created_at;
            $data['updated_at'] = $request->created_at;
            $data['company_id'] = $request->company_id;
            $expense_res = Expense::create($data);
            $expenseId = $expense_res->id;
            if ($request->hasFile('receipt')) {
                $mainFolder = 'expense';
                // $mainFolder = storage_path() . '/images/expense';
                $file = $request->receipt;
                $uploadFile = $file->getClientOriginalName();
                $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                ImageUpload::upload($file,$mainFolder,$fname);
                $fData = [
                    'file_name' => $fname,
                    'file_path' => $mainFolder,
                    'file_extension' => $file->getClientOriginalExtension(),
                ];
                $expenseUpdate = Expense::find($expenseId);
                $expenseUpdate->receipt = $fname;
                $expenseUpdate->save();
                Files::create($fData);
            }
            if (isset($_POST['particular_more'])) {
                foreach (($_POST['particular_more']) as $key => $option) {
                    $dataExpenseMore = array();
                    $dataExpenseMore['account_head_id'] = $_POST['account_head_more'][$key];
                    $dataExpenseMore['sub_head1'] = $_POST['sub_head1_more'][$key];
                    if ($_POST['sub_head2_more'][$key] != '') {
                        $dataExpenseMore['sub_head2'] = $_POST['sub_head2_more'][$key];
                    }
                    $dataExpenseMore['particular'] = $_POST['particular_more'][$key];
                    $dataExpenseMore['payment_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['bill_date'])));;
                    $dataExpenseMore['bill_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['bill_date'])));;
                    $dataExpenseMore['amount'] = $_POST['amount_more'][$key];
                    $dataExpenseMore['bill_no'] = $billDetail->bill_no;
                    $dataExpenseMore['created_at'] = $request->created_at;
                    $dataExpenseMore['updated_at'] = $request->created_at;
                    $dataExpenseMore['company_id'] = $request->company_id;
                    $dataExpenseMore['status'] = 0;
                    $expense_res = Expense::create($dataExpenseMore);
                    $expenseIdMore = $expense_res->id;
                    $files = $request->file('receipt_more');
                    if ($request->hasFile('receipt_more')) {
                        // $mainFolder = storage_path() . '/images/expense';
                        $mainFolder = 'expense';
                        $file = $request['receipt_more'][$key];
                        $uploadFile = $file->getClientOriginalName();
                        $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                        $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                        $files = ImageUpload::upload($file,$mainFolder,$fname);
                        // $file->move($mainFolder, $fname);
                        $fData = [
                            'file_name' => $fname,
                            'file_path' => $mainFolder,
                            'file_extension' => $file->getClientOriginalExtension(),
                        ];
                        $expenseUpdate = Expense::find($expenseIdMore);
                        $expenseUpdate->receipt = $fname;
                        $expenseUpdate->save();
                    }
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        expenses_logs($billDetail->bill_no, NULL, "add", Auth::user()->id);
        return redirect()->route('admin.expense')->with('success', 'Expense Created  Successfully');
    }
    public function report_expense($bill_no)
    {
        if (check_my_permission(Auth::user()->id, "169") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Expense Booking | Expense Detail  Report';
        $data['bill_status'] = \App\Models\BillExpense::select('status', 'branch_id', 'created_at', 'bill_no', 'id', 'bill_date')->where('is_deleted', 0)->where('bill_no', $bill_no)->first();
        $data['bill_no'] = $data['bill_status']->bill_no;
        return view('templates.admin.expense.expense_report', $data);
    }
    public function expense_bill()
    {
        if (check_my_permission(Auth::user()->id, "169") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Expense Booking | Bill Report';
        // $data['branch'] = \App\Models\Branch::select('id','name')->where('status',1)->get();
        return view('templates.admin.expense.bill_expense_report', $data);
    }
    public function  expense_report_listing(Request $request)
    {
        $data = Expense::with('branch')->where('bill_no', $request->bill_no)/*->where(DB::raw('DATE(bill_date)'), date('Y-m-d', strtotime($request->created_at)))*/->where('is_deleted', 0)->orderBy('id', 'DESC')->get();
        if ($request->ajax()) {
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('account_head', function ($row) {
                    if ($row->account_head_id) {
                        return getAcountHeadNameHeadId($row->account_head_id);
                    }
                })
                ->rawColumns(['account_head'])
                ->addColumn('sub_head1', function ($row) {
                    if ($row->sub_head1) {
                        return getAcountHeadNameHeadId($row->sub_head1);
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['sub_head1'])
                ->addColumn('sub_head2', function ($row) {
                    if ($row->sub_head2) {
                        return getAcountHeadNameHeadId($row->sub_head2);
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['sub_head2'])
                ->addColumn('particular', function ($row) {
                    return $row->particular;
                })
                ->rawColumns(['particular'])
                ->addColumn('receipt', function ($row) {
                    if ($row->receipt) {
                        //return $row->receipt;
                        // $url = URL::to("/core/storage/images/expense/" . $row->receipt . "");
                        $url = ImageUpload::generatePreSignedUrl('expense/' . $row->receipt);
                        return '<a href="' . $url . '" target="blank">' . $row->receipt . '</a>';
                    } else {
                        return 'N/A';
                    }
                })
                ->escapeColumns(['particular'])
                ->addColumn('amount', function ($row) {
                    return $row->amount;
                })
                ->rawColumns(['amount'])
                ->addColumn('bill_date', function ($row) {
                    return date("d/m/Y", strtotime($row->bill_date));
                })
                ->rawColumns(['bill_date'])
                ->addColumn('payment_date', function ($row) {
                    $date = 'N/A';
                    if ($row->approve_date) {
                        $date =  date("d/m/Y", strtotime($row->approve_date));
                    }
                    return $date;
                })
                ->rawColumns(['payment_date'])
                ->make(true);
        }
    }
    public function bill_expense_report_listing(Request $request)
    {
        if ($request->ajax()) {
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = \App\Models\BillExpense::has('companyName')->select('id', 'created_at', 'bill_date', 'bill_no', 'party_name', 'party_bank_name', 'party_bank_ac_no', 'party_bank_ifsc', 'payment_mode', 'utr_no', 'neft_charge', 'status', 'branch_id', 'cheque_id', 'company_id')
                    ->with('companyName:id,name')
                    ->with(['getBranchCustom' => function ($q) {
                        $q->select('id', 'branch_code', 'name');
                    }])
                    ->with(['getChequeCustom' => function ($q) {
                        $q->select('id', 'cheque_no');
                    }])
                    ->with(['expenses' => function ($q) {
                        $q->select('id', 'account_head_id', 'sub_head1', 'sub_head2');
                    }])->where('is_deleted', 0);
                if ($arrFormData['is_search'] == 'yes') {
                    // if ($arrFormData['start_date'] != '') {
                    //     $startDate = date('Y-m-d', strtotime(convertDate($arrFormData['start_date'])));
                    //     $data = $data->where('created_at' >= $startDate);
                    //     if ($arrFormData['end_date'] != '') {
                    //         $endDate = date('Y-m-d', strtotime(convertDate($arrFormData['end_date'])));
                    //     } else {
                    //         $endDate = '';
                    //     }
                    //     $data = $data->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                    // }
                    if ($arrFormData['start_date'] != '') {
                        $startDate = date('Y-m-d', strtotime(convertDate($arrFormData['start_date'])));
                        $data = $data->whereDate('created_at', '>=', $startDate);
                    }
                    
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date('Y-m-d', strtotime(convertDate($arrFormData['end_date'])));
                        $data = $data->whereDate('created_at', '<=', $endDate);
                    }
                    if ($arrFormData['company_id'] != '') {
                        if ($arrFormData['company_id'] > 0) {
                            $data = $data->where('company_id', $arrFormData['company_id']);
                        }
                    }
                    if ($arrFormData['branch_id'] != '') {
                        if ($arrFormData['branch_id'] > 0) {
                            $data = $data->where('branch_id', $arrFormData['branch_id']);
                        }
                    }
                    if ($arrFormData['party_name'] != '') {
                        $data = $data->where('party_name', 'like', '%' . $arrFormData["party_name"] . '%');
                    }
                    if ($arrFormData['status'] != '') {
                        $data = $data->where('status', $arrFormData['status']);
                    }
                }
                $count = $data->count('id');
                $data = $data->offset($_POST['start'])->limit($_POST['length'])->orderBy('created_at', 'DESC')->get();
                $totalCount  = $count;
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $val['company_name'] = isset($row['companyName']->name) ? $row['companyName']->name : "N/A";
                    $val['branch_name'] = isset($row['getBranchCustom']->name) ? $row['getBranchCustom']->name : "N/A";
                    $val['branch_code'] = isset($row['getBranchCustom']->branch_code) ? $row['getBranchCustom']->branch_code : "N/A";
                    $val['created_at'] = date('d/m/Y', strtotime($row->created_at));
                    $val['bill_date'] = isset($row->bill_date) ? date('d/m/Y', strtotime($row->bill_date)) : 'N/A';
                    $val['bill_no'] = $row->bill_no;
                    $val['party_name'] = $row->party_name;
                    $val['party_bank_name'] = $row->party_bank_name ?? 'N/A';
                    $val['party_bank_ac_no'] = $row->party_bank_ac_no ?? 'N/A';
                    $val['party_bank_ifsc'] = $row->party_bank_ifsc ?? 'N/A';
                    switch ($row->payment_mode) {
                        case '0':
                            $paymentMode = 'CASH';
                            break;
                        case '1':
                            $paymentMode = 'CHEQUE';
                            break;
                        case '2':
                            $paymentMode = 'ONLINE';
                            break;
                        default:
                            $paymentMode = 'N/A';
                            break;
                    }
                    $val['payment_mode'] = $paymentMode;
                    switch ($row->payment_mode) {
                        case '1':
                            $cheque = $row['getChequeCustom']; //\App\Models\SamraddhCheque::select('cheque_no')->where('id',$row->cheque_id)->first();
                            $cheque = $cheque->cheque_no;
                            break;
                        default:
                            $cheque = 'N/A';
                            break;
                    }
                    $val['cheque_no'] =  $cheque;
                    switch ($row->payment_mode) {
                        case '2':
                            $utr = $row->utr_no;
                            break;
                        default:
                            $utr = 'N/A';
                            break;
                    }
                    $val['utr_no'] = $utr;
                    switch ($row->payment_mode) {
                        case '2':
                            $neft_charge = $row->neft_charge;
                            break;
                        default:
                            $neft_charge = 'N/A';
                            break;
                    }
                    $val['neft_charge'] = $neft_charge;
                    $mainHead = ' ';
                    $subHead = ' ';
                    $subHead2 = '';
                    if ($row['expenses']) {
                        if ($row['expenses']->account_head_id) {
                            $mainHead = getAcountHeadNameHeadId($row['expenses']->account_head_id);
                            $des = $mainHead;
                        }
                        if ($row['expenses']->sub_head1) {
                            $subHead = getAcountHeadNameHeadId($row['expenses']->sub_head1);
                            $des = $mainHead . '/' . $subHead;
                        }
                        if ($row['expenses']->sub_head2) {
                            $subHead2 = getAcountHeadNameHeadId($row['expenses']->sub_head2);
                            $des = $mainHead . '/' . $subHead . '/' . $subHead2;
                        }
                    }
                    $val['account_head'] = $mainHead . (($subHead  != '') ? '' : '/' . $subHead . (($subHead2 != '') ? '' : '/' . $subHead2));
                    $date = date('Y-m-d', strtotime($row->bill_date));
                    $amount = \App\Models\Expense::where('bill_no', $row->bill_no)->where(DB::raw('DATE(bill_date)'), $date)->sum('amount');
                    $val['amount'] = $amount;
                    $totalExpense = \App\Models\Expense::where('bill_no', $row->bill_no)->count('id');
                    $val['total_expense'] = $totalExpense;
                    switch ($row->status) {
                            // case 0 : $status = "<span class='badge bg-warning'>Pending</span>"; break;
                            // case 1 : $status = "<span class='badge bg-success'>Approved</span>"; break;
                            // case 2 : $status = "<span class='badge bg-danger'>Deleted</span>"; break;
                            // default : $status = "N/A";
                        case 0:
                            $status = "Pending";
                            break;
                        case 1:
                            $status = "Approved";
                            break;
                        case 2:
                            $status = "Deleted";
                            break;
                        default:
                            $status = "N/A";
                    }
                    $val['status'] = $status;
                    $detailExpenseurl =  URL::to("admin/report/expense/" . $row->bill_no . "");
                    $print_url =  URL::to("admin/report/expense_pr/" . $row->id . "");
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    if ($row->status == 0) {
                        $btn .= '<a href="admin/expense/edit/' . $row->id . '" class="dropdown-item" title="Edit Expense" target="_blank"><i class="icon-pencil5 mr-2"></i> Edit</a>';
                        $btn .= '<button class="dropdown-item delete_expense" data-row-id="' . $row->bill_no . '" title="Delete Expense"><i class="icon-box mr-2"></i> Delete</button>';
                    }
                    if ($row->status == 1) {
                        $btn .= '<button class="dropdown-item delete_expense" data-row-id="' . $row->bill_no . '" title="Delete Approved Bill"><i class="icon-box mr-2"></i> Delete Approved Bill</button>';
                    }
                    if (check_my_permission(Auth::user()->id, "264") == "1") {
                        if ($row->status == 1) {
                            $btn .= '<a class="dropdown-item"  target="_blank" href="' . $print_url . '" title="Print Detail Expense" ><i class="icon-printer  mr-2"></i> Print</button>';
                        }
                    }
                    $btn .= '<a class="dropdown-item"  href="' . $detailExpenseurl . '" title="Detail Expense" ><i class="icon-snowflake mr-2"></i> Detail Expense</button>';
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
    public function report_expense_print($bill_no)
    {
        if (check_my_permission(Auth::user()->id, "264") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Print Expense Booking | Print Expense Detail  Report';
        $data['bill_status'] = \App\Models\BillExpense::select('bill_date', 'status', 'branch_id', 'created_at', 'bill_no', 'party_name', 'id', 'company_id')->where('is_deleted', 0)->where('id', $bill_no)
            ->with('companyName:id,name,mobile_no,email,address')->first();
        $data['bill_no'] = $data['bill_status']->bill_no;
        $data['created_at'] = $data['bill_status']->created_at;
        $data['party_name'] = $data['bill_status']->party_name;
        $data['branch_id'] = $data['bill_status']->branch_id;
        $data['print_data'] = Expense::with('branch')->where('bill_no', $data['bill_status']->bill_no)->where(DB::raw('DATE(created_at)'), date('Y-m-d', strtotime($data['bill_status']->created_at)))->where('is_deleted', 0)->orderBy('id', 'DESC')->get();
        $data['total_amount'] = $data['print_data']->sum('amount');
        $sno = 1;
        $rowReturn = array();
        foreach ($data['print_data'] as $row) {
            $sno++;
            $val['DT_RowIndex'] = $sno;
            if ($row->account_head_id) {
                $val['account_head'] = getAcountHeadNameHeadId($row->account_head_id);
            }
            if ($row->sub_head1) {
                $val['sub_head1'] = getAcountHeadNameHeadId($row->sub_head1);
            } else {
                $val['sub_head1'] = 'N/A';
            }
            if ($row->sub_head2) {
                $val['sub_head2'] = getAcountHeadNameHeadId($row->sub_head2);
            } else {
                $val['sub_head2'] = 'N/A';
            }
            $val['particular'] = $row->particular;
            $val['amount'] = $row->amount;
            $val['bill_date'] = date("d/m/Y", strtotime($row->bill_date));
            $date = 'N/A';
            if ($row->approve_date) {
                $date =  date("d/m/Y", strtotime($row->approve_date));
            }
            $val['payment_date'] = $date;
            $rowReturn[] = $val;
        }
        $data['pr_data'] = $rowReturn;
        return view('templates.admin.expense.expense_report_print', $data);
    }
    public function approve_expense(Request $request)
    {
        $data2 = BillExpense::with('expenses')->where('bill_no', $request->bill_no)->first();
        $companyId = $data2->company_id;
        $data = Expense::where('bill_no', $request->bill_no)->where('bill_date', $data2->bill_date)->get();
        $amount = Expense::where('bill_no', $request->bill_no)->where('bill_date', $data2->bill_date)->sum('amount');
        DB::beginTransaction();
        try {
            $daybookRef = CommanController::createBranchDayBookReferenceNew($amount, $data2->created_at);
            $response["status"] = '';
            $response["msg"] = '';
            foreach ($data as $key => $value) {
                // $bank_id = $data2->bank_id != ''  ? $data2->bank_id :NULL;
                // $bank_id_ac = $data2->bank_id != '' ? $data2->bank_id :NULL;
                $des = $value->particular;
                $created_by = 1;
                $created_by_id = \Auth::user()->id;
                $created_by_name = \Auth::user()->username;
                $ExpenseheadId = ''; //Expense Head Id
                $headId = ''; // Head Id based on Payment Mode
                $neftHead = '';
                $branch_id = $data2->branch_id;
                $type = 20;
                $sub_type = 201;
                $type_id = $value->bill_no;
                $type_transaction_id = $value->id;
                $opening_balance = $value->amount;
                $closing_balance = $value->amount;
                $amount = $value->amount;
                $payment_mode = $data2->payment_mode;
                $currency_code = 'INR';
                $amount_to_id = NULL;
                $amount_to_name = NULL;
                $amount_from_id = NULL;
                $amount_from_name = NULL;
                $v_no = NULL;
                $v_date = NULL;
                $ssb_account_id_from = NULL;
                if ($data2->cheque_id) {
                    $cheque = \App\Models\SamraddhCheque::select('cheque_no')->where('id', $data2->cheque_id)->first();
                    $bank_id = $data2->bank_id;
                    $bank_ac_id = $data2->bank_id;
                    $cheque_no = $cheque->cheque_no;
                    $cheque_type = 1;
                    $cheque_id = $data2->cheque_id;
                    $transction_no = NULL;
                } else {
                    $cheque_no = NULL;
                    $cheque_type = NULL;
                    $cheque_id = NULL;
                    $bank_id = $data2->bank_id;
                    $bank_ac_id = $data2->account_id ?? NULL;
                    $transction_no = $data2->utr_no ?? NULL;
                }
                $cheque_date = NULL;
                $cheque_bank_from = NULL;
                $cheque_bank_ac_from = NULL;
                $cheque_bank_ifsc_from = NULL;
                $cheque_bank_branch_from = NULL;
                $cheque_bank_to = NULL;
                $cheque_bank_ac_to = NULL;
                $transction_bank_from = NULL;
                $transction_bank_ac_from = NULL;
                $transction_bank_ifsc_from = NULL;
                $transction_bank_branch_from = NULL;
                $transction_bank_to = NULL;
                $transction_bank_ac_to = NULL;
                $transction_date = NULL;
                $tranId = NULL;
                $ssb_account_id_to = NULL;
                $cheque_bank_from_id = NULL;
                $cheque_bank_ac_from_id = NULL;
                $cheque_bank_to_name = NULL;
                $cheque_bank_to_branch = NULL;
                $cheque_bank_to_ac_no = NULL;
                $cheque_bank_to_ifsc = NULL;
                $transction_bank_from_id = NULL;
                $transction_bank_from_ac_id = NULL;
                $transction_bank_to_name = NULL;
                $transction_bank_to_ac_no = NULL;
                $transction_bank_to_branch = NULL;
                $transction_bank_to_ifsc = NULL;
                $branch_id_to = $value->branch_id;
                $branch_id_from = NULL;
                $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = NULL;
                $head3 = NULL;
                $head4 = NULL;
                $head5 = NULL;
                if ($value->account_head_id != '' && $value->sub_head1 != '' && $value->sub_head2 != '') {
                    $head5 = $value->sub_head2;
                    $ExpenseheadId = $value->sub_head2;
                } elseif ($value->account_head_id != '' && $value->sub_head1 != '' && $value->sub_head2 == '') {
                    $ExpenseheadId = $value->sub_head1;
                    $head4 = $value->sub_head1;
                } elseif ($value->account_head_id != '' && $value->sub_head1 == '' && $value->sub_head2 == '') {
                    $ExpenseheadId = $value->account_head_id;
                    $head3 = $value->account_head_id;
                }
                // Payment Mode
                if ($data2->payment_mode == 0) {
                    $headId = 28;
                } elseif ($data2->payment_mode == 1 || $data2->payment_mode == 2) {
                    $headId = getSamraddhBank($data2->bank_id)->account_head_id;
                }
                // If NEFT Charge Exist
                $headName = getAcountHeadNameHeadId(86);
                if ($head3 > 0) {
                    //  $des.= ','.getAcountHeadNameHeadId($head3);
                    $headName .= '/' . getAcountHeadNameHeadId($head3);
                }
                if ($head4 > 0) {
                    // $des.= ','.getAcountHeadNameHeadId($head4);
                    $headName .= '/' . getAcountHeadNameHeadId($head4);
                }
                if ($head5 > 0) {
                    //  $des.= ','.getAcountHeadNameHeadId($head5);
                    $headName .= '/' . getAcountHeadNameHeadId($head5);
                }
                $entry_date = date("Y-m-d", strtotime(convertDate($value->bill_date)));
                $entry_time = date("H:i:s", strtotime(convertDate($value->created_at)));
                $created_at = date("Y-m-d H:i:s", strtotime(convertDate($value->bill_date)));
                $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($value->created_at)));
                $description_dr = 'Cash A/c DR ' . $value->amount . '/-';
                $description_cr = $headName . ' A/c CR ' . $value->amount . '/-';
                // Branch Daybook Entry
                $brDaybook = CommanController::branchDaybookCreateModified($daybookRef, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = Null, $branch_id_to = NULL, $branch_id_from = NULL,  $amount, $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $companyId);

                // AllHeadTransaction Entry
                $allTran1 = CommanController::newHeadTransactionCreate($daybookRef, $branch_id, $bank_id, $bank_ac_id, $ExpenseheadId, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from,  $amount, $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

                $allTran1 = CommanController::newHeadTransactionCreate($daybookRef, $branch_id, $bank_id, $bank_ac_id, $headId, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $des, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

                if ($data2->payment_mode == 1 || $data2->payment_mode == 2) {
                    $bankcashDaybook = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($daybookRef, $bank_id, $bank_ac_id, $type, $sub_type, $type_id, $associate_id = NULL, $memberId = NULL, $branch_id, $opening_balance, $amount, $closing_balance, $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);
                    if ($data2->payment_mode == 1) {
                        $update = \App\Models\SamraddhCheque::where('id', $data2->cheque_id)->update(['is_use' => 1]);
                    }
                }
                $approvedDate =  date('Y-m-d H:i:s');
                $updateDaybookId = $data2->update(['daybook_refid' => $daybookRef, 'status' => 1]);
                $value->update(['status' => 1, 'approve_date' => $approvedDate]);
                $response["status"] = "1";
                $response["msg"] = "Expense Approved Successfully!";
            }
            //Neft Charge
            if (!is_null($data2->neft_charge) && $data2->neft_charge > 0) {
                $des = 'NEFT CHARGE on Bill' . $data2->bill_no;
                $created_by = 1;
                $created_by_id = \Auth::user()->id;
                $created_by_name = \Auth::user()->username;
                $ExpenseheadId = ''; //Expense Head Id
                $headId = ''; // Head Id based on Payment Mode
                $neftHead = 92;
                $headId = getSamraddhBank($data2->bank_id)->account_head_id;
                $branch_id = $data2->branch_id;
                $type = 20;
                $sub_type = 201;
                $type_id = $data2->bill_no;
                $type_transaction_id = $data2->bill_no;
                $opening_balance =  $data2->neft_charge;
                $closing_balance =  $data2->neft_charge;
                $amount =  $data2->neft_charge;
                $payment_mode = $data2->payment_mode;
                $currency_code = 'INR';
                $amount_to_id = NULL;
                $amount_to_name = NULL;
                $amount_from_id = NULL;
                $amount_from_name = NULL;
                $v_no = NULL;
                $v_date = NULL;
                $ssb_account_id_from = NULL;
                if ($data2->cheque_id) {
                    $cheque = \App\Models\SamraddhCheque::select('cheque_no')->where('id', $data2->cheque_id)->first();
                    $bank_id = $data2->bank_id;
                    $bank_ac_id = $data2->bank_id;
                    $cheque_no = $cheque->cheque_no;
                    $cheque_type = 1;
                    $cheque_id = $data2->cheque_id;
                } else {
                    $cheque_no = NULL;
                    $cheque_type = NULL;
                    $cheque_id = NULL;
                    $bank_id = $data2->bank_id;
                    $bank_ac_id = $data2->bank_id;
                }
                $cheque_date = NULL;
                $cheque_bank_from = NULL;
                $cheque_bank_ac_from = NULL;
                $cheque_bank_ifsc_from = NULL;
                $cheque_bank_branch_from = NULL;
                $cheque_bank_to = NULL;
                $cheque_bank_ac_to = NULL;
                if ($data2->utr_no) {
                    $transction_no = $data2->utr_no;
                } else {
                    $transction_no = NULL;
                }
                $transction_bank_from = NULL;
                $transction_bank_ac_from = NULL;
                $transction_bank_ifsc_from = NULL;
                $transction_bank_branch_from = NULL;
                $transction_bank_to = NULL;
                $transction_bank_ac_to = NULL;
                $transction_date = NULL;
                $tranId = NULL;
                $ssb_account_id_to = NULL;
                $cheque_bank_from_id = NULL;
                $cheque_bank_ac_from_id = NULL;
                $cheque_bank_to_name = NULL;
                $cheque_bank_to_branch = NULL;
                $cheque_bank_to_ac_no = NULL;
                $cheque_bank_to_ifsc = NULL;
                $transction_bank_from_id = NULL;
                $transction_bank_from_ac_id = NULL;
                $transction_bank_to_name = NULL;
                $transction_bank_to_ac_no = NULL;
                $transction_bank_to_branch = NULL;
                $transction_bank_to_ifsc = NULL;
                $branch_id_to = $data2['expenses']->branch_id;
                $branch_id_from = NULL;
                $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = NULL;
                $head3 = NULL;
                $head4 = NULL;
                $head5 = NULL;
                // Payment Mode
                $entry_date = date("Y-m-d", strtotime(convertDate($data2->created_at)));
                $entry_time = date("H:i:s", strtotime(convertDate($data2->created_at)));
                $created_at = date("Y-m-d H:i:s", strtotime(convertDate($data2->created_at)));
                $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($data2->created_at)));
                $description_dr = 'Cash A/c DR ' . $amount . '/-';
                $description_cr = $data2->bill_no . ' A/c CR ' . $amount . '/-';
                $brDaybook = CommanController::branchDaybookCreateModified($daybookRef, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = Null, $branch_id_to = NULL, $branch_id_from = NULL, $amount,  $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $companyId);

                // AllHeadTransaction Entry
                $allTran1 = CommanController::newHeadTransactionCreate($daybookRef, $branch_id, $bank_id, $bank_ac_id, $neftHead, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from,  $amount, $des, 'DR', $payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

                $allTran1 = CommanController::newHeadTransactionCreate($daybookRef, $branch_id, $bank_id, $bank_ac_id, $headId, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from,  $amount, $des, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

                $bankcashDaybook = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($daybookRef, $bank_id, $bank_ac_id, $type, $sub_type, $type_id, $associate_id = NULL, $memberId = NULL, $branch_id, $opening_balance, $amount, $closing_balance, $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        expenses_logs($request->bill_no, NULL, "approve", Auth::user()->id);
        echo json_encode($response);
    }
    public function edit($id)
    {
        $title = 'Expense | Edit Expense';
        $branches = Branch::where('status', 1)->get();
        $billExpense = \App\Models\BillExpense::with('expenses')->select('bill_no', 'party_name', 'branch_balance', 'payment_mode', 'cheque_id', 'utr_no', 'neft_charge', 'party_bank_name', 'party_bank_ac_no', 'party_bank_ifsc', 'branch_id', 'bank_balance', 'bank_id', 'account_id', 'created_at', 'bill_date', 'id', 'company_id')->where('id', $id)->where('is_deleted', 0)->first();
        $companyId = $billExpense->company_id;
        $bank = \App\Models\SamraddhBank::where('status', 1)->where('company_id', $companyId)->get();
        $bank_ac = \App\Models\SamraddhBankAccount::where('status', 1)->where('id', $billExpense->account_id)->get();
        $cheques = \App\Models\SamraddhCheque::select('cheque_no', 'id')->where('is_use', 0)->get();
        $account_heads = AccountHeads::where('parent_id', 86)->get();
        $branches = \App\Models\Branch::with(['companybranchsAll' => function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
            $query->select('company_id','branch_id');
        }])
        ->select('id','name','status')->where('status',1)
        ->get();
        $companyData = \App\Models\Companies::select('id','name')->where('id',$companyId)->get();
        $expenseData = \App\Models\Expense::select('id', 'bill_no', 'bill_date', 'particular', 'account_head_id', 'sub_head1', 'sub_head2', 'amount', 'receipt')->where('bill_no', $billExpense->bill_no)/*->where(DB::raw('Date(bill_date)'), date("Y-m-d", strtotime($billExpense->bill_date)))*/->where('is_deleted', 0)->get();
        $data = [
            'title' => 'title',
            'branches' => 'branches',
            'companyData' => 'companyData',
            'account_heads' => 'account_heads',
            'bank' => 'bank',
            'bank_ac' => 'bank_ac',
            'expenseData' => 'expenseData',
            'billExpense' => 'billExpense',
            'cheques' => 'cheques',
        ];
        return view('templates.admin.expense.edit_expense', compact($data));
    }
    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            if ($request->expensesId) {
                //foreach($request->expensesId as $key => $expense)
                $expensesIds = $request->expensesId;
                $particulars = $request->particular;
                $account_head_ids = $request->account_head;
                $subHeads1 = $request->sub_head1;
                $subHeads2 = $request->sub_head2;
                $branchid = $request->branch_id;
                $bill_date = $request->expensesDate;
                $amounts = $request->amount;
                $companyId = $request->company_id;
                for ($i = 0; $i < count($expensesIds); $i++) {
                    if (isset($particulars[$i])  == '') {
                        $ExistExpenseUpdate = Expense::where('id', $expensesIds[$i])->delete();
                    } else {
                        $expensesId = $expensesIds[$i];
                        $particular = $particulars[$i];
                        $account_head_id = $account_head_ids[$i];
                        $subHead1 = $subHeads1[$i];
                        $subHead2 = $subHeads2[$i];
                        $branchid = $branchid;
                        $bill_date = $bill_date;
                        $amount = $amounts[$i];
                        $dataExpenseMore['account_head_id'] = $account_head_id;
                        $dataExpenseMore['sub_head1'] = $subHead1;
                        $dataExpenseMore['sub_head2'] = $subHead2;
                        $dataExpenseMore['particular'] = $particular;
                        $dataExpenseMore['payment_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $bill_date)));
                        $dataExpenseMore['bill_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $bill_date)));
                        $dataExpenseMore['amount'] = $amount;
                        $dataExpenseMore['company_id'] = $companyId;
                        $ExistExpenseUpdate = Expense::where('id', $expensesIds[$i])->update($dataExpenseMore);
                        if (isset($request->receipt[$i])) {
                            // $mainFolder = storage_path() . '/images/expense';
                            $mainFolder = 'expense';
                            $hiddenFileId = $request->receipt[$i];
                            $file = $request->receipt[$i];
                            $uploadFile = $file->getClientOriginalName();
                            $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                            $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                            $files = ImageUpload::upload($file,$mainFolder,$fname);
                            // $file->move($mainFolder, $fname);
                            $data = [
                                'receipt' => $fname,
                            ];
                            $fileRes = Expense::find($expensesIds[$i]);
                            $fileRes->update($data);
                        }
                    }
                }
            }
            $bill['payment_mode'] = $request->payment_mode;
            $bill['bank_id'] = $request->bank_id;
            $bill['account_id'] = $request->account_id;
            $bill['bank_balance'] =  $request->bank_balance;
            $bill['branch_balance'] = $request->branch_total_balance;
            $bill['cheque_id'] = $request->cheque_id;
            $bill['utr_no'] = $request->utr_no;
            $bill['neft_charge'] = $request->neft_charge;
            $bill['party_name'] = $request->party_name;
            $bill['party_bank_name'] = $request->party_bank_name;
            $bill['party_bank_ac_no'] = $request->party_bank_ac_no;
            $bill['party_bank_ifsc'] = $request->party_bank_ifsc;
            $bill['branch_id'] = $branchid;
            $bill['company_id'] = $companyId;
            $bill['bill_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $bill_date)));
            $billExist = \App\Models\BillExpense::where('bill_no', $request->bill_no)->update($bill);
            if (isset($_POST['particular_more'])) {
                foreach (($_POST['particular_more']) as $key => $option) {
                    $dataExpenseMore = array();
                    $dataExpenseMore['account_head_id'] = $_POST['account_head_more'][$key];
                    $dataExpenseMore['sub_head1'] = $_POST['sub_head1_more'][$key];
                    if ($_POST['sub_head2_more'][$key] != '') {
                        $dataExpenseMore['sub_head2'] = $_POST['sub_head2_more'][$key];
                    }
                    $dataExpenseMore['particular'] = $_POST['particular_more'][$key];
                    $dataExpenseMore['payment_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $bill_date)));;
                    $dataExpenseMore['bill_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $bill_date)));;
                    $dataExpenseMore['amount'] = $_POST['amount_more'][$key];
                    $dataExpenseMore['bill_no'] = $request->bill_no;
                    $dataExpenseMore['created_at'] = $request->created_at;
                    $dataExpenseMore['updated_at'] = $request->created_at;
                    $dataExpenseMore['company_id'] = $request->company_id;
                    $dataExpenseMore['status'] = 0;
                    $expense_res = Expense::create($dataExpenseMore);
                    $expenseIdMore = $expense_res->id;
                    // expenses_logs($expense_res->id, $expense_res->account_head_id, $expense_res->branch_id, "update",1);
                    $files = $request->file('receipt_more');
                    if ($request->hasFile('receipt_more')) {
                        // $mainFolder = storage_path() . '/images/expense';
                        $mainFolder = 'expense';
                        $file = $request['receipt_more'][$key];
                        $uploadFile = $file->getClientOriginalName();
                        $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                        $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                        $files = ImageUpload::upload($file,$mainFolder,$fname);
                        // $file->move($mainFolder, $fname);
                        $fData = [
                            'file_name' => $fname,
                            'file_path' => $mainFolder,
                            'file_extension' => $file->getClientOriginalExtension(),
                        ];
                        $expenseUpdate = Expense::find($expenseIdMore);
                        $expenseUpdate->receipt = $fname;
                        $expenseUpdate->update();
                    }
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        expenses_logs($request->bill_no, NULL, "update", Auth::user()->id);
        return redirect()->route('admin.expense.expense_bill')->with('success', 'Expense Update  Successfully');
    }
    /**
     * Delete Bill And Expense
     * @param bill id
     **/
    public function deleteBill(Request $request)
    {
        $response['status'] = '';
        $response['message'] = '';
        $billNo = $request->bill_no;
        $title = $request->title;
        try{
            if ($title == 'Delete Approved Bill') {
                $BillRecord = \App\Models\BillExpense::select('daybook_refid', 'payment_mode', 'branch_id')->where('bill_no', $billNo)->first();
                $deleteBranchDaybook = \App\Models\BranchDaybook::where('daybook_ref_id', $BillRecord->daybook_refid)->get();
                // $deleteAllheadTransaction = \App\Models\AllHeadTransaction::where('daybook_ref_id',$value->daybook_refid)->update(['is_deleted'=>1]);
                $deleteAllheadTransaction = \App\Models\AllHeadTransaction::where('daybook_ref_id', $BillRecord->daybook_refid)->get();
                foreach ($deleteBranchDaybook as $value) {
                    //dd($value);
                    $deletedBranch = \App\Models\BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->update(['is_deleted' => 1]);
                }
                foreach ($deleteAllheadTransaction as $value) {
                    $deletedBranch = \App\Models\AllHeadTransaction::where('daybook_ref_id', $value->daybook_ref_id)->update(['is_deleted' => 1]);
                }
                if ($BillRecord->payment_mode == 1 || $BillRecord->payment_mode == 2) {
                    $deleteSamraddhBankDaybook = \App\Models\SamraddhBankDaybook::where('daybook_ref_id', $BillRecord->daybook_refid)->update(['is_deleted' => 1]);
                }
                $deleteExpense = \App\Models\Expense::where('bill_no', $billNo)->get();
                foreach ($deleteExpense as $value) {
                    $delet =   \App\Models\Expense::where('bill_no', $billNo)->update(['is_deleted' => 1]);
                }
                $deleteBill = \App\Models\BillExpense::where('bill_no', $billNo)->where('branch_id', $BillRecord->branch_id)->update(['is_deleted' => 1]);
                $response['status'] = 1;
                $response['message'] = 'Approved Bill No - ' . $billNo . ' Deleted Successfully!';
                expenses_logs($request->bill_no, NULL, "bill_delete", Auth::user()->id);
            } elseif ($title == 'Delete Expense') {
                $BillRecord = \App\Models\BillExpense::select('daybook_refid', 'payment_mode', 'branch_id')->where('bill_no', $billNo)->first();
                $deleteExpense = \App\Models\Expense::where('bill_no', $billNo)->get();
                foreach ($deleteExpense as $value) {
                    $delet =   \App\Models\Expense::where('bill_no', $billNo)->update(['is_deleted' => 1]);
                }
                $deleteBill = \App\Models\BillExpense::where('bill_no', $billNo)->where('branch_id', $BillRecord->branch_id)->update(['is_deleted' => 1]);
                if ($deleteExpense && $deleteBill) {
                    $response['status'] = 1;
                    $response['message'] = 'Bill No - ' . $billNo . ' Deleted Successfully!';
                }
                expenses_logs($request->bill_no, NULL, "delete", Auth::user()->id);
            }
        } catch (\Exception $ex) {
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
        }
        echo json_encode($response);
        die;
    }
    public function export_bill(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/expense_bill.csv";
        $fileName = env('APP_EXPORTURL') ."/asset/expense_bill.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        $data = \App\Models\BillExpense::has('companyName')->with('expenses')->with('companyName:id,name')->where('is_deleted', 0);
        if ($request['expense_export'] == 0) {
            // if ($request['start_date'] != '') {
            //     $startDate = date('Y-m-d', strtotime(convertDate($request['start_date'])));
            //     if ($request['end_date'] != '') {
            //         $endDate = date('Y-m-d', strtotime(convertDate($request['end_date'])));
            //     } else {
            //         $endDate = '';
            //     }
            //     $data = $data->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            // }
            if ($request['start_date'] != '') {
                $startDate = date('Y-m-d', strtotime(convertDate($request['start_date'])));
                $data = $data->whereDate('created_at', '>=', $startDate);
            }
            
            if ($request['end_date'] != '') {
                $endDate = date('Y-m-d', strtotime(convertDate($request['end_date'])));
                $data = $data->whereDate('created_at', '<=', $endDate);
            }
            if ($request['company_id'] != '') {
                if ($request['company_id'] > 0) {
                    $data = $data->where('company_id', $request['company_id']);
                }
            }
            if ($request['branch_id'] != '') {
                if ($request['branch_id'] > 0) {
                    $data = $data->where('branch_id', $request['branch_id']);
                }
            }
            if ($request['party_name'] != '') {
                $data = $data->where('party_name', 'like', '%' . $request["party_name"] . '%');
            }
            if ($request['status'] != '') {
                $data = $data->where('status', $request['status']);
            }
            $sno = $_POST['start'];
            $totalResults = $data->orderby('id', 'DESC')->count();
            $data = $data->orderby('created_at', 'DESC')->offset($start)->limit($limit)->get();
            // $results=$data->orderby('created_at','DESC')->offset($start)->limit($limit)->get();
            // $result = 'next';
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
            foreach ($data as $row) {
                $sno++;
                $val['SR_NO'] = $sno;
                $val['COMPANY NAME'] = isset($row['companyName']->name)? $row['companyName']->name:"N/A";
                $val['BRANCH NAME'] = isset(getBranchDetail($row->branch_id)->name)?getBranchDetail($row->branch_id)->name : 'N/A';
                $val['BRANCH CODE'] = isset(getBranchDetail($row->branch_id)->branch_code)? getBranchDetail($row->branch_id)->branch_code : 'N/A';
                $val['CREATED AT'] = date('d/m/Y', strtotime($row->created_at));
                $val['BILL DATE'] = $row->bill_date ? date('d/m/Y', strtotime($row->bill_date)) : 'N/A';
                $val['BILL NO.'] = $row->bill_no;
                $val['PARTY NAME'] = $row->party_name;
                $PartyBankName = 'N/A';
                if ($row->party_bank_name) {
                    $PartyBankName =  $row->party_bank_name;
                }
                $val['PARTY BANK NAME'] = $PartyBankName;
                $partyBAnkAc = 'N/A';
                if ($row->party_bank_ac_no) {
                    $partyBAnkAc =  $row->party_bank_ac_no;
                }
                $val['PARTY BANK A/C NO'] = $partyBAnkAc;
                $partyBankIfsc = 'N/A';
                if ($row->party_bank_ifsc) {
                    $partyBankIfsc =  $row->party_bank_ifsc;
                }
                $val['PARTY BANK IFSC'] = $partyBankIfsc;
                switch ($row->payment_mode) {
                    case '0':
                        $paymentMode = 'CASH';
                        break;
                    case '1':
                        $paymentMode = 'CHEQUE';
                        break;
                    case '2':
                        $paymentMode = 'ONLINE';
                        break;
                    default:
                        $paymentMode = 'N/A';
                        break;
                }
                $val['PAYMENT MODE'] = $paymentMode;
                switch ($row->payment_mode) {
                    case '1':
                        $cheque = \App\Models\SamraddhCheque::select('cheque_no')->where('id', $row->cheque_id)->first();
                        $cheque = $cheque->cheque_no;
                        break;
                    default:
                        $cheque = 'N/A';
                        break;
                }
                $val['CHEQUE NO.'] = $cheque;
                switch ($row->payment_mode) {
                    case '2':
                        $utr = $row->utr_no;
                        break;
                    default:
                        $utr = 'N/A';
                        break;
                }
                $val['UTR NO.'] = $utr;
                switch ($row->payment_mode) {
                    case '2':
                        $neft_charge = $row->neft_charge;
                        break;
                    default:
                        $neft_charge = 'N/A';
                        break;
                }
                $val['NEFT CHARGE'] = $neft_charge;
                $mainHead = ' ';
                $subHead = ' ';
                $subHead2 = '';
                if (isset($row['expenses']->account_head_id)) {
                    $mainHead = getAcountHeadNameHeadId($row['expenses']->account_head_id);
                    $des = $mainHead;
                }
                if (isset($row['expenses']->sub_head1)) {
                    $subHead = getAcountHeadNameHeadId($row['expenses']->sub_head1);
                    $des = $mainHead . '/' . $subHead;
                }
                if (isset($row['expenses']->sub_head2)) {
                    $subHead2 = getAcountHeadNameHeadId($row['expenses']->sub_head2);
                    $des = $mainHead . '/' . $subHead . '/' . $subHead2;
                }
                $h =  $mainHead . (($subHead  != '') ? '' : '/' . $subHead . (($subHead2 != '') ? '' : '/' . $subHead2));
                //$val['ACCOUNT HEAD']=$h;
                $date = date('Y-m-d', strtotime($row->bill_date));
                $amount = \App\Models\Expense::where('bill_no', $row->bill_no)->where(DB::raw('DATE(bill_date)'), $date)->sum('amount');
                $val['AMOUNT'] = $amount;
                $totalExpense = \App\Models\Expense::where('bill_no', $row->bill_no)->count();
                $val['TOTAL EXPENSE'] = $totalExpense;
                switch ($row->status) {
                    case 0:
                        $status = "Pending";
                        break;
                    case 1:
                        $status = "Approved";
                        break;
                    case 2:
                        $status = "Deleted";
                        break;
                    default:
                        $status = "N/A";
                }
                $val['STATUS'] = $status;
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
            // Make sure nothing else is sent, our file is done
            exit;
        }
    }
}
