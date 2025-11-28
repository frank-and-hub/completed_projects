<?php 
namespace App\Http\Controllers\Admin; 

use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\CorrectionRequests;
use App\Models\AccountHeads;
use App\Models\SubAccountHeads;
use App\Models\SamraddhBankAccount;
use App\Models\SamraddhBank;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use URL;
use DB;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Member Management CorrectionController
    |--------------------------------------------------------------------------
    |
    | This controller handles members all functionlity.
*/
class CategoryController extends Controller
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
    public function accountHead()
    {
        $data['title']='Account Head';
        $data['head1'] = array('id' => '','sub_head' => '', 'parent_id' => 0);
        $data['head2'] = array('id' => '','sub_head' => '', 'parent_id' => 0);
        $data['head3'] = array('id' => '','sub_head' => '', 'parent_id' => 0);
        $data['accountHeads'] = AccountHeads::where('labels',1)->orderBy('id', 'desc')->get();
        return view('templates.admin.category.account-head-listing', $data);
    }

    /**
     * Display a listing of the account heads categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function accountHeadCategory($id)
    {
        $data['title']='Account Sub Head';
        $data['accountHeads'] = AccountHeads::where('parent_id',$id)->orderBy('id', 'desc')->get();
        $data['head1'] = AccountHeads::select('id','parent_id','sub_head')->where('id',$id)->orderBy('id', 'desc')->first();

        /*if($parentId['parent_id'] != 0){
            $data['head1'] = AccountHeads::select('sub_head','parent_id')->where('id',$parentId['parent_id'])->orderBy('id', 'desc')->first();
        }else{
            $data['head1'] = array('sub_head' => '', 'parent_id' => 0);
        }*/

        if($data['head1']['parent_id'] != 0){
            $data['head2'] = AccountHeads::select('id','sub_head','parent_id')->where('id',$data['head1']['parent_id'])->orderBy('id', 'desc')->first();
        }else{
            $data['head2'] = array('id' => '','sub_head' => '', 'parent_id' => 0);
        }

        if($data['head2']['parent_id'] != 0){
            $data['head3'] = AccountHeads::select('id','sub_head','parent_id')->where('id',$data['head2']['parent_id'])->orderBy('id', 'desc')->first();
        }else{
            $data['head3'] = array('id' => '','sub_head' => '', 'parent_id' => 0);;
        }


        return view('templates.admin.category.account-head-listing', $data);
    }

    /**
     * Display a listing of the sub account heads.
     *
     * @return \Illuminate\Http\Response
     */
    public function subAccountHead()
    {
        $data['title']='Sub Account Head';
        return view('templates.admin.category.sub-account-head-listing', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function accountheadListing(Request $request)
    { 
        if ($request->ajax()) {

            $data=AccountHeads::orderBy('id', 'desc')->get();

            return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function($row){
                $created_at = date("d/m/Y", strtotime(convertDate($row->created_at)));
                return $created_at;
            })
            ->rawColumns(['created_at'])          
            ->addColumn('account_type', function($row){
                if($row->account_type == 0){
                    $account_type = 'Expenses';
                }elseif($row->account_type == 1){
                    $account_type = 'Liability';
                }elseif($row->account_type == 2){
                    $account_type = 'Bank';        
                }elseif($row->account_type == 3){
                    $account_type = 'inflow';   
                }
                return $account_type;
            })
            ->rawColumns(['account_type'])
            ->addColumn('head_fa_code', function($row){
                $head_fa_code = $row->account_number;
                return $head_fa_code;
            })
            ->rawColumns(['head_fa_code'])
            ->addColumn('title', function($row){
                $title = $row->title;
                return $title;
            })
            ->rawColumns(['title'])
            ->addColumn('status', function($row){
                if($row->status == 0){
                    $status = 'Active';
                }else{
                    $status = 'Not active';        
                }
                
                return $status;
            })
            ->rawColumns(['status'])
            ->addColumn('action', function($row){
                $url = URL::to("admin/editaccounthead/".$row->id."");
                $deleteurl = URL::to("admin/deleteaccounthead/".$row->id."");
                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                $btn .= '<a class="dropdown-item" href="'.$url.'"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                //if($row->account_type != 2){

                $btn .= '<a class="dropdown-item delete-account-head" href="'.$deleteurl.'"><i class="fas fa-thumbs-down"></i>Delete</a>';
               // }

                $btn .= '</div></div></div>';          
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function subAccountheadListing(Request $request)
    { 
        if ($request->ajax()) {

            $data=SubAccountHeads::with('accountHead')->orderBy('id', 'desc')->get();
            
            return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function($row){
                $created_at = date("d/m/Y", strtotime(convertDate($row->created_at)));
                return $created_at;
            })
            ->rawColumns(['created_at'])
            ->addColumn('account_type', function($row){
                if($row->account_type == 0){
                    $account_type = 'Expenses';
                }elseif($row->account_type == 1){
                    $account_type = 'Liability';
                }elseif($row->account_type == 2){
                    $account_type = 'Bank';        
                }elseif($row->account_type == 3){
                    $account_type = 'inflow';   
                }
                return $account_type;
            })
            ->rawColumns(['account_type'])
            ->addColumn('acount_head', function($row){
                $account_head = $row->account_head_id;
                return $account_head;
            })
            ->rawColumns(['acount_head'])
            ->addColumn('head_fa_code', function($row){
                $head_fa_code = $row->account_head_code;
                return $head_fa_code;
            })
            ->rawColumns(['head_fa_code'])
            ->addColumn('sub_head_fa_code', function($row){
                $sub_head_fa_code = $row->sub_account_head_code;
                return $sub_head_fa_code;
            })
            ->rawColumns(['sub_head_fa_code'])
            ->addColumn('title', function($row){
                $title = $row->title;
                return $title;
            })
            ->rawColumns(['title'])
            ->addColumn('status', function($row){
                if($row->status == 0){
                    $status = 'Active';
                }else{
                    $status = 'Not active';        
                }
                
                return $status;
            })
            ->rawColumns(['status'])
            ->addColumn('action', function($row){
                $url = URL::to("admin/editsubaccounthead/".$row->id."");
                $deleteurl = URL::to("admin/deletesubaccounthead/".$row->id."");
                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                $btn .= '<a class="dropdown-item" href="'.$url.'"><i class="icon-pencil7 mr-2"></i>Edit</a>';
               // if($row->account_type != 2){
                $btn .= '<a class="dropdown-item delete-sub-account-head" href="'.$deleteurl.'"><i class="fas fa-thumbs-down"></i>Delete</a>';
              //  }
                $btn .= '</div></div></div>';          
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }

    /**
     * Add Account Head View.
     * Route: /member/passbook
     * Method: get 
     * @return  array()  Response
     */
    public function addCategory()
    {
        $data['title'] = 'Add Account Head';
        return view('templates.admin.category.addaccounthead',$data);
    }

    /**
     * Add Sub Account Head View.
     * Route: /member/passbook
     * Method: get 
     * @return  array()  Response
     */
    public function addSubCategory()
    {
        $data['title'] = 'Add Sub Account Head';
        return view('templates.admin.category.addsubaccounthead',$data);
    }

    /**
     * Save Account Head.
     * Route: /save-account-head
     * Method: get 
     * @return  array()  Response
     */
    public function saveAccountHead(Request $request)
    {
        /*$rules = [
            'accounttype' => 'required',
            'account_head_name' => 'required',
        ];

        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];

        $this->validate($request, $rules, $customMessages);*/

        DB::beginTransaction();
        try {
                $hbank['sub_head']=$request['account_head_name'];
                $hbank['parent_id']=$request['subhead'];
                $hbank['labels']=3;
                $hbank['created_at'] = $request['created_at'];
                $accountHead = AccountHeads::create($hbank);

                $hData['head_id']=$accountHead->id;           
                AccountHeads::where('id',$accountHead->id)->update($hData); 

                $sbank['bank_name']=$request['account_head_name'];
                $sbank['account_head_id']=$accountHead->id;
                $sbank['created_at'] = $request['created_at'];            
                $bankCreate = SamraddhBank::create($sbank);

                $sbankAccount['bank_id']=$bankCreate->id;
                $sbankAccount['account_no']=$request['account_number'];
                $sbankAccount['account_head_id']=$accountHead->id;
                $sbankAccount['created_at'] = $request['created_at'];            
                $bankAccountCreate = SamraddhBankAccount::create($sbankAccount);

                /*$accounttype = $request['accounttype'];
                
                $aHead['title'] = $request['account_head_name'];
                $aHead['account_type'] = $accounttype;

                if($accounttype == 2){
                    $aHead['fa_code'] = $request['account_number'];
                    $aHead['account_number'] = $request['account_number'];
                }else{
                    $accountHeadAccount = getAccountHeadFacode($accounttype,'save');
                    $aHead['account_head_code'] = $accountHeadAccount;
                    $aHead['fa_code'] = $accountHeadAccount;
                    $aHead['account_number'] = $accountHeadAccount;
                }

                $res = AccountHeads::create($aHead);
                if($accounttype == 2)
                {
                    $bank['bank_name']=$request['account_head_name'];
                    $bank['account_head_id']=$res->id;
                    $bank['created_at'] = $request['created_at'];            
                    $bankCreate = \App\Models\SamraddhBank::create($bank);
                    $bankAccount['bank_id']=$bankCreate->id;
                    $bankAccount['account_no']=$request['account_number'];
                    $bankAccount['account_head_id']=$res->id;
                    $bankAccount['created_at'] = $request['created_at'];            
                    $bankAccountCreate = \App\Models\SamraddhBankAccount::create($bankAccount);
                }*/
        DB::commit();
        } 
        catch (\Exception $ex) 
        {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }

       
        if ($bankAccountCreate) {
            return redirect()->route('admin.accountHead')->with('success', 'Account Head Created Successfully!');
        } else {
            return back()->with('alert', 'Problem With Creating New User');
        }
    }

    /**
     * Get account heads.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getAccountHead(Request $request)
    {
        $accountType = $request->accountType;
        $accountHeads = AccountHeads::select('id','title')->where('account_type',$accountType)->get();
        $resCount = count($accountHeads);
        $return_array = compact('accountHeads','resCount');
        return json_encode($return_array); 
    } 

    /**
     * Get account heads.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getAccountNumber(Request $request)
    {
        $account_number = $request->account_number;
        $accountnumber = AccountHeads::where('account_number',$account_number)->count();
        $return_array = compact('accountnumber');
        return json_encode($return_array);
    } 

    /**
     * Get account heads.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getEditAccountNumber(Request $request)
    {
        $account_number = $request->account_number;
        $ahid = $request->ahid;
        $accountnumber = AccountHeads::where('id','!=',$ahid)->where('account_number',$account_number)->count();
        $return_array = compact('accountnumber');
        return json_encode($return_array);
    }

    /**
     * Get sub account heads.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getSubAccountNumber(Request $request)
    {
        $account_number = $request->account_number;
        $accountnumber = SubAccountHeads::where('account_number',$account_number)->count();
        $return_array = compact('accountnumber');
        return json_encode($return_array);
    } 

    /**
     * Get sub account heads.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getSubEditAccountNumber(Request $request)
    {
        $account_number = $request->account_number;
        $ahid = $request->ahid;
        $accountnumber = SubAccountHeads::where('id','!=',$ahid)->where('account_number',$account_number)->count();
        $return_array = compact('accountnumber');
        return json_encode($return_array);
    }

    /**
     * Save Account Head.
     * Route: /save-account-head
     * Method: get 
     * @return  array()  Response
     */
    public function saveSubAccountHead(Request $request)
    {
        $rules = [
            'subaccounttype' => 'required',
            'sub_account_head_name' => 'required',
        ];

        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];

        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {
                $accounttype = $request['subaccounttype'];
                

                $sHead['title'] = $request['sub_account_head_name'];
                $sHead['account_type'] = $accounttype;
                
                if($accounttype == 2){
                    $sHead['fa_code'] = $request['account_number'];
                    $sHead['account_number'] = $request['account_number'];
                }else{
                    $accountHeadAccount = getSubAccountHeadFacode($request['accounthead'],$accounttype,'save');
                    $sHead['account_head_id'] = $request['accounthead'];
                    $sHead['account_head_code'] = $accountHeadAccount['account_head_code'];
                    $sHead['sub_account_head_code'] = $accountHeadAccount['sub_account_head_code'];
                    $sHead['fa_code'] = $accountHeadAccount['fa_code'];
                    $sHead['account_number'] = $accountHeadAccount['account_number'];
                }

                $res = SubAccountHeads::create($sHead);

                if($accounttype == 2)
                {
                    $bank['bank_name']=$request['sub_account_head_name'];
                    $bank['sub_account_head_id']=$res->id; 
                    $bank['created_at'] = $request['created_at'];            
                    $bankCreate = \App\Models\SamraddhBank::create($bank);

                    $bankAccount['bank_id']=$bankCreate->id;
                    $bankAccount['account_no']=$request['account_number'];
                    $bankAccount['sub_account_head_id']=$res->id; 
                    $bankAccount['created_at'] = $request['created_at'];            
                    $bankAccountCreate = \App\Models\SamraddhBankAccount::create($bankAccount);
                }
        DB::commit();
        } 
        catch (\Exception $ex) 
        {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }

        if ($res) {
            return redirect()->route('admin.subaccountHead')->with('success', 'Subaccount Head Created Successfully!');
        } else {
            return back()->with('alert', 'Problem With Creating New User');
        }
    }

    /**
     * Edit Account Head View.
     * Route: /member/passbook
     * Method: get 
     * @return  array()  Response
     */
    public function editAccountHead($id)
    {
        $data['title'] = 'Edit Account Head';
        $data['accounthead'] = AccountHeads::findOrFail($id);
        return view('templates.admin.category.editaccounthead',$data);
    }

    /**
     * Edit Sub Account Head View.
     * Route: /member/passbook
     * Method: get 
     * @return  array()  Response
     */
    public function editSubAccountHead($id)
    {
        $data['title'] = 'Edit Sub Account Head';
        $data['subaccounthead'] = SubAccountHeads::findOrFail($id);
        $data['accountHeads'] = AccountHeads::select('id','title')->where('account_type',$data['subaccounthead']->account_type)->get();
        return view('templates.admin.category.editsubaccounthead',$data);
    }

    /**
     * Update the specified accounthead.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateAccountHead(Request $request)
    {
        $rules = [
            'accounttype' => 'required',
            'account_head_name' => 'required',
        ];

        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];

        $this->validate($request, $rules, $customMessages);         
        DB::beginTransaction();
        try {      
            $data = AccountHeads::findOrFail($request->accountheadid);

            $accounttype = $request->accounttype;
            $data->title = $request->account_head_name;
            $data->account_type = $accounttype;

            if($accounttype == 2){
                $data->fa_code = $request['account_number'];
                $data->account_number = $request['account_number'];
                $data->account_head_code = NULL;

                $bank['bank_name']=$request['account_head_name'];           
                $bankCreate = \App\Models\SamraddhBank::where('account_head_id',$request->accountheadid)->update($bank); 

                $bankAccount['account_no']=$request['account_number'];           
                $bankAccountCreate = \App\Models\SamraddhBankAccount::where('account_head_id',$request->accountheadid)->update($bankAccount); 


            }else{
                $accountHeadAccount = getAccountHeadFacode($accounttype,'update');
                $data->account_head_code = $accountHeadAccount;
                $data->fa_code = $accountHeadAccount;
                $data->account_number = $accountHeadAccount; 
            }

            $res=$data->save();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
        if ($res) {
            return back()->with('success', 'Update was Successful!');
        } else {
            return back()->with('alert', 'An error occured');
        }
    }

    /**
     * Update the specified accounthead.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateSubAccountHead(Request $request)
    {
        $rules = [
            'subaccounttype' => 'required',
            'sub_account_head_name' => 'required',
        ];

        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];

        $this->validate($request, $rules, $customMessages);  
        $accounttype = $request['subaccounttype'];
        
        DB::beginTransaction();
        try {        

            $data = SubAccountHeads::findOrFail($request->accountheadid);

            $data->title = $request->sub_account_head_name;
            $data->account_type = $accounttype;
            
            if($accounttype == 2){
                $data->fa_code = $request['account_number'];
                $data->account_number = $request['account_number'];
                $data->sub_account_head_code = NULL;
                $data->account_head_id = NULL;
                $data->account_head_code = NULL;
            
                $bank['bank_name']=$request['sub_account_head_name'];             
                $bankCreate = \App\Models\SamraddhBank::where('sub_account_head_id',$request->accountheadid)->update($bank); 

                $bankAccount['account_no']=$request['account_number'];           
                $bankAccountCreate = \App\Models\SamraddhBankAccount::where('sub_account_head_id',$request->accountheadid)->update($bankAccount);       


            }else{
                $accountHeadAccount = getSubAccountHeadFacode($request['accounthead'],$accounttype,'update');
                $data->account_head_id = $request->accounthead;
                $data->account_head_code = $accountHeadAccount['account_head_code'];
                $data->sub_account_head_code = $accountHeadAccount['sub_account_head_code'];
                $data->fa_code = $accountHeadAccount['fa_code'];
                $data->account_number = $accountHeadAccount['account_number'];
            }

            $res=$data->save();

        DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
        if ($res) {
            return back()->with('success', 'Update was Successful!');
        } else {
            return back()->with('alert', 'An error occured');
        }
    } 

    /**
     * Delete account head.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteAccountHead($id)
    {
        DB::beginTransaction();
        try { 

            $getType=AccountHeads::where('id',$id)->first('account_type');
            

            if($getType->account_type==2)
            {
                $bank = \App\Models\SamraddhBank::where('account_head_id',$id)->delete();
            $account = \App\Models\SamraddhBankAccount::where('account_head_id',$id)->delete();
            }
            $accounthead = AccountHeads::where('id',$id)->delete();
         DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
        return back()->with('success', 'Account Head deleted successfully!');
    }

    /**
     * Delete account head.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteSubAccountHead($id)
    {
        DB::beginTransaction();
        try { 

            $getType=SubAccountHeads::where('id',$id)->first('account_type');
            
            if($getType->account_type==2)
            {
                $bank = \App\Models\SamraddhBank::where('sub_account_head_id',$id)->delete();
                $account = \App\Models\SamraddhBankAccount::where('sub_account_head_id',$id)->delete();
            }
            $subaccounthead = SubAccountHeads::where('id',$id)->delete();
        DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }

        return back()->with('success', 'Sub Account Head deleted successfully!');
    }
}
