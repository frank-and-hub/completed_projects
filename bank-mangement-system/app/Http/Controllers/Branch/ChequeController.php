<?php
namespace App\Http\Controllers\Branch;

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
| branch Panel -- Cheque Management ChequeController
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
     * Route: /branch/received/cheque
     * Method: get 
     * @return  array()  Response
     */
    public function index()
    {
        if (!in_array('Cheque List', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }



        $data['title'] = 'Received Cheque Management | Cheque/UTR  List';
        $data['branch'] = \App\Models\Branch::where('status', 1)->get();


        return view('templates.branch.cheque_management.received_list', $data);
    }
    /**
     * Get bank account  according to bank.
     * Route: ajax call from - /branch/cheque/add
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function getBankAccount(Request $request)
    {
        $account = accountListBank($request->bank_id);
        $return_array = compact('account');
        return json_encode($return_array);
    }
    /**
     * Show Add cheque.
     * Route: /branch/received/cheque/add
     * Method: get 
     * @return  array()  Response
     */
    public function receivedChequeAdd()
    {
        if (!in_array('Add Cheque', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }

        $data['title'] = 'Received Cheque Management | Add Cheque/UTR ';
        $data['bank'] = SamraddhBank::where('status', 1)->get(['id', 'bank_name']);
        //$data['branch'] =  \App\Models\Branch::where('status',1)->get();


        return view('templates.branch.cheque_management.received_add', $data);
    }

    /**
     * Get cheque list
     * Route: ajax call from - /branch/received/cheque
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function receivedChequeListing(Request $request)
    {
        if ($request->ajax()) {
        
            // fillter array 
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            
            /******* fillter query start ****/
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {

               // code updated by sourab
                $data = \App\Models\ReceivedCheque::with([
                    'receivedBank:id,bank_name',
                    'receivedCompany:id,name',
                    'receivedAccount:id,account_no',
                    'receivedBranch:id,name,branch_code,sector,regan,zone',
                    'receivedChequePayment:id,cheque_id,created_at'
                ]);
                $getBranchId = getUserBranchId(Auth::user()->id)->id;
                $data = $data->where('branch_id', $getBranchId);
                
                /*if($arrFormData['branch_id'] !=''){
                $branch_id=$arrFormData['branch_id'];
                $data=$data->where('branch_id',$branch_id);
                }*/

                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', $status);
                }
                if ($arrFormData['company_id'] != '') {
                    $company_id = $arrFormData['company_id'];
                    $data = $data->where('company_id', $company_id);
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

                $count = $data->count('id');
                $data = $data->orderby('created_at', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();

                $totalCount = \App\Models\ReceivedCheque::count();

                $sno = $_POST['start'];
                $rowReturn = array();
                // dd($data);

                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company_name'] = $row['receivedCompany']->name;
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

                    if ($row->clearing_date) {
                        $val['clearing_date'] = date("d/m/Y", strtotime($row->clearing_date));
                    } else {
                        $val['clearing_date'] = 'N/A';
                    }

                    if ($row->status == 3) {
                        if ($row['receivedChequePayment']) {
                            $val['used_date'] = date("d/m/Y", strtotime($row['receivedChequePayment']->created_at));
                        } else {
                            $val['used_date'] = "N/A";
                        }

                    } else {
                        $val['used_date'] = 'N/A';
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
                    $btn = '';
                    $url = URL::to("branch/received/cheque/view/" . $row->id . "");

                    $btn = '<a class=" " href="' . $url . '" title="Cheque View"><i class="fas fa-eye text-default"></i></a>  ';
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
     * Route: /branch/received/cheque/add
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * add cheque.
     * @return  array()  Response
     */
    public function receivedChequeSave(Request $request)
    {

      // dd($request);
        DB::beginTransaction();
        try {
            $sId = \App\Models\Branch::where('name', 'like', '%' . Auth::user()->username . '%')->first('state_id');
            $created_at = checkMonthAvailability(date('d'), date('m'), date('Y'), $sId->state_id);
            $getcChequeDetail = \App\Models\ReceivedCheque::where('cheque_no', $request->cheque_number)->where('cheque_account_no', $request->account_no)->where('status', '!=', 0)->get();
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

            $cheque['created_at'] = $created_at;

            $cheque['cheque_deposit_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->deposit_cheque_date)));
            $chequeadd = \App\Models\ReceivedCheque::create($cheque);
            $bookId = $chequeadd->id;


            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()->route('branch.received.cheque_list')->with('success', 'Cheque/UTR  created successfully!');
    }

    /**
     * Show Add cheque.
     * Route: /branch/received/cheque/add
     * Method: get 
     * @return  array()  Response
     */
    public function receivedChequeView($id)
    {
        $data['title'] = 'Received Cheque Management | Cheque/UTR  Detail';
        $data['cheque'] = \App\Models\ReceivedCheque::with(['receivedBank' => function ($query) {
            $query->select('id', 'bank_name'); }])->with(['receivedAccount' => function ($query) {
                $query->select('id', 'account_no'); }])->with(['receivedBranch' => function ($query) {
                $query->select('id', 'name'); }])->with(['receivedCompany' => function ($query) {
                $query->select('id', 'name'); }])->where('id', $id)->first();
        //  print_r($data['cheque']);die;


        return view('templates.branch.cheque_management.received_view', $data);
    }
    public function bankChequeList(Request $request)
    {
        $chequeListAcc = SamraddhCheque::where('is_use', 0)->where('status', '!=', 0)->where('account_id', $request->account_id)->get(['id', 'cheque_no']);
        $return_array = compact('chequeListAcc');
        return json_encode($return_array);
    }
    
    /**
     *  Get bank  list  according to company.
     * Route: ajax call from - /branch/getBankByCompany
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function getBankByCompany(Request $request)
    {
        $bankList = SamraddhBank::where('company_id', $request->company_id)->where('status', 1)->get(['id', 'bank_name', 'status', 'company_id']);
        $return_array = compact('bankList');
        return json_encode($return_array);
    }
}