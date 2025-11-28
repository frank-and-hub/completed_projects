<?php
namespace App\Http\Controllers\Admin;
use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\SamraddhBank;
use App\Models\SamraddhCheque;
use App\Models\SamraddhChequeBook;
use App\Models\SamraddhBankAccount;
use App\Models\SamraddhChequeIssue;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Services\Email;
use App\Services\Sms;
use Illuminate\Support\Facades\Schema;
/*
|---------------------------------------------------------------------------
| Admin Panel -- Cheque Management ChequeController
|--------------------------------------------------------------------------
|
| This controller handles members all functionlity.
*/
class ChequeController extends Controller
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
    * Show Cheque.
    * Route: /admin/cheque
    * Method: get 
    * @return  array()  Response
    */
    public function index()
    {
        if (check_my_permission(Auth::user()->id, "96") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Cheque Management | Cheque List';
        $data['bank'] = SamraddhBank::has('company')->select('id', 'bank_name')->where('status', 1)->get();
        $data['account'] = SamraddhBankAccount::select('id', 'account_no')->where('status', 1)->get();
        return view('templates.admin.cheque_management.index', $data);
    }
    /**
    * Show Add cheque.
    * Route: /admin/cheque/add
    * Method: get 
    * @return  array()  Response
    */
    public function add()
    {
        if (check_my_permission(Auth::user()->id, "95") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Cheque Management | Add Cheque';
        $data['bank'] = SamraddhBank::select('id', 'bank_name')->where('status', 1)->get();
        return view('templates.admin.cheque_management.add', $data);
    }
    /**
    * Get bank account  according to bank.
    * Route: ajax call from - /admin/cheque/add
    * Method: Post 
    * @param  \Illuminate\Http\Request  $request
    * @return JSON array
    */
    public function getBankAccount(Request $request)
    {
        // $account = accountListBank($request->bank_id);
        // $return_array = compact('account');
        // return json_encode($return_array);
        $account = accountListBank($request->bank_id);
        if($request->company_id != '0'){
            $banks = \App\Models\SamraddhBank::where('status', 1)->where('company_id', $request->company_id)->get();
        }
        $return_array = compact('account', 'banks');
        return json_encode($return_array);
    }
    
    /**
    * Route: /admin/cheque/add
    * Method: Post 
    * @param  \Illuminate\Http\Request  $request
    * add cheque.
    * @return  array()  Response
    */
    public function chequeSave(Request $request)
    {
        // print_r($_POST);die;
        $coutDigit = strlen($request->cheque_from);
        DB::beginTransaction();
        try {
            $data = SamraddhCheque::where('account_id', $request->account_id)->whereBetween('cheque_no', array($request->cheque_from, $request->cheque_to))->get();
            $count = count($data);
            if ($count > 0) {
                return back()->with('alert', 'Enter cheque number range already exits');
            }
            $chequeBook['bank_id'] = $request->bank_id;
            $chequeBook['account_id'] = $request->account_id;
            $chequeBook['cheque_no_from'] = $request->cheque_from;
            $chequeBook['cheque_no_to'] = $request->cheque_to;
            $chequeBook['total_count'] = $request->total_cheque;
            $chequeBook['cheque_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->cheque_date)));
            $chequeBook['created_at'] = $request->created_at;
            $book = SamraddhChequeBook::create($chequeBook);
            $bookId = $book->id;
            for ($i = 0; $i < $request->total_cheque; $i++) {
                $cheque_no_new = $request->cheque_from + $i;
                $chequeNumber = str_pad($cheque_no_new, $coutDigit, '0', STR_PAD_LEFT);
                $cheque['bank_id'] = $request->bank_id;
                $cheque['account_id'] = $request->account_id;
                $cheque['cheque_book_id'] = $bookId;
                $cheque['cheque_no'] = $chequeNumber;
                $cheque['cheque_create_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->cheque_date)));
                $cheque['created_at'] = $request->created_at;
                $chequeCreate = SamraddhCheque::create($cheque);
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()->route('admin.cheque_list')->with('success', 'Cheque created successfully!');
    }
    /**
    * Get cheque list
    * Route: ajax call from - /admin/cheque
    * Method: Post 
    * @param  \Illuminate\Http\Request  $request
    * @return JSON array
    */
    public function chequeListing(Request $request)
    {
        if ($request->ajax()) {
            // fillter array 
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            //print_r($arrFormData);die;
            /******* fillter query start ****/
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                // $data = SamraddhCheque::select('id', 'cheque_create_date', 'cheque_no', 'is_use', 'status', 'cheque_delete_date', 'cheque_cancel_date', 'remark_cancel', 'account_id', 'bank_id')
                // ->with(['samrddhBank' => function ($query) {
                //     $query->has('company')->select('id', 'bank_name','company_id')->with('company');
                //  }])
                // ->with(['samrddhAccount' => function ($query) {
                //         $query->select('id', 'account_no');
                //     }]);
                
                $data = SamraddhCheque::with(['samrddhBank' => function ($query) {
                    $query->select('id', 'bank_name', 'company_id');
                    }])
                    ->whereHas('samrddhBank', function ($query) {
                        $query->has('company')->with('company')->where('status', 1);
                    })
                    ->with(['samrddhAccount' => function ($query){
                        $query->select('id', 'account_no');
                    }]);

                if ($arrFormData['bank_id'] != '') {
                    $bank_id = $arrFormData['bank_id'];
                    $data = $data->where('bank_id', $bank_id);
                }
                if ($arrFormData['account_id'] != '') {
                    $id = $arrFormData['account_id'];
                    $data = $data->where('account_id', $id);
                }
                if ($arrFormData['cheque_no'] != '') {
                    $cheque_no = $arrFormData['cheque_no'];
                    $data = $data->where('cheque_no', $cheque_no);
                }
                if (isset($arrFormData['company_id']) && $arrFormData['company_id'] != '') {
                    $companyId = $arrFormData['company_id'];
                    if ($companyId != '0') {
                        $data = $data->whereHas('samrddhBank.company', function ($query) use ($companyId) {
                            $query->where('company_id', $companyId);
                        });
                    }
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', $status);
                }                
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(cheque_create_date)'), [$startDate, $endDate]);
                }
                /******* fillter query End ****/
                $count = $data->count('id');
                $data = $data->orderby('created_at', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                $sno = $_POST['start'];
                $totalCount = $count;
                $rowReturn = array();
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    //$val['company_name'] = $row['receivedCompany']->name;
                    $val['cheque_create_date'] = date("d/m/Y", strtotime($row->cheque_create_date));
                    $val['bank_name'] = $row['samrddhBank']->bank_name;
                    $val['account_no'] = $row['samrddhAccount']->account_no;
                    $val['cheque_no'] = $row->cheque_no;
                    if ($row->is_use == 1) {
                        $use = 'Yes';
                    } else {
                        $use = 'No';
                    }
                    $val['use'] = $use;
                    $status = 'New';
                    if ($row->status == 1) {
                        $status = 'New';
                    }
                    if ($row->status == 2) {
                        $status = 'Pending';
                    }
                    if ($row->status == 3) {
                        $status = 'cleared';
                    }
                    if ($row->status == 4) {
                        $status = 'Canceled & Re-issued';
                    }
                    if ($row->status == 0) {
                        $status = 'Deleted';
                    }
                    $val['status'] = $status;
                    $cheque_delete_date = '';
                    if ($row->cheque_delete_date != NULL) {
                        $cheque_delete_date = date("d/m/Y", strtotime($row->cheque_delete_date));
                    }
                    $val['cheque_delete_date'] = $cheque_delete_date;
                    $cheque_cancel_date = '';
                    if ($row->cheque_cancel_date != NULL) {
                        $cheque_cancel_date = date("d/m/Y", strtotime($row->cheque_cancel_date));
                    }
                    $val['cheque_cancel_date'] = $cheque_cancel_date;
                    $val['remark_cancel'] = $row->remark_cancel;
                    $btn = '';
                    $url = URL::to("admin/cheque/delete?id=" . $row->id . "");
                    $url2 = URL::to("admin/cheque/cancel?id=" . $row->id . "");
                    $url4 = URL::to("admin/cheque/view/" . $row->id . "");
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    if ($row->status > 0 && $row->status < 4) {
                        if ($row->is_use == 0) {
                            $btn .= '<a class="dropdown-item" href="' . $url . '" title="Cheque Delete"><i class="icon-trash-alt  mr-2"></i>Cheque Delete</a>  ';
                        }
                        if ($row->status != 3 && $row->is_use == 1) {
                            $btn .= '<a class="dropdown-item" href="' . $url2 . '" title="Cheque Cancel"><i class="icon-cancel-circle2  mr-2"></i>Cancel</a>  ';
                        }
                    }
                    $btn .= '<a class="dropdown-item" href="' . $url4 . '" title="Cheque View"><i class="icon-eye8  mr-2"></i>Cheque View</a>';
                    $btn .= '</div></div></div>';
                    $val['action'] = $btn;
                    $rowReturn[] = $val;
                }
                $output = array("branch_id" => Auth::user()->branch_id, "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
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
    /**
    * Delete cheque.
    * Route: /admin/cheque/delete
    * Method: get 
    * @return  array()  Response
    */
    public function delete()
    {
        if (check_my_permission(Auth::user()->id, "97") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Cheque Management | Delete Cheque';
        $data['bank'] = SamraddhBank::where('status', 1)->get(['id', 'bank_name']);
        $data['account'] = SamraddhBankAccount::where('status', 1)->get(['id', 'account_no']);
        $data['cheque'] = SamraddhCheque::where('is_use', 0)->where('status', 1)->get(['id', 'cheque_no']);
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $data['chequeDetail'] = SamraddhCheque::where('id', $id)->first(['id', 'cheque_no', 'bank_id', 'account_id']);
            
         

        }
        return view('templates.admin.cheque_management.delete', $data);
    }
    /**
    * Route: /admin/cheque/delete
    * Method: Post 
    * @param  \Illuminate\Http\Request  $request
    * add cheque.
    * @return  array()  Response
    */
    public function chequeDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            $cheque['status'] = 0;
            $cheque['cheque_delete_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->cheque_date)));
            $chequeupdate = SamraddhCheque::find($request->cheque_no);
            $chequeupdate->update($cheque);
            ;
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()->route('admin.cheque_list')->with('success', 'Cheque deleted successfully!');
    }
    /**
    * Get cheque list  according to account.
    * Route: ajax call from - /admin/cheque/cancel
    * Method: Post 
    * @param  \Illuminate\Http\Request  $request
    * @return JSON array
    */
    public function bankChequeList(Request $request)
    {
        $chequeListAcc = SamraddhCheque::where('is_use', 0)->where('status', '!=', 0)->where('account_id', $request->account_id)->get(['id', 'cheque_no']);
        $return_array = compact('chequeListAcc');
        return json_encode($return_array);
    }
    /**
    * Cancel cheque.
    * Route: /admin/cheque/cancel
    * Method: get 
    * @return  array()  Response
    */
    public function cancel()
    {
        if (check_my_permission(Auth::user()->id, "98") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Cheque Management | Cancel Cheque';
        $data['bank'] = SamraddhBank::where('status', 1)->get(['id', 'bank_name']);
        //$data['account'] = SamraddhBankAccount::where('status',1)->get(['id','account_no']);
        //$data['cheque'] = SamraddhCheque::where('is_use',1)->where('status','>',1)->where('status','<',4)->get(['id','cheque_no']);
        // $data['chequeIssue'] = SamraddhCheque::where('is_use',0)->where('status','!=',0)->get(['id','cheque_no']);
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $data['chequeDetail'] = SamraddhCheque::where('id', $id)->first(['id', 'cheque_no', 'bank_id', 'account_id']);
        }
        return view('templates.admin.cheque_management.cancel', $data);
    }
    /**
    * Route: /admin/cheque/delete
    * Method: Post 
    * @param  \Illuminate\Http\Request  $request
    * add cheque.
    * @return  array()  Response
    */
    public function chequeCancel(Request $request)
    {
        DB::beginTransaction();
        try {
            $getChk = SamraddhCheque::where('id', $request->issue_cheque_no)->where('is_use', 1)->where('status', '>', 1)->where('status', '<', 4)->get();
            if (count($getChk) > 0) {
                return back()->with('alert', 'Issued cheque number already used.');
            }
            $advice = SamraddhChequeIssue::where('cheque_id', $request->cheque_no)->where('status', 1)->first(['advice_id']);
            $cheque['status'] = 4;
            $cheque['cheque_cancel_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->cheque_date)));
            $cheque['remark_cancel'] = $request->remark;
            $cheque['cheque_reissue_id'] = $request->issue_cheque_no;
            $chequeupdate = SamraddhCheque::find($request->cheque_no);
            $chequeupdate->update($cheque);
            $updateIssue = SamraddhChequeIssue::where('cheque_id', $request->cheque_no)->where('status', 1)->update(['cheque_cancel_date' => date("Y-m-d", strtotime(str_replace('/', '-', $request->cheque_date))), 'status' => 0]);
            $getChk = SamraddhCheque::where('id', $request->issue_cheque_no)->get();
            if (count($getChk) > 0) {
            }
            /********** Cheque reissue ***********/
            $chequeIssue['status'] = 2;
            $chequeIssue['is_use'] = 1;
            $chequeupdateIssue = SamraddhCheque::find($request->issue_cheque_no);
            $chequeupdateIssue->update($chequeIssue);
            $issue['cheque_id'] = $request->issue_cheque_no;
            $issue['cheque_issue_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->cheque_date)));
            $issue['advice_id'] = $advice->advice_id;
            $issue['created_at'] = $request->created_at;
            $issueCreate = SamraddhChequeIssue::create($issue);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()->route('admin.cheque_list')->with('success', 'Cheque canceled and re-issue successfully!');
    }
    /**
    *  Get cheque list  according to account.
    * Route: ajax call from - /admin/cheque/delete
    * Method: Post 
    * @param  \Illuminate\Http\Request  $request
    * @return JSON array
    */
    public function getBankAccountCheque(Request $request)
    {
        $chequeListAcc = SamraddhCheque::where('is_use', 0)->where('status', '!=', 0)->where('account_id', $request->account_id)->get(['id', 'cheque_no']);
        $return_array = compact('chequeListAcc');
        return json_encode($return_array);
    }
    /**
    * Get cheque list  according to account.
    * Route: ajax call from - /admin/cheque/cancel
    * Method: Post 
    * @param  \Illuminate\Http\Request  $request
    * @return JSON array
    */
    public function getChequeForCancel(Request $request)
    {
        $chequeListAcc = SamraddhCheque::where('is_use', 1)->where('status', 2)->where('account_id', $request->account_id)->get(['id', 'cheque_no']);
        $return_array = compact('chequeListAcc');
        return json_encode($return_array);
    }
    /**
    * Show  cheque detail.
    * Route: /admin/cheque/view
    * Method: get 
    * @return  array()  Response
    */
    public function chequeView($id)
    {
        $data['title'] = 'Cheque Management | Cheque Detail';
        $data['cheque'] = SamraddhCheque::select('id', 'cheque_no', 'cheque_create_date', 'is_use', 'status', 'cheque_delete_date', 'cheque_cancel_date', 'remark_cancel', 'bank_id', 'account_id')->with(['samrddhBank' => function ($query) {
            $query->select('id', 'bank_name'); }])->with(['samrddhAccount' => function ($query) {
                $query->select('id', 'account_no'); }])->where('id', $id)->first();
        return view('templates.admin.cheque_management.view', $data);
    }
    /************************************* Received Cheque Management *******************************/
    /**
    * Show Cheque.
    * Route: /admin/received/cheque
    * Method: get 
    * @return  array()  Response
    */
    public function receivedChequeList()
    {
        if (check_my_permission(Auth::user()->id, "101") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Received Cheque Management | Cheque/UTR  List';
        $data['branch'] = \App\Models\Branch::select('id', 'name')->where('status', 1)->get();
        return view('templates.admin.cheque_management.received_list', $data);
    }
    /**
    * Show Add cheque.
    * Route: /admin/received/cheque/add
    * Method: get 
    * @return  array()  Response
    */
    public function receivedChequeAdd()
    {
        if (check_my_permission(Auth::user()->id, "100") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Received Cheque Management | Add Cheque/UTR ';
        $data['bank'] = SamraddhBank::select('id', 'bank_name')->where('status', 1)->get();
        if (Auth::user()->branch_id > 0) {
            $id = Auth::user()->branch_id;
            $data['branch'] = \App\Models\Branch::select('id', 'state_id', 'name')->where('status', 1)->where('id', $id)->get();
        } else {
            $data['branch'] = \App\Models\Branch::select('id', 'state_id', 'name')->where('status', 1)->get();
        }
        return view('templates.admin.cheque_management.received_add', $data);
    }
    /**
    * Show edit cheque.
    * Route: /admin/received/cheque/add
    * Method: get 
    * @return  array()  Response
    */
    public function receivedChequeEdit($id)
    {
        $data['title'] = 'Received Cheque Management | Edit Cheque/UTR ';
        $data['bank'] = SamraddhBank::where('status', 1)->get();
        $data['branch'] = \App\Models\Branch::where('status', 1)->get();
        $data['cheque'] = \App\Models\ReceivedCheque::where('id', $id)->first();
        return view('templates.admin.cheque_management.received_edit', $data);
    }
    /**
    * Get cheque list
    * Route: ajax call from - /admin/received/cheque
    * Method: Post 
    * @param  \Illuminate\Http\Request  $request
    * @return JSON array
    */
    public function receivedChequeListing(Request $request)
    {

       
        if ($request->ajax() && check_my_permission(Auth::user()->id, "101") == "1") 
        {
            // fillter array 
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            //    print_r($arrFormData);die;
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $date_application = $arrFormData['create_application_date'];
                $date_created = $arrFormData['created_at'];
                //print_r($date_application);die;
                $data = \App\Models\ReceivedCheque::has('receivedCompany')->select('company_id','id', 'cheque_create_date', 'cheque_no', 'bank_name', 'branch_name', 'account_holder_name', 'cheque_account_no', 'remark', 'amount', 'cheque_deposit_date', 'status', 'clearing_date', 'deposit_bank_id', 'deposit_account_id', 'branch_id')
                    ->with(['receivedBank' => function ($query) {
                        $query->select('id', 'bank_name'); }])
                    ->with(['receivedAccount' => function ($query) {
                        $query->select('id', 'account_no'); }])
                    ->with(['receivedBranch' => function ($query) {
                        $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone'); }])
                    ->with(['receivedChequePayment' => function ($query) {
                        $query->select('id', 'cheque_id', 'created_at'); }])
                        ->with( ['receivedCompany' => function($query)
                        { $query->select('id', 'name');}]);                       
                       
                /******* fillter query start ****/
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $branch_id = $arrFormData['branch_id'];
                    if ($branch_id != '0') {
                        $data = $data->where('branch_id', $branch_id);
                    }
                }
                if (isset($arrFormData['company_id']) && $arrFormData['company_id'] != '') {
                    $company_id = $arrFormData['company_id'];
                    if ($company_id != '0') {
                        $data = $data->where('company_id', $company_id);
                    }
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', $status);
                }
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(cheque_create_date)'), [$startDate, $endDate]);
                }
                $data1 = $data->orderby('created_at', 'DESC')->get();
                $count = count($data1);
                $data = $data->orderby('created_at', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                /*
                if (Auth::user()->branch_id > 0) {
                    $totalCount = \App\Models\ReceivedCheque::where('branch_id', '=', Auth::user()->branch_id)->count('id');
                } else {
                    $totalCount = \App\Models\ReceivedCheque::with(['receivedBank' => function ($query) {
                        $query->has('receivedCompany')->with('receivedCompany');
                    }])->count('id');
                }
                */
                $totalCount = $count;
                $sno = $_POST['start'];
                $rowReturn = array();
              
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company_name']=$row['receivedCompany']->name;
                    // $val['branch_name'] = $row['receivedBranch']->name;
                    // $val['branch_code'] = $row['receivedBranch']->branch_code;
                    // $val['sector_name'] = $row['receivedBranch']->sector;
                    // $val['region_name'] = $row['receivedBranch']->regan;
                    // $val['zone_name'] = $row['receivedBranch']->zone;
                    $val['cheque_create_date'] = date("d/m/Y", strtotime($row->cheque_create_date));
                    $val['cheque_no'] = $row->cheque_no;
                    $val['bank_name'] = $row->bank_name;
                    $val['bank_branch_name'] = $row->branch_name;
                    $val['account_holder_name'] = $row->account_holder_name;
                    $val['cheque_account_no'] = $row->cheque_account_no;
                    $val['remark'] = $row->remark;
                    $val['amount'] = number_format((float) $row->amount, 2, '.', '');
                    $val['deposit_bank_id'] = $row['receivedBank']->bank_name;
                    $val['deposit_account_id'] = $row['receivedAccount']->account_no;
                    $val['cheque_deposit_date'] = date("d/m/Y", strtotime($row->cheque_deposit_date));
                    if ($row->status == 3) {
                        if ($row['receivedChequePayment']) {
                            $val['used_date'] = date("d/m/Y", strtotime($row['receivedChequePayment']->created_at));
                        } else {
                            $val['used_date'] = "N/A";
                        }
                    } else {
                        $val['used_date'] = 'N/A';
                    }
                    if ($row->clearing_date) {
                        $val['clearing_date'] = date("d/m/Y", strtotime($row->clearing_date));
                    } else {
                        $val['clearing_date'] = 'N/A';
                    }
                    $status = 'New';
                    if ($row->status == 1) {
                        $status = 'Pending';
                    }
                    if ($row->status == 2) {
                        $status = 'Apporved';
                    }
                    if ($row->status == 3) {
                        $status = 'cleared';
                    }
                    if ($row->status == 0) {
                        $status = 'Deleted';
                    }
                    $val['status'] = $status;
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    // $url = URL::to("admin/received/cheque/delete/".$row->id."?date=".$date_application);  
                    $url2 = URL::to("admin/received/cheque/edit/" . $row->id . "");
                    //$url3 = URL::to("admin/received/cheque/approved/".$row->id."?date=".$date_application);
                    $url4 = URL::to("admin/received/cheque/view/" . $row->id . "");
                    if ($row->status == 1) {
                        $btn .= '<a class="dropdown-item"  href="javascript:void(0)" title="Cheque Approve" onclick=delete_cheque("' . $row->id . '");><i class="icon-trash-alt  mr-2"></i>Cheque Delete</a>  ';
                        $btn .= '<a class="dropdown-item" href="' . $url2 . '" title="Cheque Edit"><i class="icon-pencil7  mr-2"></i>Cheque Edit</a>  ';
                        //$btn .= '<a class="dropdown-item" href="javascript:void(0)" title="Cheque Approve" onclick=approve("'.$row->id.'");><i class="icon-checkmark4  mr-2"></i>Cheque Approve</a>  ';
                        $btn .= '<a class="dropdown-item" href="javascript:void(0)" data="' . $row->id . '" title="Cheque Approve" onclick=setDate("' . $row->id . '");><i class="icon-checkmark4  mr-2"></i>Cheque Approve</a>  ';
                    }
                    $btn .= '<a class="dropdown-item" href="' . $url4 . '" title="Cheque View"><i class="icon-eye8  mr-2"></i>Cheque View</a>  ';
                    $btn .= '</div></div></div>';
                    $val['action'] = $btn;
                    $rowReturn[] = $val;
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
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
    * Show approved cheque.
    * Route: /admin/received/cheque/approved
    * Method: get 
    * @return  array()  Response
    */
    public function receivedChequeApproved($id)
    {
        DB::beginTransaction();
        try {
            $approved['status'] = 2;
            $approved['cheque_approved_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $_GET['date'])));
            $approvedupdate = \App\Models\ReceivedCheque::find($id);
            $approvedupdate->update($approved);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()->route('admin.received.cheque_list')->with('success', 'Cheque/UTR  Apporved Successfully');
    }
    /**
    * Show approved cheque.
    * Route: /admin/received/cheque/approved
    * Method: get 
    * @return  array()  Response
    */
    public function receivedChequeDelete($id)
    {
        DB::beginTransaction();
        try {
            $delete['status'] = 0;
            $delete['cheque_delete_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $_GET['date'])));
            $deleteupdate = \App\Models\ReceivedCheque::find($id);
            $deleteupdate->update($delete);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()->route('admin.received.cheque_list')->with('success', 'Cheque/UTR  Deleted Successfully');
    }
    public function companyDroupDown(Request $request)
    {
        //
    }
    /**
    * Route: /admin/received/cheque/add
    * Method: Post 
    * @param  \Illuminate\Http\Request  $request
    * add cheque.
    * @return  array()  Response
    */
    public function receivedChequeSave(Request $request)
    {
      
        DB::beginTransaction();
        try {
            $getcChequeDetail = \App\Models\ReceivedCheque::where('cheque_no', $request->cheque_number)->where('cheque_account_no', $request->account_no)->where('status', '!=', 0)->get();
            if (count($getcChequeDetail) > 0) {
                return back()->with('alert', 'Cheque number already created with same account number');
            }
        
            $cheque['branch_id'] = $request->branch_id;
            $cheque['bank_name'] = $request->bank_name;
            $cheque['branch_name'] = $request->bank_branch_name;
            $cheque['cheque_no'] = $request->cheque_number;
            $cheque['cheque_account_no'] = $request->account_no;
            $cheque['account_holder_name'] = $request->account_holder;
            $cheque['cheque_create_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->cheque_date)));
            $cheque['remark'] = $request->remark;
            $cheque['amount'] = $request->amount;
            $cheque['deposit_bank_id'] = $request->bank_id;
            $cheque['deposit_account_id'] = $request->account_id;
            $cheque['cheque_deposit_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->deposit_cheque_date)));
            $cheque['created_at'] =  date('Y-m-d H:i:s',strtotime(convertDate($request->created_at)));
            $cheque['company_id'] = $request->company_id;
            $chequeadd = \App\Models\ReceivedCheque::create($cheque);
            $bookId = $chequeadd->id;
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()->route('admin.received.cheque_list')->with('success', 'Cheque/UTR  created successfully!');
    }
    public function receivedChequeUpdate(Request $request)
    {
        // print_r($_POST);die;
        DB::beginTransaction();
        try {
            $getcChequeDetail = \App\Models\ReceivedCheque::where('cheque_no', $request->cheque_number)->where('cheque_account_no', $request->account_no)->where('id', '!=', $request->id)->where('status', '!=', 0)->get();
            if (count($getcChequeDetail) > 0) {
                return back()->with('alert', 'Cheque/UTR  number already created with same account number');
            }
            $cheque['branch_id'] = $request->branch_id;
            $cheque['bank_name'] = $request->bank_name;
            $cheque['branch_name'] = $request->bank_branch_name;
            $cheque['cheque_no'] = $request->cheque_number;
            $cheque['cheque_account_no'] = $request->account_no;
            $cheque['account_holder_name'] = $request->account_holder;
            $cheque['cheque_create_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->cheque_date)));
            $cheque['remark'] = $request->remark;
            $cheque['amount'] = $request->amount;
            $cheque['deposit_bank_id'] = $request->bank_id;
            $cheque['deposit_account_id'] = $request->account_id;
            $cheque['company_id'] = $request->company_id;
            $cheque['cheque_deposit_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->deposit_cheque_date)));
            $chequeupdate = \App\Models\ReceivedCheque::find($request->id);
            $chequeupdate->update($cheque);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()->route('admin.received.cheque_list')->with('success', 'Cheque/UTR  updeted successfully!');
    }
    /**
    * Show Add cheque.
    * Route: /admin/received/cheque/view
    * Method: get 
    * @return  array()  Response
    */
    public function receivedChequeView($id)
    {
        $data['title'] = 'Received Cheque Management | Cheque/UTR  Detail';
        $data['cheque'] = \App\Models\ReceivedCheque::with(['receivedBank' => function ($query) {
            $query->select('id', 'bank_name'); }])->with(['receivedAccount' => function ($query) {
                $query->select('id', 'account_no'); }])->with(['receivedBranch' => function ($query) {
                $query->select('id', 'name'); }])->where('id', $id)->first();
        //print_r($data['cheque']);die;
        return view('templates.admin.cheque_management.received_view', $data);
    }
    public function chequeCancelView($id)
    {
        $data['title'] = 'Cheque Management | Cancel Cheque Detail';
        $data['cheque'] = SamraddhCheque::with(['samrddhBank' => function ($query) {
            $query->select('id', 'bank_name'); }])->with(['samrddhAccount' => function ($query) {
                $query->select('id', 'account_no'); }])->where('id', $id)->first();
        $chequeIssues = SamraddhChequeIssue::where("cheque_id", $id)->orderby("id", "desc")->first();
        $data['cheque_issue'] = $chequeIssues;
        if (!empty($chequeIssues)) {
            $type = $chequeIssues->type;
            $type_id = $chequeIssues->type_id;
        } else {
            $type = 0;
            $type_id = 0;
        }
        $data['type'] = $type;
        $setData = array();
        if ($type == "1") {
            $fundTransfer = \App\Models\FundTransfer::where('id', $type_id)->first();
            $setData = array("fundTransfer" => $fundTransfer);
        } elseif ($type == "3") {
            // Now Get Records
            $Memberloans = \App\Models\Memberloans::where('id', $type_id)->first();
            $loan_type = $Memberloans->loan_type;
            if ($loan_type == "3") {
                // Group Loan
                $loan_type = "Group Loan";
            } else {
                // Others
                if ($loan_type == "1") {
                    $loan_type = "Personal Loan";
                } elseif ($loan_type == "2") {
                    $loan_type = "Staff Loan";
                } elseif ($loan_type == "4") {
                    $loan_type = "Loan Investment";
                }
                $account_number = $Memberloans->account_number;
                $amount = $Memberloans->amount;
                $associate_member_id = $Memberloans->associate_member_id;
                $applicant_id = $Memberloans->applicant_id;
                $members = \App\Models\Member::where('id', $applicant_id)->first();
                $membersName = $members->first_name;
                $member_id = $members->member_id;
                $associate = \App\Models\Member::where('id', $associate_member_id)->first();
                $associateName = $associate->first_name;
                //$associateCode = $associate->associate_code;
                $associateCode = $associate->associate_no;
                $setData = array("loan_type" => $loan_type, "account_number" => $account_number, "amount" => $amount, "membersName" => $membersName, "member_id" => $member_id, "associateName" => $associateName, "associateCode" => $associateCode);
            }
        } elseif ($type == "4") {
            // Now Get Records
            $MemberSalary = \App\Models\EmployeeSalary::leftJoin('employees', function ($join) {
                $join->on('employee_salary.employee_id', '=', 'employees.id');
            })->where('employee_salary.leaser_id', $type)
                ->get([
                    'employee_salary.id',
                    'employee_salary.actual_transfer_amount',
                    'employees.employee_code',
                    'employees.employee_name'
                ]);
            $setData = array("MemberSalary" => $MemberSalary);
        } elseif ($type == "5") {
            // Now Get Records
            $MemberRent = \App\Models\RentPayment::leftJoin('rent_liabilities', function ($join) {
                $join->on('rent_payments.rent_liability_id', '=', 'rent_liabilities.id');
            })->leftJoin('account_heads', function ($join) {
                $join->on('rent_liabilities.rent_type', '=', 'account_heads.id');
            })->where('rent_payments.ledger_id', $type)
                ->get([
                    'rent_payments.id',
                    'rent_payments.actual_transfer_amount',
                    'rent_liabilities.owner_name',
                    'rent_liabilities.rent_type',
                    'account_heads.sub_head'
                ]);
            $setData = array("MemberRent" => $MemberRent);
        } elseif ($type == "6") {
            $demandAdvice = \App\Models\DemandAdvice::with('expenses', 'branch')->where('id', $id)->first();
            $setData = array("demandAdvice" => $demandAdvice);
        } elseif ($type == "7") {
            $head = \App\Models\AllTransaction::where('head4', $type_id)->first();
            $setData = array("head" => $head);
        } elseif ($type == "8") {
            $ssb = \App\Models\SavingAccountTranscation::where('saving_account_id', $type_id)->where('reference_no', $data['cheque']->cheque_no)->first();
            $setData = array("ssb" => $ssb);
        }
        //echo "<pre/>";
        //print_r($setData);  die;
        $data['setData'] = $setData;
        //echo "<pre/>";
        // print_r($data->funds_transfer );  die;
        return view('templates.admin.cheque_management.cancel_cheque_view', $data);
    }
    public function customReceivedChequeApproved(Request $request)
    {
        $fields = $request->all();
        $id = $fields['id'];
        $input = $fields['input'];
        $date = $fields['date'];
        DB::beginTransaction();
        try {
            $approved['status'] = 2;
            $approved['cheque_approved_date'] = date('Y-m-d'); //date("Y-m-d", strtotime( str_replace('/','-', $date)));
            $approved['clearing_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $input)));
            $approvedupdate = \App\Models\ReceivedCheque::find($id);
            $approvedupdate->update($approved);
            DB::commit();
            $response = array("msg" => "success", "clear_date" => $input);
        } catch (\Exception $ex) {
            DB::rollback();
            $response = array("msg" => "error", "clear_date" => $input);
        }
        echo json_encode($response);
    }
 /**
    *  Get bank  list  according to company.
    * Route: ajax call from - /admin/getBankByCompany
    * Method: Post 
    * @param  \Illuminate\Http\Request  $request
    * @return JSON array
    */
    public function getBankByCompany(Request $request)
    {
        $companyId = $request->company_id;
        if ($companyId != '0' && $companyId != '') {
            $bankList =  SamraddhBank::where('company_id', $companyId)
                ->where('status', 1)
                ->get(['id', 'bank_name', 'status', 'company_id']);
        } else {
            $bankList =  SamraddhBank::has('company')->get(['id', 'bank_name', 'status', 'company_id']);
        }
        $return_array = compact('bankList');
        return json_encode($return_array);
    }


    public function getBanks(Request $request){
        $banks = \App\Models\SamraddhBank::where('status', 1)->where('company_id',$request->company_id)->get();
        $return_array = compact('banks');
        return json_encode($return_array);
    }
}