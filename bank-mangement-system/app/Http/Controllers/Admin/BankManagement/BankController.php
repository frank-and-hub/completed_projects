<?php

namespace App\Http\Controllers\Admin\BankManagement;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\RepositoryInterface;
use Yajra\DataTables\DataTables;
use App\Models\AccountHeads;

class BankController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->middleware('auth');
    }

    /*------------------- Display the index page of listing bank management -----------------*/
    public function index()
    {
        $data['title'] = "Bank Management";
        return view('templates.admin.bank_management.index',$data);
    }

    /*----------- Listing ---------------*/
    /*---------- Table samraddh_banks -----------*/
    public function listing(Request $request)
    {
        if($request->ajax())
        {
            $data = $this->repository->getAllSamraddhBank()->has('company')->with('company:id,name,short_name')->when($request->companyId != '0',function($q) use ($request){
                $q->where('company_id',$request->companyId);
            })->orderBy('id','desc')->get(['id','bank_name','status','created_by','created_at','company_id']);

            return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('company_id',function($row)
            {
                $company_id = $row->company->name ??'N/A';
                return $company_id;
            })
            ->rawColumns(['company_id'])
            ->addColumn('created_by',function($row){
                $created_by = ($row->created_by) ? "Admin" : "N/A";

                return $created_by;
            })
            ->rawColumns(['created_by'])
            ->addColumn('created_at',function($row){
                $created_at = date('d/m/Y',strtotime($row->created_at));

                return $created_at;
            })
            ->rawColumns(['created_at'])
            ->addColumn('action', function($row) {
                $btn = "";
                $btn = '<div class="list-icons">
                <div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                    <div class="dropdown-menu dropdown-menu-right">
                        
                    <button class="status_data btn btn-white w-100 legitRipple text-left" id="status_data" data-id="'.$row->id.'" data-status="'.$row->status.'"  
                title="Status Change" ><i class="far fa-thumbs-up mr-2"></i>Status Change</button>

                        <button class="edit_data btn btn-white w-100 legitRipple text-left" data-toggle="modal" data-target="#bankModel" id="edit-data" data-id="'.$row->id.'"  
				title="Edit" ><i class="fa fa-edit mr-2"></i>Edit</button> 
        
                
                </div>
                </div>
                </div>';
                return $btn;
            })
            ->rawColumns(['action'])
            
            ->make(true);
        }
    }

    /*
    * Insert the data 
    * Table name is Samraddh_banks
    */
    public function create(Request $request)
    {
        if($request->ajax())
        {
            $validator = Validator::make($request->all(), [
                'bank_name' => 'required|unique:samraddh_banks,bank_name,NULL,id,company_id,'.$request->addBankCompanyId,
                'addBankCompanyId' => 'required'
            ]);
            if($validator->fails())
            {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $head_id = AccountHeads::select('head_id')->orderBy('head_id','desc')->first('head_id');

            // $create= [
            //     'sub_head' => $request->bank_name,
            //     'head_id'=>$head_id->head_id + 1,
            //     'parent_id'=>27,
            //     'parentId_auto_id'=>27,
            //     'labels'=>4,
            //     'status'=>0,
            //     'cr_nature'=>2,
            //     'dr_nature'=>1,
            //     'is_move'=>0,
            //     'company_id'=>json_encode([(int)$request->company_id]),
            //     'can_disable_status'=>0,
            //     'basic_heads'=>2
            // ];
            // dd($create); 

            $insertAccountHeads = AccountHeads::create([
                'sub_head' => $request->bank_name,
                'head_id'=>$head_id->head_id + 1,
                'parent_id'=>27,
                'parentId_auto_id'=>27,
                'labels'=>4,
                'status'=>0,
                'cr_nature'=>2,
                'dr_nature'=>1,
                'is_move'=>0,
                'company_id'=>'['.$request->addBankCompanyId.']',
                'can_disable_status'=>0,
                'basic_heads'=>'0',
                'created_at'=> $request->created_at,
                'updated_at'=> $request->created_at
            ]); 

            if($insertAccountHeads)
            {
                $insertBank = $this->repository->getAllSamraddhBank()->create([
                'account_head_id'=>$head_id->head_id + 1,
                'bank_name'=> $request->bank_name,
                'company_id'=>$request->addBankCompanyId,
                'created_at'=>$request->created_at,
                'updated_at'=>$request->created_at
            ]);
                
                $response = 1;    
            }
            else
            {
                $response = 0;
            }
            
            
            $data = ['data'=>$response??0];
            
            return response()->json($data);
        }
    }

    //------------ fetch data for edit value ----------------------
    public function fetch(Request $request)
    {
        $fetch = $this->repository->getAllSamraddhBank()->whereId($request->id)->first(['id','bank_name','company_id']);
        $fetchCompanyName = $this->repository->getAllCompanies()->whereId($fetch->company_id)->first(['id','name']);
        $data = ['bank_id'=>$request->id,'bank_name' => $fetch->bank_name,'company_id'=>$fetchCompanyName->name];
        return response()->json($data);
    }

    //------------- Update the data ---------------
    public function update(Request $request)
    {
        $check = $this->repository->getAllSamraddhBank()->whereId($request->bank_id)->update(['bank_name'=>$request->bank_name,'updated_at'=>$request->created_at]);

        $checkGetBank = $this->repository->getAllSamraddhBank()->where('id',$request->bank_id)->first();

        $insertAccountHeads = AccountHeads::where('head_id',$checkGetBank->account_head_id)->update(['sub_head'=>$request->bank_name,'updated_at'=>$request->created_at]);

        
        if($check)
        {
            $response = 1;
        }
        else{ $response = 0;}

        $data = ['data'=>$response];
        return response()->json($data);
    }

    /*
    * Status Change 
    */

    public function status(Request $request)
    {
        $data = $this->repository->getAllSamraddhBank()->whereId($request->id)->first(['id','status']);
        if($data->status == '1')
        {
            $data->update(['status' => '0']);
            $response = "1";
        }
        else
        {
            $data->update(['status' => '1']);
            $response = "1";
        }
        
        $data = ['data'=>($response)? $response : "0"];
        return response()->json($data);
    }

    //Collect data for add account 
    //two tables use here 1. samraddh_banks 2. samraddh_bank_accounts

    // public function collect(Request $request)
    // {
    //     dd($request);
    //     $bankNameFetch = $this->repository->getAllSamraddhBank()->whereCompany_id($request->id)->first(['company_id','bank_name']);
    //     return response()->json(['bank_name'=>($bankNameFetch->bank_name)??""]);
    // }

    // /*------- Add Account --------------- */
    // /*--------table name samraddh_bank_accounts ---------------*/
    // public function accountCreate(Request $request)
    // {
    //     $insert = $this->repository->getAllSamraddhBankAccounts()->create([
    //         'company_id'=>$request->company_id,
    //         'bank_id' => $request->bank_id,
    //         'account_no'=>$request->account_no,
    //         'ifsc_code'=>$request->ifsc_code,
    //         'branch_name'=>$request->branch_name,
    //         'address'=>$request->address
    //     ]);

    //     return response()->json(['data'=>($insert)?1:0]);
    // }
}