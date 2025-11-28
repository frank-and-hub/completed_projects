<?php

namespace App\Http\Controllers\Branch\Expense;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountHeads;
use App\Models\Expense;
use Illuminate\Support\Facades\Cache;
use App\Models\CompanyBranch;
use Yajra\DataTables\DataTables;
use URL;
use DB;
use Session;
use App\Services\ImageUpload;
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
        if (!in_array('Add Expense Booking', auth()->user()->getPermissionNames()->toArray())) {

            return redirect()
    
                ->route('branch.dashboard');
    
            }
        $data['title'] = 'Expense Booking Form';

        $data['branch'] = \App\Models\Branch::select('id', 'name')->where('manager_id', Auth::user()->id)->first();
        $branchId = $data['branch']->id;
        $data['selectedCompany'] = CompanyBranch::where([['branch_id', $branchId], ['is_primary', 1]])->first()->company_id;
        $data['account_heads'] = AccountHeads::where('parent_id', 86)->get();
        $data['bank'] = \App\Models\SamraddhBank::where('status', 1)->where('company_id', $data['selectedCompany'])->get();
        return view('templates.branch.expense.add_expense', $data);
    }

    public function get_indirect_expense()
    {
        $account_heads = AccountHeads::where('parent_id', 86)->get();
        return response()->json($account_heads);
    }
    public function get_indirect_expense_sub_head(Request $request)
    {
        $account_heads = AccountHeads::where('parent_id', $request->head_id)->where('status', 0)->get();
        $return_array = compact('account_heads');
        return json_encode($return_array);
    }

    public function save(Request $request)
    {
        // pd($request->all());
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
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($request->created_at)));
            $created_time = date("His", strtotime(convertDate($request->created_at)));
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

                // $mainFolder = storage_path() . '/images/expense';
                $mainFolder = 'expense';
                $file = $request->receipt;
                $uploadFile = $file->getClientOriginalName();
                $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                ImageUpload::upload($file,$mainFolder,$fname);
                // $file->move($mainFolder, $fname);
                $fData = [
                    'file_name' => $fname,
                    'file_path' => $mainFolder,
                    'file_extension' => $file->getClientOriginalExtension(),
                ];
                $expenseUpdate = Expense::find($expenseId);
                $expenseUpdate->receipt = $fname;
                $expenseUpdate->save();
                /*$res = Files::create($fData);
                    $file_id = $res->id;*/
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
                    $expense_res = Expense::create($dataExpenseMore);;
                    $expenseIdMore = $expense_res->id;
                    $files = $request->file('receipt_more');
                    // die();
                    if ($request->hasFile('receipt_more')) {

                        //dd($files);die();
                        $mainFolder = 'expense';

                        // $mainFolder = storage_path() . '/images/expense';
                        $file = $request['receipt_more'][$key];
                        $uploadFile = $file->getClientOriginalName();
                        $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                        $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                        ImageUpload::upload($file,$mainFolder,$fname);
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

        return redirect()->route('branch.expense.expense_bill')->with('success', 'Expense Created  Successfully');
    }
    // public function save(Request $request)
    // {
    //     // dd($request->all());
    //       $rules = [
    //         'account_head' => ['required'],
    //         'branch_id' => ['required'],
    //         'amount' => ['required'],

    //     ];
    //     $customMessages = [
    //         'required' => ':Attribute  is required.',
    //         'unique' => ' :Attribute already exists.'
    //     ];
    //      $this->validate($request, $rules, $customMessages);

    //     DB::beginTransaction();
    //     try {

    //         $entry_date = date("Y-m-d", strtotime(convertDate($request->created_at)));
    //         $entry_time = date("H:i:s", strtotime(convertDate($request->created_at)));

    //         $created_at = date("Y-m-d H:i:s", strtotime(convertDate($request->created_at)));
    //         $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($request->created_at))); 
    //         Session::put('created_at', $created_at);
    //         $billNo = random_int(0,5000);

    //         $billNumber = $billNo;
    //         $bill['bill_no'] = $billNumber;
    //         $bill['payment_mode'] = $request->payment_mode;
    //         $bill['bank_id'] = $request->bank_id;
    //         $bill['account_id'] = $request->account_id;
    //         $bill['bank_balance'] =  $request->bank_balance;
    //         $bill['branch_balance'] = $request->branch_total_balance;
    //         $bill['cheque_id'] = $request->cheque_id;
    //         $bill['utr_no'] = $request->utr_no;
    //         $bill['neft_charge'] = $request->neft_charge; 
    //         $bill['party_name'] = $request->party_name;
    //         $bill['party_bank_name'] = $request->party_bank_name;
    //         $bill['party_bank_ac_no'] = $request->party_bank_ac_no;
    //         $bill['party_bank_ifsc'] = $request->party_bank_ifsc;
    //         $bill['branch_id'] = $request->branch_id; 
    //         $billDetail = \App\Models\BillExpense::create($bill);

    //         $data['account_head_id'] = $request->account_head; 
    //         $data['sub_head1'] = $request->sub_head1;
    //         $data['sub_head2'] = $request->sub_head2;   
    //         $data['particular'] = $request->particular;
    //         $data['payment_date'] =date("Y-m-d", strtotime( str_replace('/','-', $request['bill_date']))); ;  
    //         $data['bill_date'] = date("Y-m-d", strtotime( str_replace('/','-', $request['bill_date']))); ;  
    //         $data['amount'] = $request->amount;
    //         $data['bill_no'] = $billDetail->bill_no;
    //         $data['status'] = 0;
    //         $data['created_at'] = $request->created_at;  
    //         $data['updated_at'] = $request->created_at;   
    //         $expense_res = Expense::create($data);
    //         $expenseId=$expense_res->id;

    //         if ($request->hasFile('receipt')) 
    //         {

    //                 $mainFolder = storage_path().'/images/expense';
    //                 $file = $request->receipt;
    //                 $uploadFile = $file->getClientOriginalName();
    //                 $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
    //                 $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();
    //                 $file->move($mainFolder,$fname);
    //                 $fData = [
    //                     'file_name' => $fname,
    //                     'file_path' => $mainFolder,
    //                     'file_extension' => $file->getClientOriginalExtension(),
    //                 ];
    //                  $expenseUpdate = Expense::find($expenseId);             
    //                 $expenseUpdate->receipt=$fname; 
    //                 $expenseUpdate->save();
    //                 /*$res = Files::create($fData);
    //                 $file_id = $res->id;*/
    //         }
    //         if(isset($_POST['particular_more']))
    //         {
    //             foreach(($_POST['particular_more']) as $key=>$option)
    //             {


    //                  $dataExpenseMore=array();
    //                 $dataExpenseMore['account_head_id'] = $_POST['account_head_more'][$key]; 
    //                 $dataExpenseMore['sub_head1'] = $_POST['sub_head1_more'][$key];
    //                 if($_POST['sub_head2_more'][$key]!='')
    //                 {
    //                     $dataExpenseMore['sub_head2'] = $_POST['sub_head2_more'][$key]; 
    //                 } 

    //                 $dataExpenseMore['particular'] = $_POST['particular_more'][$key]; 
    //                 $dataExpenseMore['payment_date'] =date("Y-m-d", strtotime( str_replace('/','-', $request['bill_date']))); ;  
    //                 $dataExpenseMore['bill_date'] = date("Y-m-d", strtotime( str_replace('/','-', $request['bill_date']))); ;  
    //                 $dataExpenseMore['amount'] = $_POST['amount_more'][$key]; 
    //                 $dataExpenseMore['bill_no'] = $billDetail->bill_no;    

    //                 $dataExpenseMore['created_at'] = $request->created_at;   
    //                 $dataExpenseMore['updated_at'] = $request->created_at;  
    //                 $dataExpenseMore['status'] = 1;                 
    //                 $expense_res = Expense::create($dataExpenseMore);;
    //                 $expenseIdMore=$expense_res->id;
    //                  $files = $request->file('receipt_more');
    //                 // die();
    //                 if ($request->hasFile('receipt_more')) 
    //                 {

    //                     //dd($files);die();

    //                     $mainFolder = storage_path().'/images/expense';
    //                     $file = $request['receipt_more'][$key];
    //                     $uploadFile = $file->getClientOriginalName();
    //                     $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
    //                     $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();
    //                     $file->move($mainFolder,$fname);
    //                     $fData = [
    //                         'file_name' => $fname,
    //                         'file_path' => $mainFolder,
    //                         'file_extension' => $file->getClientOriginalExtension(),
    //                     ];
    //                     $expenseUpdate = Expense::find($expenseIdMore); 

    //                     $expenseUpdate->receipt=$fname; 
    //                     $expenseUpdate->save();

    //             }
    //         }
    //       }
    //         DB::commit(); 
    //     } catch (\Exception $ex) {
    //         DB::rollback(); 
    //         return back()->with('alert', $ex->getMessage());
    //     }
    //     expenses_logs($billDetail->bill_no,NULL, "add",Auth::user()->id);

    //      return redirect()->route('admin.expense.expense_bill')->with('success', 'Expense Created  Successfully');
    // }
    public function report_expense($bill_no)
    {

        $data['title'] = 'Expense Booking | Expense Detail  Report';



        $data['bill_status'] = \App\Models\BillExpense::select('status', 'branch_id', 'created_at', 'bill_no')->where('is_deleted', 0)->where('id', $bill_no)->first();

        $data['bill_no'] = $data['bill_status']->bill_no;
        return view('templates.branch.expense.expense_report', $data);
    }
    public function expense_bill()
    {
        if (!in_array('Expense Booking Report', auth()->user()->getPermissionNames()->toArray())) {

            return redirect()
    
                ->route('branch.dashboard');
    
            }
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        $data['title'] = 'Expense Booking | Bill Report';
        $data['branch'] = \App\Models\Branch::where('id', $branch_id)->where('status', 1)->get();
        $data['companyId'] = \App\Models\CompanyBranch::select('company_id')
            ->with('get_company:id,name')
            ->where('branch_id',$branch_id)
            ->where('is_primary',1)
            ->first();
  
 
        return view('templates.branch.expense.bill_expense_report', $data);
    }
    public function  expense_report_listing(Request $request)
    {
        $data = Expense::with('branch')->where('bill_no', $request->bill_no)->where('is_deleted', 0)->orderBy('id', 'DESC')->get();
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
                        return getAcountHeadNameHeadId($row->sub_head1);;
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['sub_head1'])
                ->addColumn('sub_head2', function ($row) {
                    if ($row->sub_head2) {
                        return getAcountHeadNameHeadId($row->sub_head2);;
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
                    return date("d/m/Y", strtotime($row->bill_date));;
                })
                ->rawColumns(['bill_date'])
                ->addColumn('payment_date', function ($row) {
                    $date = 'N/A';
                    if ($row->approve_date) {
                        $date =  date("d/m/Y", strtotime($row->approve_date));;
                    }
                    return $date;
                })
                ->rawColumns(['payment_date'])

                ->make(true);
        }
    }

    public function bill_expense_report_listing(Request $request)
    {
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
   

        if ($request->ajax()) {
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $companyId = $arrFormData['company_id'];
            $data = \App\Models\BillExpense::where('branch_id', $branch_id)
                ->where('company_id',$companyId)
                ->where('is_deleted', 0)
                ->with('expenses:id,bill_no,account_head_id,sub_head1,sub_head2');

            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['is_search'] == 'yes') {
                    // if($arrFormData['company_id'] != '')
                    // {
                    //     $data = $data->where('company_id',$arrFormData['company_id']);
                    // }
                    if ($arrFormData['start_date'] != '') {
                        $startDate = date('Y-m-d', strtotime(convertDate($arrFormData['start_date'])));
                        if ($arrFormData['end_date'] != '') {
                            $endDate = date('Y-m-d', strtotime(convertDate($arrFormData['end_date'])));
                        } else {
                            $endDate = '';
                        }
                        $data = $data->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                    }
                    if ($arrFormData['branch_id'] != '') {
                        $data = $data->where('branch_id', $arrFormData['branch_id']);
                    }
                    if ($arrFormData['company_id'] != '') {
                        $data = $data->where('company_id', $arrFormData['company_id']);
                    }
                    if ($arrFormData['party_name'] != '') {
                        $data = $data->where('party_name', 'like', '%' . $arrFormData["party_name"] . '%');
                    }

                    if ($arrFormData['status'] != '') {

                        $data = $data->where('status', $arrFormData['status']);
                    }
                }
            }
            $data = $data->orderBy('created_at', 'DESC')->get();

            $token = session()->get('_token');
            Cache::put('bill_expense_report_branch'.$token,$data);
            Cache::put('bill_expense_report_branch_count'.$token,count($data));

            return Datatables::of($data)
                ->addIndexColumn()
                // ->addColumn('branch_code', function($row){
                //     return getBranchDetail($row->branch_id)->branch_code;
                // })
                // ->rawColumns(['branch_code'])
                ->addColumn('branch_name', function ($row) {

                    return getBranchDetail($row->branch_id)->name . " (" . getBranchDetail($row->branch_id)->branch_code . ")";
                })
                ->rawColumns(['branch_name'])
                // ->addColumn('company_name', function($row){

                //     return $row['companyName']->name;
                // })
                // ->rawColumns(['company_name'])
                ->addColumn('created_at', function ($row) {

                    return date('d/m/Y', strtotime($row->created_at));
                })
                ->rawColumns(['created_at'])
                ->addColumn('bill_date', function ($row) {
                    $date = date('Y-m-d', strtotime($row->created_at));
                    $a = \App\Models\Expense::where('bill_no', $row->bill_no)->where(DB::raw('DATE(created_at)'), $date)->first();
                    if (isset($row->bill_date)) {
                        return date('d/m/Y', strtotime($row->bill_date));
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['bill_date'])
                ->addColumn('bill_no', function ($row) {

                    return $row->bill_no;
                })
                ->rawColumns(['bill_no'])
                ->addColumn('party_name', function ($row) {

                    return $row->party_name;
                })
                ->rawColumns(['party_name'])
                ->addColumn('party_bank_name', function ($row) {
                    $PartyBankName = 'N/A';
                    if ($row->party_bank_name) {
                        $PartyBankName =  $row->party_bank_name;
                    }
                    return $PartyBankName;
                })
                ->rawColumns(['party_bank_name'])
                ->addColumn('party_bank_ac_no', function ($row) {
                    $partyBAnkAc = 'N/A';
                    if ($row->party_bank_ac_no) {
                        $partyBAnkAc =  $row->party_bank_ac_no;
                    }
                    return $partyBAnkAc;
                })
                ->rawColumns(['party_bank_ac_no'])
                ->addColumn('party_bank_ifsc', function ($row) {
                    $partyBankIfsc = 'N/A';
                    if ($row->party_bank_ifsc) {
                        $partyBankIfsc =  $row->party_bank_ifsc;
                    }
                    return $partyBankIfsc;
                })
                ->rawColumns(['party_bank_ifsc'])
                ->addColumn('payment_mode', function ($row) {

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
                    return $paymentMode;
                })
                ->rawColumns(['payment_mode'])
                ->addColumn('cheque_no', function ($row) {

                    switch ($row->payment_mode) {
                        case '1':
                            $cheque = \App\Models\SamraddhCheque::select('cheque_no')->where('id', $row->cheque_id)->first();
                            $cheque = $cheque->cheque_no;

                            break;
                        default:
                            $cheque = 'N/A';
                            break;
                    }
                    return $cheque;
                })
                ->rawColumns(['cheque_no'])
                ->addColumn('utr_no', function ($row) {

                    switch ($row->payment_mode) {
                        case '2':
                            $utr = $row->utr_no;
                            break;
                        default:
                            $utr = 'N/A';
                            break;
                    }
                    return $utr;
                })
                ->rawColumns(['utr_no'])
                ->addColumn('neft_charge', function ($row) {
                    $neft_charge = 'N/A';
                    switch ($row->payment_mode) {
                        case '2':
                            $neft_charge = $row->neft_charge;
                            break;
                        default:
                            // code...
                            break;
                    }
                    return $neft_charge;
                })
                ->rawColumns(['neft_charge'])

                // ->addColumn('account_head', function ($row) {
                //     if (!empty($row['expenses'])) {
                //         $mainHead = ' ';
                //         $subHead = ' ';
                //         $subHead2 = '';
                //         if ($row['expenses']->account_head_id) {
                //             $mainHead = getAcountHeadNameHeadId($row['expenses']->account_head_id);
                //             $des = $mainHead;
                //         }
                //         if ($row['expenses']->sub_head1) {
                //             $subHead = getAcountHeadNameHeadId($row['expenses']->sub_head1);;
                //             $des = $mainHead . '/' . $subHead;
                //         }
                //         if ($row['expenses']->sub_head2) {
                //             $subHead2 = getAcountHeadNameHeadId($row['expenses']->sub_head2);;
                //             $des = $mainHead . '/' . $subHead . '/' . $subHead2;
                //         }
                //         return $mainHead . (($subHead  != '') ? '' : '/' . $subHead . (($subHead2 != '') ? '' : '/' . $subHead2));
                //     } else {
                //         return 'N/A';
                //     }
                // })
                // ->rawColumns(['account_head'])
                ->addColumn('amount', function ($row) {
                    $date = date('Y-m-d', strtotime($row->created_at));
                    $amount = \App\Models\Expense::where('bill_no', $row->bill_no)->where(DB::raw('DATE(bill_date)'), $date)->sum('amount');
                    return $amount;
                })
                ->rawColumns(['amount'])
                ->addColumn('total_expense', function ($row) {
                    $totalExpense = \App\Models\Expense::where('bill_no', $row->bill_no)->count();
                    return $totalExpense;
                })
                ->rawColumns(['total_expense'])
                ->addColumn('status', function ($row) {
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
                    return $status;
                })
                ->rawColumns(['status'])
                ->addColumn('action', function ($row) {
                    $detailExpenseurl =  URL::to("branch/report/expense/" . $row->id . "");
                    $deleteurl = URL::to("branch/bill_delete");
                    $print_url =  URL::to("branch/report/expense_pr/" . $row->id . "");

                    $btn = '';
                    if ($row->status == 0) {

                        $btn .= '<a href="branch/expense/edit/' . $row->id . '"   title="Edit Expense"><i class="fa fa-edit mr-3"></i></a>';

                        $btn .= '<a class="delete_expense mr-4" href="#" onclick="return false;" data-row-id="' . $row->bill_no . '" title="Delete Expense"><i class="fa fa-trash"></i></a>';
                    }
                    if (in_array('Expense bill print', auth()->user()->getPermissionNames()->toArray())) {
                        if ($row->status == 1) {

                            $btn .= '<a class=""  href="' . $print_url . '" title="Print Detail Expense"><i class="fas fa-print mr-3
                            "></i></button>';
                        }
                    }



                    $btn .= '<a class=""  href="' . $detailExpenseurl . '" title="Detail Expense"><i class="fas fa-eye text-default"></i></button>';

                    return '
                    <div class="list-icons">
                        <div class="dropdown">
                            <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                            <div class="">
                                ' . $btn . '
                            </div>
                        </div>
                    </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }



    public function edit($id)
    {
        $title = 'Edit Expense';

        $branch = \App\Models\Branch::select('id', 'name')->where('manager_id', Auth::user()->id)->first();;

        $account_heads = AccountHeads::where('parent_id', 86)->where('entry_everywhere', 0)->get(['sub_head', 'head_id','company_id']);

        $billExpense = \App\Models\BillExpense::with('expenses')->select('bill_no', 'party_name', 'branch_balance', 'payment_mode', 'cheque_id', 'utr_no', 'neft_charge', 'party_bank_name', 'party_bank_ac_no', 'party_bank_ifsc', 'branch_id', 'bank_balance', 'bank_id', 'account_id', 'created_at', 'bill_date', 'company_id')->where('id', $id)->where('is_deleted', 0)->first();
        $companyId = $billExpense->company_id;
        $bank = \App\Models\SamraddhBank::where('status', 1)->where('company_id', $companyId)->get(['id', 'bank_name']);
        $bank_ac = \App\Models\SamraddhBankAccount::where('status', 1)->where('id', $billExpense->account_id)->get(['id', 'account_no']);
        $cheques = \App\Models\SamraddhCheque::select('cheque_no', 'id')->where('is_use', 0)->get();
        $expenseData = \App\Models\Expense::select('id', 'bill_no', 'bill_date', 'particular', 'account_head_id', 'sub_head1', 'sub_head2', 'amount', 'receipt')->where('bill_no', $billExpense->bill_no)/*->where(DB::raw('Date(bill_date)'), date("Y-m-d", strtotime($billExpense->bill_date)))*/->where('is_deleted', 0)->get();


        $data = [
            'title' => 'title',
            'branch' => 'branch',
            'account_heads' => 'account_heads',
            'bank' => 'bank',
            'bank_ac' => 'bank_ac',
            'expenseData' => 'expenseData',
            'billExpense' => 'billExpense',
            'cheques' => 'cheques',
        ];

        return view('templates.branch.expense.edit_expense', compact($data));
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
                        $ExistExpenseUpdate = Expense::where('id', $expensesIds[$i])->update($dataExpenseMore);
                        if (isset($request->receipt[$i])) {
                            // $mainFolder = storage_path() . '/images/expense';
                            $mainFolder = 'expense';
                            $hiddenFileId = $request->receipt[$i];
                            $file = $request->receipt[$i];
                            $uploadFile = $file->getClientOriginalName();
                            $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                            $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                            ImageUpload::upload($file,$mainFolder,$fname);
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
            $bill['bill_date'] =  date("Y-m-d", strtotime(str_replace('/', '-', $bill_date)));

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
                    $expense_res = Expense::create($dataExpenseMore);;
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
                        ImageUpload::upload($file,$mainFolder,$fname);
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

        return redirect()->route('branch.expense.expense_bill')->with('success', 'Expense Update  Successfully');
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
        if ($title == 'delete_expense') {
            $deleteExpense = \App\Models\Expense::where('bill_no', $billNo)->delete();
            $deleteBill = \App\Models\BillExpense::where('bill_no', $billNo)->delete();
            if ($deleteExpense && $deleteBill) {
                $response['status'] = 1;
                $response['message'] = 'Bill No - ' . $billNo . ' Deleted Successfully!';
            }
            expenses_logs($request->bill_no, NULL, "delete", Auth::user()->id);
        }


        echo json_encode($response);
        die;
    }


    public function export_bill(Request $request)
    {
        $token = session()->get('_token');
        $data = Cache::get('bill_expense_report_branch'.$token);
        $count = Cache::get('bill_expense_report_branch_count'.$token);
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/expense_bill.csv";
        $fileName = env('APP_EXPORTURL') . "asset/expense_bill.csv";
        header("Content-type: text/csv");
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

        if ($request['expense_export'] == 0) {
            foreach ($data->slice($start,$limit) as $row) {
                $sno++;
                $val['SR_NO'] = $sno;
                // $val['COMPANY NAME'] = $row['companyName']->name;
                $val['BRANCH NAME'] = $row['getBranchCustom']->name;
                $val['BRANCH CODE'] = $row['getBranchCustom']->branch_code;
                $val['REGION'] = $row['getBranchCustom']->regan;
                $val['ZONE'] = $row['getBranchCustom']->zone;
                $val['SECTOR'] = $row['getBranchCustom']->sector;
                $val['Bill Date'] = date('d/m/Y', strtotime($row->bill_date));
                $val['CREATED AT'] = date('d/m/Y', strtotime($row->created_at));
                $val['BILL NO.'] = $row->bill_no;
                $val['PARTY NAME'] = $row->party_name;
                
                $val['PARTY BANK NAME'] = $row->party_bank_name ?? 'N/A';
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
                // if (!empty($row['expenses'])) {
                //     $mainHead = ' ';
                //     $subHead = ' ';
                //     $subHead2 = '';
                //     if ($row['expenses']->account_head_id) {
                //         $mainHead = getAcountHeadNameHeadId($row['expenses']->account_head_id);
                //         $des = $mainHead;
                //     }
                //     if ($row['expenses']->sub_head1) {
                //         $subHead = getAcountHeadNameHeadId($row['expenses']->sub_head1);;
                //         $des = $mainHead . '/' . $subHead;
                //     }
                //     if ($row['expenses']->sub_head2) {
                //         $subHead2 = getAcountHeadNameHeadId($row['expenses']->sub_head2);;
                //         $des = $mainHead . '/' . $subHead . '/' . $subHead2;
                //     }
                //     $h =  $mainHead . (($subHead  != '') ? '' : '/' . $subHead . (($subHead2 != '') ? '' : '/' . $subHead2));
                //     $val['ACCOUNT HEAD'] = $h;
                // } else {
                //     $val['ACCOUNT HEAD'] = "N/A";
                // }


                $amount = \App\Models\Expense::where('bill_no', $row->bill_no)->sum('amount');
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


    public function report_expense_print($bill_no)
    {
        if (check_my_permission(Auth::user()->id, "169") != "1" || check_my_permission(Auth::user()->id, "254") != "1") {
            return redirect()->route('branch.dashboard');
        }
        $data['title'] = 'Print Expense Booking | Print Expense Detail  Report';

        $data['bill_status'] = \App\Models\BillExpense::select('bill_date', 'status', 'branch_id', 'created_at', 'bill_no', 'party_name', 'id', 'company_id')->where('is_deleted', 0)->where('id', $bill_no)->with('companyName:id,name,address,email,mobile_no')->first();

        $data['bill_no'] = $data['bill_status']->bill_no;
        $data['created_at'] = $data['bill_status']->created_at;
        $data['party_name'] = $data['bill_status']->party_name;
        $data['branch_id'] = $data['bill_status']->branch_id;

        $data['print_data'] = Expense::with('branch')->where('bill_no', $data['bill_status']->bill_no)->where(DB::raw('DATE(created_at)'), date('Y-m-d', strtotime($data['bill_status']->created_at)))->where('is_deleted', 0)->orderBy('id', 'DESC')->get();

        $data['total_amount'] = $data['print_data']->sum('amount');
        $sno = 1;
        $rowReturn = array();
        //dd($data['print_data']);
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

        return view('templates.branch.expense.expense_report_print', $data);
    }
}
// branch 