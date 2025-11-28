<?php
namespace App\Http\Controllers\Admin\Shareholder;
use Illuminate\Http\Request;
use Auth;
use App\Models\Settings;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\SamraddhBank;
use App\Models\Member;
use App\Models\AccountHeads;
use App\Models\ShareHolder;
use Illuminate\Support\Facades\Schema;
use Validator;
use Carbon\Carbon;
use DB;
use URL;
use Session;
use Image;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Admin\CommanController;
use App\Models\SamraddhCheque;
class ShareHolderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    // Cash In Hand Table
    public function shareholderReport()
    {
        if (check_my_permission(Auth::user()->id, "47") != "1")
        {
            return redirect()
                ->route('admin.dashboard');
        }
		/* $data = ShareHolder::with('member')->orderBy('id', 'asc');
			if(Auth::user()->branch_id >0){
			   $branch_id=Auth::user()->branch_id;
		       $data = $data->whereHas('member', function ($query) use ($branch_id) {
			    $query->where('members.branch_id',$branch_id); 
			   });
		    }
		$data = $data->get();
		echo '<pre>';
		print_r($data->toArray());
		exit;*/
        $data['title'] = "Shareholder/Director | List";
        return view('templates.admin.shareholder.shareholderReport', $data);
    }
    public function createShareHolder()
    {
        if (check_my_permission(Auth::user()->id, "46") != "1")
        {
            return redirect()
                ->route('admin.dashboard');
        }
        $data['title'] = "Shareholder/Director | Add ";
        return view('templates.admin.shareholder.create', $data);
    }
    /*Verify Member*/
    public function verifyMember(Request $request)
    {
        $m_id = $request->memberid;
        $resCount = 0;
        $name = '';
        $member_id = '';
        $member = Member::where('member_id', $m_id)->first(['first_name', 'last_name', 'member_id','company_id']);
        if ($member)
        {
                $name = $member->first_name ;
                $fullname = $member->first_name.' '.$member->last_name ;
                $member_id = $member->member_id;
                $company_id = $member->company_id;
                $resCount = 1;
        }else{
            $name = null ;
            $fullname = null ;
            $member_id = null;
            $company_id = null;
            $resCount = 0;
        }
        $return_array = compact('resCount', 'name', 'member_id','fullname','company_id');
        return json_encode($return_array);
    }
    /*Store ShareHolder*/
    public function storeShareHolder(Request $request)
    {
        $head_id = AccountHeads::orderBy('head_id', 'desc')->first('head_id');
        $m_id = Member::where('member_id', $request->member_id)->first('id');
        // dd($m_id,$request->all());
        $rules = ['name' => 'required', 'father_name' => 'required', 'type' => 'required', 'address' => 'required', 'pan_no' => 'required', 'aadhar_no' => 'required', 'contact_no' => 'required', 'bank_name' => 'required', 'branch_name' => 'required', 'account_number' => 'required', 'ifsc_code' => 'required',  'remark' => 'required'];
        $customMessages = ['required' => 'The :attribute field is required.'];
        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try        
        {
            $globaldate = $request->created_at;
            $select_date = $request->date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $parentHeadDetail = AccountHeads::where('head_id', $request->type)->first();
            $head_data['sub_head'] = $request->name;
            $head_data['head_id'] = $head_id->head_id + 1;
            $head_data['labels'] = 4;
            $head_data['parent_id'] = $request->type;
            $head_data['parentId_auto_id'] = $parentHeadDetail->id;
            $head_data['cr_nature'] = $parentHeadDetail->cr_nature;
            $head_data['dr_nature'] = $parentHeadDetail->dr_nature;
            $head_data['is_move'] = 1;
            $head_data['status'] = 0;
            $head_data['company_id'] = $request->company_id;
            $head_data['created_at'] = $created_at;
            $head_data['updated_at'] = $updated_at;
            $headCreate = AccountHeads::create($head_data);
            $account_head = AccountHeads::where('id', $headCreate->id)->first();
            $data['name'] = $request->name;
            $data['father_name'] = $request->father_name;
            $data['head_id'] = $account_head->head_id;
            $data['type'] = $request->type;
            $data['address'] = $request->address;
            $data['pan_card'] = $request->pan_no;
            $data['aadhar_card'] = $request->aadhar_no;
            $data['contact'] = $request->contact_no;
            $data['bank_name'] = $request->bank_name;
            $data['branch_name'] = $request->branch_name;
            $data['account_number'] = $request->account_number;
            $data['ifsc_code'] = $request->ifsc_code;
            if($m_id)
            {
                $data['member_id'] = $m_id->id;
            }
            else{
                $data['member_id'] =NULL;
            }
            $data['ssb_account'] = $request->ssb_account;
            $data['ssb_account_id'] = $request->ssb_id;
            $data['remark'] = $request->remark;
            $data['email'] = $request->email;
            $data['status'] = 1;
            $data['is_deleted'] = 1;
            $data['created_at'] = $created_at;
            $data['updated_at'] = $updated_at;
            $data['firm_name'] = $request->firm_name;
			$data['company_id'] = $request->company_id;
            $ShareHolder = ShareHolder::create($data);
            DB::commit();
        }
        catch(\Exception $ex)
        {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()
            ->route('admin.shareholder')
            ->with('success', 'Head Created Successfully!');
    }
    public function reportListing(Request $request)
    {
        if ($request->ajax())
        {
            // fillter array 
            $arrFormData = array();
            if (!empty($_POST['searchform']))
            {
                foreach ($_POST['searchform'] as $frm_data)
                {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
            {
                //print_r($arrFormData);die;  
                $data = ShareHolder::select( 'id','type','name','father_name','address','pan_card','aadhar_card','firm_name','email','contact','bank_name','branch_name','account_number','current_balance','ifsc_code','member_id','ssb_account','remark','created_at','head_id','company_id')->with(['member'=>function($query){
                    $query->select('id','member_id','first_name','last_name');
                }])->with(['account_head'=>function($q){
                    $q->select('id','head_id','labels');
                }])->with(['company'=>function($q){
                    $q->select('id','name','short_name');
                }])->where('is_deleted',1);	
               	
                if(Auth::user()->branch_id >0){
                $branch_id=Auth::user()->branch_id;
                $data = $data->whereHas('member', function ($query) use ($branch_id) {
                    $query->where('members.branch_id',$branch_id); 
                });
                }
                /******* fillter query start ****/
                    if ($arrFormData['type'] != '')
                    {
                        $type = $arrFormData['type'];
                        $data = $data->where('type', $type);
                    }
                    if ($arrFormData['company_id'] != '')
                    {
                        $company = $arrFormData['company_id'];
                        $data = $data->where('company_id', $company);
                    }
				        $data = $data->where('status',1);
               
                /******* fillter query End ****/
                $data1=$data->count('id');
                $count=$data1;
                $data=$data->offset($_POST['start'])->limit($_POST['length'])->orderBy('created_at','DESC')->get();	
                // $total  = DemandAdvice::with(['investment' => function($q){ $q->select('id','member_id')->with('associateMember','member','ssb'); } ])->with('expenses','branch')->where('is_deleted',0)->get();
                $totalCount  = $count;
                $sno=$_POST['start'];
                $rowReturn = array(); 
                // dd($data);
                foreach($data as $row)
                {
                    $type = 'N/A';
                    if ($row->type == 15)                
                    {
                        $type = 'Shareholder';
                    }
                    if ($row->type == 19)
                    {
                        $type = 'Director';
                    }
                    $sno++;
                    $val['DT_RowIndex']=$sno;
                    $val['company'] = $row['company']->short_name;
                    // pd($row->toArray());
                    $val['type'] = $type;
                    $val['name'] = $row->name;
                    $val['father_name'] = $row->father_name;
                    $val['address'] = $row->address;
                    $val['pan_card'] = $row->pan_card;
                    $val['aadhar_card'] = $row->aadhar_card;
                    $firm = 'N/A';
                    if (!empty($row->firm_name))
                    {
                        $firm =  $row->firm_name;
                    }   
                    $val['firm_name'] = $firm;
                    $email = 'N/A';
                    if (!empty($row->email))
                    {
                        $email =  $row->email;
                    }   
                    $val['email'] = $email;
                    $val['contact'] = $row->contact;
                    $val['bank_name'] = $row->bank_name;
                    $val['branch_name'] = $row->branch_name;
                    $val['account_number'] = $row->account_number;
                    $val['current_balance'] = number_format((float)$row->current_balance, 2, '.', '');
                    $val['ifsc_code'] = $row->ifsc_code;
                    if(isset($row['member']->member_id))
                    {
                        $val['member_id'] = $row['member']->member_id;
                    }
                    else{
                        $val['member_id'] = 'N/A';
                    }
                    if(isset($row->ssb_account))
                    {
                        $val['ssb_account'] = $row->ssb_account;
                    }
                    else{
                        $val['ssb_account'] = 'N/A';
                    }
                    $val['remark'] = $row->remark;
                    $val['created_at'] = date("d/m/Y", strtotime($row->created_at));
                    $head_id = $row['account_head'];
                    $url = URL::to("admin/shareholder_director/edit/" . $row->type . '/' . $row->id . "");
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $btn .= '<a class="dropdown-item" href="' . $url . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                    $statusUrl = URL::to("admin/share-holder/updateStatus/" . $row->id . '/' . $row->head_id . "");
                    $headLedger = URL::to("admin/account_head_ledger/" . $row->head_id . '/' . $head_id->labels . "");
                    $btn .= '<a class="dropdown-item" href="' . $headLedger . '" target="blank"><i class="icon-list mr-2"></i>Transactions</a>';
                    if ($row->status == 0)
                    {
                        $btn .= '<button class="dropdown-item" href="#" title="Ledger  Detail" onclick="statusUpdate(' . $row->id . ',' . $row->head_id . ')"><i class="icon-checkmark4 mr-2"></i>Active</button>  ';
                    }
                    else
                    {
                        $btn .= '<button class="dropdown-item" href="#" title="Ledger  Detail" onclick="statusUpdate(' . $row->id . ',' . $row->head_id . ')"><i class="icon-checkmark4 mr-2"></i>Deactive</button>  ';
                    }
                    $btn .= '</div></div></div>';
                    $val['action'] = $btn;
                    $rowReturn[] = $val; 
                }
                $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
                return json_encode($output);
                }
            else{
                $output = array(
                    "draw" =>0,
                    "recordsTotal" => 0,
                    "recordsFiltered" =>0,
                    "data" => 0,
                );
                return json_encode($output);
            }
         }
    }
    public function verifyssbAccount(Request $request)
    {
        if ($request->member_id)
        {
            $member_id = Member::where('member_id', $request->member_id)
                ->first('id');
            $data = getMemberSsbAccountDetail($member_id->id);
            return json_encode($data);
        }
        else
        {
            return json_encode(0);
        }
    }
    public function edit($type, $id)
    {
        $data['title'] = "Director | Edit ";
        if ($type == 15)
        {
            $data['title'] = "Shareholder | Edit ";
        }
        $data['data'] = ShareHolder::with('company:id,name','short_name')->where('id', $id)->first();
        dd($data['data']);
        $data['member'] = getMemberData($data['data']->member_id);
        
        // dd( $data['data']);
        return view('templates.admin.shareholder.edit', $data);
    }
    public function update(Request $request)
    {
        $m_id =NULL;
        if($request->member_id)
        {
            $mid = Member::where('member_id', $request->member_id)->first('id');
            $m_id =$mid->id;
        } 
        try
        {
            $head_data = $request->name;
            AccountHeads::where('head_id', $request->head_id)->update(['sub_head' => $head_data, 'company_id' => $request->company_id]);
            $data['name'] = $request->name;
            $data['father_name'] = $request->father_name;
            $data['type'] = $request->type;
            $data['address'] = $request->address;
            $data['pan_card'] = $request->pan_no;
            $data['aadhar_card'] = $request->aadhar_no;
            $data['contact'] = $request->contact_no;
            $data['bank_name'] = $request->bank_name;
            $data['branch_name'] = $request->branch_name;
            $data['account_number'] = $request->account_number;
            $data['ifsc_code'] = $request->ifsc_code;
            $data['member_id'] = $m_id;
            $data['ssb_account'] = $request->ssb_account;
            $data['ssb_account_id'] = $request->ssb_id;
            $data['remark'] = $request->remark;
            $data['email'] = $request->email;
            $data['firm_name'] = $request->firm_name;
            $data['company_id'] = $request->company_id;
            ShareHolder::where('id', $request->id)
                ->update($data);
            DB::commit();
        }
        catch(\Exception $ex)
        {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());        
        }
        return redirect()
            ->route('admin.shareholder')
            ->with('success', 'Head Update Successfully!');
    }
    public function updateStatus(Request $request)
    {
        //  print_r($_POST);die; 
        $headStatus = AccountHeads::select('status', 'id')->where('head_id', $request->head_id)
            ->first();
        $holderStatus = ShareHolder::select('status', 'id')->where('id', $request->id)
            ->first();
        $updateholderStatus = ShareHolder::findOrFail($request->id);
        $updateStatus = AccountHeads::findOrFail($headStatus->id);
        if ($headStatus->status == 0)
        {
            $updateStatus->status = 1;
        }
        else        
        {
            $updateStatus->status = 0;
        }
        if ($holderStatus->status == 0)        
        {
            $updateholderStatus->status = 1;
        }
        else
        {
            $updateholderStatus->status = 0;
        }
        $updateStatus = $updateStatus->save();
        $updateholderStatus = $updateholderStatus->save();
        $message = [$updateStatus, $updateholderStatus];
        return response()->json($message);
    }
    public function transfer_share()
    {
        if (check_my_permission(Auth::user()->id, "51") != "1")
        {
            return redirect()
                ->route('admin.dashboard');
        }
        $data['title'] = "Share holder | Transfer Share Holder";
        $data['shareholder'] =  ShareHolder::select('id','name')->where('type', 15)->get();
        return view('templates.admin.shareholder.transfer_share', $data);
    }
    public function get_share_holder_detail(Request $request)    
    {
        $id = $request->id;
		//die($id);
        $data['shareholder'] =  ShareHolder::select('id','name','father_name','address','address','aadhar_card','pan_card','created_at','amount','current_balance','company_id')->where('id', $id)->first();
        $data['member'] = Member::select('id','member_id','first_name','last_name')->where('id', $data['shareholder']->member_id)
            ->first();        
        $data['ssb'] = \App\Models\SavingAccount::select('id','member_id','account_no')->where('id', $data['shareholder']->ssb_account_id)
            ->first();
        $data['rgister_date'] = date("d/m/Y", strtotime($data['shareholder']->created_at));
        return response()
            ->json($data);
    }
    public function headDetailGetAll(Request $request)    
    {
        $id = $request->id;
        $data['shareholder'] = AccountHeads::where('head_id', $id)->first(); 
        $data['rgister_date'] = date("d/m/Y", strtotime($data['shareholder']->created_at));
        return response()
            ->json($data);
    }
    public function share_holder_deposit_payment()
    {
        if (check_my_permission(Auth::user()->id, "50") != "1")
        {
            return redirect()
                ->route('admin.dashboard');
        }
        $data['title'] = "ShareHolder | Deposit Payment";
        $data['shareholder'] = ShareHolder::select('id','name')->where('type', 15)->where('status', 1)
            ->get();
        $data['branches'] = Branch::select('id','name','branch_code')->where('status', 1)->get();
        $data['banks'] = SamraddhBank::select('id','bank_name')->with(['bankAccount'=>function($q){
            $q->select('id', 'account_no', 'ifsc_code', 'bank_id', 'branch_name');
        }])->where('status', 1)
                    ->get();
        return view('templates.admin.shareholder.deposit_share_payment', $data);
    }
    public function director_deposit_payment()
    {
        if (check_my_permission(Auth::user()->id, "48") != "1")
        {
            return redirect()
                ->route('admin.dashboard');
        }
        $data['title'] = "Director | Deposit Payment";
        $data['directors'] = ShareHolder::select('id','name')->where('type', 19)->where('status', 1)
            ->get(); 
        $data['branches'] = Branch::select('id','name','branch_code')->where('status', 1)->get();
        $data['branchesbank'] =Branch::select('id','name','branch_code')->where('id',29)->where('status', 1)->get();
        $data['banks'] =SamraddhBank::select('id','bank_name')->with(['bankAccount'=>function($q){
            $q->select('id', 'account_no', 'ifsc_code', 'bank_id', 'branch_name');
        }])->where('status', 1)
            ->get();
        return view('templates.admin.shareholder.deposit_director_payment', $data);
    }
    public function director_withdrawal_payment()
    {
        if (check_my_permission(Auth::user()->id, "49") != "1")        
        {
            return redirect()
                ->route('admin.dashboard');
        }
        $data['title'] = "Director | Withdrawal Payment";
        $data['directors'] =  ShareHolder::select('id','name','company_id')->where('type', 19)->where('status', 1)
            ->get();
        $data['branches'] =Branch::select('id','name','branch_code')->where('status', 1)->get();
        $data['branchesbank'] =Branch::select('id','name','branch_code')->where('id',29)->where('status', 1)->get();
        $data['banks'] = SamraddhBank::select('id','bank_name')->with(['bankAccount'=>function($q){
            $q->select('id', 'account_no', 'ifsc_code', 'bank_id', 'branch_name');
        }])->where('status', 1)
            ->get();
        $data['cheques'] = SamraddhCheque::select('id', 'cheque_no', 'account_id')->where('status', 1)
            ->get();
        return view('templates.admin.shareholder.withdrawal_director_payment', $data);
    }
    public function director_deposit_payment_transaction(Request $request)
    {
        DB::beginTransaction();
        try
        {
           
            $globaldate = $request->created_at;
            $select_date = $request->date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            Session::put('created_at', $created_at);
            $director = ShareHolder::where('id', $request->name)
                ->first();
            $companyId = $director->company_id;
            $totalAmount = $request->deposit_amount + $director->amount;
            $director->update(['amount' => $totalAmount, 'updated_at' => $updated_at]);
            $currency_code = 'INR';
            $created_by = 1;
            $created_by_id = \Auth::user()->id;
            $created_by_name = \Auth::user()->username;
            $amount_to_id = NULL; $amount_to_name = NULL; $amount_from_id = NULL;   $amount_from_name = NULL;    $type_id = $bank_id = $bank_id_ac = $bank_ac_id = $empID = NULL;
            $des = '';
            $payment_mode = 4;
            $branch_id = $request->branch;
			if($branch_id>0)
			{
				$branch_id = $request->branch;
			}
			else{
				$branch_id = 29;
			}
           $branchDetail = getBranchDetail($branch_id);
            $branch_code = $branchDetail->branch_code;
            $des = $description = $director->name . '(director) deposit amount';
            $amount = $request->deposit_amount;
            $member_id = NULL;
            //echo $bank_id;die;
            if ($director->type == 19)
            {
                $type_id = $director->head_id;
                $type = 15;
                $sub_type = 151;
                $description_cr = 'To ' . getAcountHeadNameHeadId($type_id) . ' A/c Cr ' . $amount . '/-';
                $amount_from_id = $type_id;
                $amount_from_name = getAcountHeadNameHeadId($type_id);
                $member_id = $director->member_id;
            }
            if ($director->type == 15)
            { //Shareholder
                $type_id = $director->head_id;
                $type = 16;
                $sub_type = 161;
                $description_cr = 'To ' . getAcountHeadNameHeadId($type_id) . ' A/c Cr ' . $amount . '/-';
                $amount_from_id = $type_id;
                $amount_from_name = getAcountHeadNameHeadId($type_id);
                $member_id = $director->member_id;
            }
            // ---------------------head ---------------------------------       
            $v_no = NULL; $v_date = NULL; $ssb_account_id_from = NULL;  $cheque_no = NULL;  $cheque_date = NULL;  $cheque_bank_from = NULL;  $cheque_bank_ac_from = NULL; $cheque_bank_ifsc_from = NULL;
            $cheque_bank_branch_from = NULL; $cheque_bank_to = NULL; $cheque_bank_ac_to = NULL;  $transction_no = NULL; $transction_bank_from = NULL; $transction_bank_ac_from = NULL; $transction_bank_ifsc_from = NULL;  $transction_bank_branch_from = NULL; $transction_bank_to = NULL; $transction_bank_ac_to = NULL;  $transction_date = NULL; $ssb_account_id_to = NULL; $cheque_bank_from_id = NULL; $cheque_bank_ac_from_id = NULL; $cheque_bank_to_name = NULL;  $cheque_bank_to_branch = NULL; $cheque_bank_to_ac_no = NULL; $cheque_bank_to_ifsc = NULL; $transction_bank_from_id = NULL; $transction_bank_from_ac_id = NULL; $transction_bank_to_name = NULL;  $transction_bank_to_ac_no = NULL;   $transction_bank_to_branch = NULL;  $transction_bank_to_ifsc = NULL;  
            $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = NULL ;
            $daybookRef = CommanController::createBranchDayBookReferenceNew($amount, $created_at);
            $refId = $daybookRef;
            $associate_id = NULL;
            if ($request->payment_mode == 0)
            { //cash
                $amount_to_id = $branch_id;
                $amount_to_name = $branchDetail->name . '' . $branchDetail->branch_code;
                $payment_mode = 0;
            }
            if ($request->payment_mode == 1)
            { //cheque
                $payment_mode = 1;
                $chequeDetail = \App\Models\ReceivedCheque::where('id', $request->cheque_no)
                    ->first();
                $cheque_no = $chequeDetail->cheque_no;
                $cheque_date = date("Y-m-d", strtotime(convertDate($chequeDetail->cheque_deposit_date)));
                $cheque_bank_from = $chequeDetail->bank_name;
                $cheque_bank_ac_from = $chequeDetail->cheque_account_no;
                $cheque_bank_ifsc_from = NULL;
                $cheque_bank_branch_from = $chequeDetail->branch_name;
                $cheque_bank_to = $chequeDetail->deposit_bank_id;
                $cheque_bank_ac_to = $chequeDetail->deposit_account_id;
                $cheque_bank_from_id = NULL;
                $cheque_bank_ac_from_id = NULL;
                $cheque_type = 0;
                $cheque_id = $chequeDetail->id;
                $cheque_bank_to_name = getSamraddhBank($cheque_bank_to)->bank_name;
                $bank_ac_detail_get= getSamraddhBankAccountId($cheque_bank_ac_to);
                $cheque_bank_to_branch =$bank_ac_detail_get->branch_name;
                $cheque_bank_to_ac_no = $bank_ac_detail_get->account_no;
                $cheque_bank_to_ifsc = $bank_ac_detail_get->ifsc_code;
                $amount_to_id = $cheque_bank_to;
                $amount_to_name = $cheque_bank_to_name . ' - ' . $cheque_bank_to_ac_no;
                $bank_id = $cheque_bank_to;
                $bank_ac_id = $cheque_bank_ac_to;
            }
            if ($request->payment_mode == 2)            
            { //online
                $payment_mode = 2;
                $transction_no = $request->utr_no;
                $transction_bank_from = $request->transaction_bank;
                $transction_bank_ac_from = $request->transaction_bank_ac;
                $transction_bank_to = $request->online_bank;
                $transction_bank_ac_to = $request->online_bank_ac;
                $transction_date = date("Y-m-d", strtotime(convertDate($request->utr_date)));
                $transction_bank_to_name = getSamraddhBank($transction_bank_to)->bank_name;
                $bank_ac_detail_get_tran= getSamraddhBankAccountId($transction_bank_ac_to);
                $transction_bank_to_branch = $bank_ac_detail_get_tran->branch_name;
                $transction_bank_to_ac_no = $bank_ac_detail_get_tran->account_no;
                $transction_bank_to_ifsc = $bank_ac_detail_get_tran->ifsc_code;
                $amount_to_id = $transction_bank_to;
                $amount_to_name = $transction_bank_to_name . ' - ' . $transction_bank_to_ac_no;
                $bank_id = $transction_bank_to;
                $bank_ac_id = $transction_bank_ac_to;
            }
            $id = $tranId = $director->id;

            if ($request->payment_type == 0)
            { 
            //cash
                $description_dr = 'Cash A/c Dr ' . $amount . '/-';
                // branch daybook
                 $brDaybook = CommanController:: branchDayBookNew($refId, $branch_id, $type, $sub_type, $type_id, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $description_dr, $description_cr,'CR', 0, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date,  $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);

                
                $head3rdC = 28; 
                $allTran1 = CommanController:: headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head3rdC, $type, $sub_type, $type_id, $associate_id= NULL, $member_id, $branch_id_to= NULL, $branch_id_from= NULL,  $amount, $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$companyId);

                 
            }
            else
            {
                $description_dr = 'Bank A/c Dr ' . $amount . '/-';
                if ($request->payment_mode == 1)
                {
                //cheque
                    $receivedPayment['type'] = 5;
                    $receivedPayment['branch_id'] = $branch_id;
                    $receivedPayment['type_id'] = $tranId;
                    $receivedPayment['cheque_id'] = $request->cheque_no;
                    $receivedPayment['created_at'] = $created_at;
                    $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
                    $dataRC['status'] = 3;
                    $receivedcheque = \App\Models\ReceivedCheque::find($request->cheque_no);
                    $receivedcheque->update($dataRC);
                    $gbh = \App\Models\SamraddhBank::where('id', $cheque_bank_to)->first();
                    $chequeLastHeadId=$gbh->account_head_id;
                    $allTran2 = CommanController:: headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $chequeLastHeadId, $type, $sub_type, $type_id, $associate_id= NULL, $member_id, $branch_id_to= NULL, $branch_id_from= NULL,  $amount, $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$companyId);


                    


                    $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id, $cheque_bank_ac_to, $type, $sub_type, $type_id, $associate_id= NULL, $member_id, $branch_id, $opening_balance= NULL, $amount, $closing_balance = NULL, $des, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);


                   
                }
                if ($request->payment_mode == 2)
                {
                //online
                    $gbh = \App\Models\SamraddhBank::where('id', $transction_bank_to)->first();
                    $onlieLastHeadAllTran2=$gbh->account_head_id;
                    $allTran2 = CommanController::  headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $onlieLastHeadAllTran2, $type, $sub_type, $type_id, $associate_id= NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $amount,  $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                    



                    $smbdc = CommanController:: NewFieldAddSamraddhBankDaybookCreateModify($refId, $transction_bank_to, $transction_bank_ac_to, $type, $sub_type, $type_id, $associate_id= NULL, $member_id, $branch_id, $opening_balance= NULL, $amount, $closing_balance = NULL, $des, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);

                    
                    //-----------   bank balence  ---------------------------
                    if ($current_date == $entry_date)
                    {                        
                        $bankClosing = CommanController::checkCreateBankClosing($transction_bank_to, $transction_bank_ac_to, $created_at, $amount, 0);
                    }
                    else
                    {
                        $bankClosing = CommanController::checkCreateBankClosingCRBackDate($transction_bank_to, $transction_bank_ac_to, $created_at, $amount, 0);
                    }
                }
            }
            if ($director->type == 19)
            { //Director
                $current_balance = $director->current_balance;
                $ddata['current_balance'] = $current_balance + $amount;
                $ddata['updated_at'] = $updated_at;
                $ddataUpdate = \App\Models\ShareHolder::find($director->id);
                $ddataUpdate->update($ddata);
                $headD4 = $director->head_id;
                $allTran1 = CommanController:: headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $headD4, $type, $sub_type, $type_id, $associate_id= NULL, $member_id, $branch_id_to= NULL, $branch_id_from= NULL,  $amount, $des, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$companyId);


                



                 
            }
            if ($director->type == 15)
            { //Shareholder
                $current_balance = $director->current_balance;
                $ddata['current_balance'] = $current_balance + $amount;
                $ddata['updated_at'] = $updated_at;
                $ddataUpdate = \App\Models\ShareHolder::find($director->id);
                $ddataUpdate->update($ddata);
                $headS4 = $director->head_id;
                $allTran1S = CommanController:: headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $headS4, $type, $sub_type, $type_id, $associate_id= NULL, $member_id, $branch_id_to= NULL, $branch_id_from= NULL,  $amount, $des, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$companyId);

                
            }
            DB::commit();
        }
        catch(\Exception $ex)
        {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()
            ->route('admin.shareholder')
            ->with('success', 'Payment deposited  Successfully!');
    }
    public function director_withdrawal_payment_transaction(Request $request)
    {   
        //dd($request->all());
        DB::beginTransaction();
        try
        {
            $globaldate = $request->created_at;
            $select_date = $request->date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            Session::put('created_at', $created_at);
            $director = ShareHolder::where('id', $request->name)->first();
            $companyId = $director->company_id; 
            $totalAmount = $director->amount - $request->withdrawal_amount;
            $currentBalance = $director->current_balance - $request->withdrawal_amount;
            $director->update(['amount' => $totalAmount, 'current_balance' => $currentBalance, 'updated_at' => $updated_at]);
            $currency_code = 'INR';
            $randNumber = mt_rand(0, 999999999999999);
            $v_no = $randNumber;
            $v_date = $entry_date;
            $created_by = 1;
            $created_by_id = \Auth::user()->id;
            $created_by_name = \Auth::user()->username;
            $amount = $request->withdrawal_amount;
            $amount1 = $request->withdrawal_amount;
			$branch_id = $request->branch;
			if($branch_id>0)
			{
				$branch_id = $request->branch;
			}
			else{
				$branch_id = 29;
			}
            if ($request->payment_mode == 1)
            {
                $amount1 = $request->withdrawal_amount + $request->neft_charge;
            }
            $daybookRef = CommanController::createBranchDayBookReferenceNew($amount1, $created_at);
            $refId = $daybook_ref_id = $daybookRef;
            $member_id = NULL;
            if ($director->member_id)
            {
                $member_id = $director->member_id;
            }
            $type = 15;
            $sub_type = 152;
            $type_id = $director->head_id;
            $type_transaction_id = $tranId = $director->id;
            if ($request->payment_type == 0)
            { 
            // cash
                $payment_type = 'DR';
                $payment_mode = 0;
                $v_no = NULL;
                $v_date = NULL;
                $ssb_account_id_from = NULL;
                $cheque_no = NULL;
                $cheque_date = NULL;
                $cheque_bank_from = NULL;
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
                $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = NULL ;
               // $branch_id = $request->branch;
                $branchDetail = getBranchDetail($branch_id);
                
                $description_cr = 'To cash A/c Cr ' . $amount . '/-';
                $description_dr = getAcountHeadNameHeadId($type_id) . ' A/c Dr ' . $amount . '/-';
                $des = $description = getAcountHeadNameHeadId($type_id) . ' withdrawal payment through cash ' . $branchDetail->name . '(' . $branchDetail->branch_code . ')';
                $amount_from_id = $branch_id;
                $amount_from_name = $branchDetail->name . '(' . $branchDetail->branch_code . ')';
                $amount_to_id = $type_id;
                $amount_to_name = getAcountHeadNameHeadId($type_id);
                // branch daybook
                $brDaybook = CommanController::branchDaybookCreateModified($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount,  $description, $description_dr, $description_cr, 'DR',0, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id,  $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $companyId);

                // branch cash head -mines ---------- 
                $head3C = 28; 
                $allTran1 = CommanController:: headTransactionCreate($daybook_ref_id, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3C, $type, $sub_type, $type_id, $associate_id= NULL, $member_id, $branch_id_to= NULL, $branch_id_from= NULL,  $amount, $des, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$companyId);

                
                // ----------- director head  mines ---------------  
                $headD4 = $type_id; 
                $allTran1 = CommanController:: headTransactionCreate($daybook_ref_id, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $headD4, $type, $sub_type, $type_id, $associate_id= NULL, $member_id, $branch_id_to= NULL, $branch_id_from= NULL,  $amount, $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$companyId);

                
               
                
            }
            if ($request->payment_type == 1)            
            {
            // bank --- cheque / online
                $payment_type = 'DR';
                $bank_id = $request->bank;
                $bank_ac_id = $request->bank_account;
                $bankDtail = getSamraddhBank($bank_id);
                $bankAcDetail = getSamraddhBankAccountId($bank_ac_id);
                $bankBla = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->whereDate('entry_date', '<=', $entry_date)->orderby('entry_date', 'desc')
                    ->first();
                if ($bankBla)
                {
                    if ($amount > $bankBla->balance)
                    {
                        return back()->with('alert', 'Sufficient amount not available in bank account!');
                    }
                }
                else
                {
                    return back()->with('alert', 'Sufficient amount not available in bank account!');
                }
                $amount_from_id = $bank_id;
                $amount_from_name = $bankDtail->bank_name . '(' . $bankAcDetail->account_no . ')';
                $amount_to_id = NULL;
                $amount_to_name = $director->bank_name . '(' . $director->account_number . ')';
                $opening_balance = NULL;
                $closing_balance = NULL;
                $v_no = NULL;
                $v_date = NULL;
                $ssb_account_id_from = NULL;
                $ssb_account_id_to = NULL;
                $cheque_no = NULL;
                $cheque_date = NULL;
                $cheque_bank_from = NULL;
                $cheque_bank_ac_from = NULL;
                $cheque_bank_ifsc_from = NULL;
                $cheque_bank_branch_from = NULL;
                $cheque_bank_to = NULL;
                $cheque_bank_ac_to = NULL;
                $cheque_bank_from_id = NULL;
                $cheque_bank_ac_from_id = NULL;
                $cheque_bank_to_name = NULL;
                $cheque_bank_to_branch = NULL;
                $cheque_bank_to_ac_no = NULL;
                $cheque_bank_to_ifsc = NULL;
                $transction_no = NULL;
                $transction_bank_from = NULL;
                $transction_bank_ac_from = NULL;
                $transction_bank_ifsc_from = NULL;
                $transction_bank_branch_from = NULL;
                $transction_bank_to = NULL;
                $transction_bank_ac_to = NULL;
                $transction_date = NULL;
                $transction_bank_from_id = NULL;
                $transction_bank_from_ac_id = NULL;
                $transction_bank_to_name = NULL;
                $transction_bank_to_ac_no = NULL;
                $transction_bank_to_branch = NULL;
                $transction_bank_to_ifsc = NULL;
                $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = NULL ;
                $description_cr = 'To Bank A/c Cr ' . $amount . '/-';
                $description_dr = getAcountHeadNameHeadId($type_id) . ' A/c Dr ' . $amount . '/-';
                if ($request->payment_mode == 0)
                {
                    // Cheque
                    $payment_mode = 1;
                    //-----------------------
                    $chequeIssue['cheque_id'] = $cheque_id = $request->cheque_number;
                    $chequeIssue['type'] = 7;
                    $chequeIssue['sub_type'] = 71;
                    $chequeIssue['type_id'] = $type_id;
                    $chequeIssue['cheque_issue_date'] = $entry_date;
                    $chequeIssue['created_at'] = $created_at;
                    $chequeIssue['updated_at'] = $updated_at;
                     $cheque_type = 1;
                    $cheque_id = $cheque_id;
                    $chequeIssueCreate = \App\Models\SamraddhChequeIssue::create($chequeIssue);
                    //------------------
                    $chequeUpdate['is_use'] = 1;
                    $chequeUpdate['status'] = 3;
                    $chequeUpdate['updated_at'] = $updated_at;
                    $chequeDataUpdate = \App\Models\SamraddhCheque::find($cheque_id);
                    $chequeDataUpdate->update($chequeUpdate);
                    $cheque_number = $chequeDataUpdate->cheque_no;
                    $des = $description = getAcountHeadNameHeadId($type_id) . ' withdrawal payment through cheque ' . $cheque_number;
                    $cheque_no = $cheque_number;
                    $cheque_date = $entry_date;
                    $cheque_bank_from = $bankDtail->bank_name;
                    $cheque_bank_ac_from = $bankAcDetail->account_no;
                    $cheque_bank_ifsc_from = $bankAcDetail->ifsc_code;
                    $cheque_bank_branch_from = $bankAcDetail->branch_name;
                    $cheque_bank_to = NULL;
                    $cheque_bank_ac_to = NULL;
                    $cheque_bank_from_id = $bank_id;
                    $cheque_bank_ac_from_id = $bank_ac_id;
                    $cheque_bank_to_name = $director->bank_name;
                    $cheque_bank_to_branch = $director->branch_name;
                    $cheque_bank_to_ac_no = $director->account_number;
                    $cheque_bank_to_ifsc = $director->ifsc_code;
                }
                if ($request->payment_mode == 1)
                {
                //online
                    $payment_mode = 2;
                    $des = $description = getAcountHeadNameHeadId($type_id) . ' withdrawal payment through online ' . $request->utr_number;
                    $transction_no = $request->utr_number;
                    $transction_bank_from = $bankDtail->bank_name;
                    $transction_bank_ac_from = $bankAcDetail->account_no;
                    $transction_bank_ifsc_from = $bankAcDetail->ifsc_code;
                    $transction_bank_branch_from = $bankAcDetail->branch_name;
                    $transction_bank_to = NULL;
                    $transction_bank_ac_to = NULL;
                    $transction_date = $entry_date;
                    $transction_bank_from_id = $bank_id;
                    $transction_bank_from_ac_id = $bank_ac_id;
                    $transction_bank_to_name = $director->bank_name;
                    $transction_bank_to_ac_no = $director->account_number;
                    $transction_bank_to_branch = $director->branch_name;
                    $transction_bank_to_ifsc = $director->ifsc_code;
                    // --------- NEFT charge------------------
                    $allTranNeft = CommanController:: headTransactionCreate($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id , 92, $type, $sub_type, $type_id, $associate_id= NULL, $member_id, $branch_id_to= NULL, $branch_id_from= NULL,  $amount, $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$companyId);

                    
                }
                /// ------------- bank head -------------
                $allTran2 = CommanController::  headTransactionCreate($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id , $bankDtail->account_head_id, $type, $sub_type, $type_id, $associate_id= NULL, $member_id, $branch_id_to= NULL, $branch_id_from= NULL,  $amount1, $des, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$companyId);

                


                $smbdc = CommanController:: branchDaybookCreateModified($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $associate_id= NULL, $member_id, $branch_id_to= NULL, $branch_id_from= NULL, $amount,  $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id,  $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $company_id);

                

                // ----------- director head  mines --------------- 
                $headD4 = $type_id; 
                $allTran1 = CommanController::headTransactionCreate($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $headD4, $type, $sub_type, $type_id, $associate_id= NULL, $member_id, $branch_id_to= NULL, $branch_id_from= NULL,  $amount,  $description, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

               
               
            }            
            if ($request->payment_type == 2)
            {
            // SSB Transaction 
                $ssb_id = $request->ssbid;
                $payment_type = 'DR';
                $payment_mode = 3;
                $ssbAccountDetail = getSavingAccountMemberId($ssb_id);
                $ssbBalance = $ssbAccountDetail->balance;
                $member_id = $ssbAccountDetail->member_id;
                $ssbId = $ssbAccountDetail->id;
                $ssbAccount = $ssbAccountDetail->account_no;
                $branch_id = $ssbAccountDetail->branch_id;
                $branchCode = $ssbAccountDetail->branch_code;
                if ($ssbBalance)
                {
                    if ($amount > $ssbBalance)
                    {
                        return back()->with('alert', 'Sufficient amount not available in SSB account!');
                    }
                }
                else
                {
                    return back()
                        ->with('alert', 'Sufficient amount not available in SSB account!');
                }
                $description_cr = 'To cash A/c Cr ' . $amount . '/-';
                $description_dr = getAcountHeadNameHeadId($type_id) . ' A/c Dr ' . $amount . '/-';
                $des = $description = getAcountHeadNameHeadId($type_id) . ' withdrawal payment through SSB A/c ' . $ssbAccount;
                $detail = 'Withdrawal - director fund';
                //$ssbTranCalculation = CommanController::ssbTransactionModify($ssbId,$ssbAccount,$ssbBalance,$amount,$detail,'INR','DR',5,$branch_id,$associate_id=NULL,4);
                $ssbTranCalculation = CommanController::SSBDateDR($ssbId, $ssbAccount, $ssbBalance, $amount, $detail, 'INR', 'DR', 5, $branch_id, $associate_id = NULL, 4, $created_at);
                $ssbBack = CommanController::SSBBackDateDR($ssbId, $created_at, $amount);
                $ssbRentTranID = $ssbTranCalculation;
                $amountArray = array(
                    '1' => $amount
                );
                $deposit_by_name = $created_by_name;
                $deposit_by_id = $created_by_id;
                $rdCreateTran = CommanController::createTransaction(NULL, 1, $ssbAccountDetail->id, $member_id, $branch_id, $branchCode, $amountArray, 77, $deposit_by_name, $deposit_by_id, $ssbAccountDetail->account_no, $cheque_dd_no = 0, $bank_name = null, $branch_name = null, $payment_date = null, $online_payment_id = null, $online_payment_by = null, $saving_account_id = 0, 'DR');
                $head4SSB = 56; 
                $ssb_account_id_from = $ssbId;
                $cheque_no = NULL;
                $cheque_date = NULL;
                $cheque_bank_from = NULL;
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
                $jv_unique_id =  $cheque_type = $cheque_id = $ssb_account_tran_id_to = NULL ;
                $ssb_account_tran_id_from =$ssbRentTranID;
                // ssb head entry +
                $allTranSSB = CommanController::  headTransactionCreate($refId, $branch_id, $bank_id = NULL, $bank_ac_id = NULL,$head4SSB ,15, 152, $ssbId, $associate_id = NULL, $member_id, $branch_id_to= NULL, $branch_id_from= NULL,  $amount, $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$companyId);

                
                $description_cr = 'To SSB(' . $ssbAccount . ') A/c Cr ' . $amount . '/-';
                $description_dr = getAcountHeadNameHeadId($type_id) . ' A/c Dr ' . $amount . '/-';
                $brDaybook = CommanController:: branchDaybookCreateModified($refId, $branch_id, $type, $sub_type, $type_id, $associate_id= NULL, $member_id= NULL, $branch_id_to= NULL, $branch_id_from= NULL, $amount,  $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id,  $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $company_id);
                
                
                // ----------- director head  mines ---------------
                $headD4 = $type_id; 
                $allTran1 = CommanController:: headTransactionCreate($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $headD4, $type, $sub_type, $type_id, $associate_id= NULL, $member_id, $branch_id_to= NULL, $branch_id_from= NULL,  $amount,  $description, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);


                
                
                
            }
            DB::commit();
        }
        catch(\Exception $ex)
        {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()
            ->route('admin.shareholder')
            ->with('success', 'Payment Withdrawal Successfully!');
    }
    public function save_transfer(Request $request)
    {
        dd($request->all());
        $head_id = AccountHeads::orderBy('head_id', 'desc')->first('head_id');
        $m_id = Member::where('member_id', $request->member_id)->first('id');
        $rules = ['name' => 'required', 'new_person_father_name' => 'required', 'new_person_address' => 'required', 'new_person_pan_no' => 'required', 'new_person_aadhar_no' => 'required', 'new_person_contact_no' => 'required', 'bank_name' => 'required', 'branch_name' => 'required', 'account_number' => 'required', 'ifsc_code' => 'required', ];
        $customMessages = ['required' => 'The :attribute field is required.'];
        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try
        {
            $globaldate = $request->created_at;
            $select_date = $request->date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            Session::put('created_at', $created_at);
            $parentHeadDetail = AccountHeads::where('head_id', 15)->first();
            $head_data['created_at'] = $created_at;
            $head_data['updated_at'] = $updated_at;
            $head_data['sub_head'] = $request->name;
            $head_data['head_id'] = $head_id->head_id + 1;
            $head_data['labels'] = 4;
            $head_data['parent_id'] = 15;
            $head_data['parentId_auto_id'] = $parentHeadDetail->id;
            $head_data['cr_nature'] = $parentHeadDetail->cr_nature;
            $head_data['dr_nature'] = $parentHeadDetail->dr_nature;
            $head_data['is_move'] = 1;
            $head_data['status'] = 0;
            $head_data['company_id'] = $request->company_id;
            $headCreate = AccountHeads::create($head_data);
            $account_head = AccountHeads::where('id', $headCreate->id)
                ->first();
            $data['name'] = $request->name;
            $data['father_name'] = $request->new_person_father_name;
            $data['head_id'] = $account_head->head_id;
            $data['type'] = 15;
            $data['address'] = $request->new_person_address;
            $data['pan_card'] = $request->new_person_pan_no;
            $data['aadhar_card'] = $request->new_person_aadhar_no;
            $data['contact'] = $request->new_person_contact_no;
            $data['bank_name'] = $request->bank_name;
            $data['branch_name'] = $request->branch_name;
            $data['account_number'] = $request->account_number;
            $data['ifsc_code'] = $request->ifsc_code;
            $data['member_id'] = $m_id->id;
            $data['ssb_account'] = $request->ssb_account;
            $data['ssb_account_id'] = $request->ssb_id;
            $data['remark'] = $request->remark;
            $data['email'] = $request->email;
            $data['status'] = 1;
            $data['firm_name'] = $request->firm_name;
            $data['created_at'] = $created_at;
            $data['updated_at'] = $updated_at;
            $createId = ShareHolder::create($data);

             $tranId = $createId->id;
            $shareholder = ShareHolder::where('id', $request->shareholder)
                ->first();
            $totalAmount1 = $shareholder->amount - $request->new_amount;
            $shareholder->update(['amount' => $totalAmount1, 'updated_at' => $updated_at]);
            $director = ShareHolder::where('id', $createId->id)
                ->first();
            $company_id = $director->company_id;
            $totalAmount = $request->new_amount + $director->amount;
            $director->update(['amount' => $totalAmount1, 'updated_at' => $updated_at]);
            $currency_code = 'INR';
            $created_by = 1;
            $created_by_id = \Auth::user()->id;
            $created_by_name = \Auth::user()->username;
            $amount_to_id = NULL;
            $amount_to_name = NULL;
            $amount_from_id = NULL;
            $amount_from_name = NULL;
            $type_id = $bank_id = $bank_id_ac = $bank_ac_id = $empID = NULL;
            $des = '';
            $payment_mode = 4;
            $branch_id = 29;
			$branchDetail = getBranchDetail($branch_id);
            $branch_code = $branchDetail->branch_code;
            $des = $description = 'Amount received from ' . $shareholder->name;
            $amount = $request->new_amount;
            $member_id = $director->member_id;
            //echo $bank_id;die;
            $type_id = $director->head_id;
            $type = 16;
            $sub_type = 162;
            $description_cr = 'To ' . getAcountHeadNameHeadId($type_id) . ' A/c Cr ' . $amount . '/-';
            $amount_from_id = $type_id;
            $amount_from_name = getAcountHeadNameHeadId($type_id);
            $member_id = $director->member_id;
            $amount_from_id = $shareholder->id;
            $amount_from_name = getAcountHeadNameHeadId($shareholder->head_id);
            $amount_to_id = $type_id;
            $amount_to_name = getAcountHeadNameHeadId($type_id);
            /// from
            $amount_from_id1 = $type_id;
            $amount_from_name1 = getAcountHeadNameHeadId($type_id);
            $amount_to_id1 = $shareholder->id;
            $amount_to_name1 = getAcountHeadNameHeadId($shareholder->head_id);
            // ---------------------head ---------------------------------
            $v_no = NULL;
            $v_date = NULL;
            $ssb_account_id_from = NULL;
            $cheque_no = NULL;
            $cheque_date = NULL;
            $cheque_bank_from = NULL;
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
            $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = NULL ;
            $daybookRef = CommanController::createBranchDayBookReferenceNew($amount, $created_at);
            $refId = $daybookRef;
            $current_balance = $director->current_balance;
            $ddata['current_balance'] = $current_balance + $amount;
            $ddata['updated_at'] = $updated_at;
            $ddataUpdate = \App\Models\ShareHolder::find($director->id);
            $ddataUpdate->update($ddata); 
            $headS4 = $director->head_id; 

            $allTran1S = CommanController:: headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $headS4, $type, $sub_type, $type_id, $associate_id= NULL, $member_id, $branch_id_to= NULL, $branch_id_from= NULL,  $amount,  $des, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

           
         
            /// --------------share ----------------------------------
            $dess = $descriptions = 'Amount tranfered to ' . $director->name;
            $current_balance1 = $shareholder->current_balance;
            $ddata1['current_balance'] = $current_balance1 - $amount;
            $ddata1['updated_at'] = $updated_at;
            $ddataUpdate1 = \App\Models\ShareHolder::find($shareholder->id);
            $ddataUpdate1->update($ddata1);
            $headS41 = $shareholder->head_id; 
            $allTran1S11 = CommanController:: headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $headS41, $type, $sub_type, $headS41, $associate_id= NULL, $shareholder->member_id, $branch_id_to= NULL, $branch_id_from= NULL,  $amount,  $dess, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $shareholder->id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

            
         
            DB::commit();
        }
        catch(\Exception $ex)
        {
            DB::rollback();
            return back()->with('alert', $ex->getLine());
        }
        return redirect()
            ->route('admin.shareholder')
            ->with('success', 'Share tranfered Successfully!');
    }
}