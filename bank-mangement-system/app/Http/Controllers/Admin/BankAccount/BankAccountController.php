<?php

namespace App\Http\Controllers\Admin\BankAccount;

use Illuminate\Http\Request;

use Auth;

use App\Models\Settings;

use App\Http\Controllers\Controller;

use App\Models\Branch;

use App\Models\SamraddhBank; 

use App\Models\AccountHeads;

use Yajra\DataTables\DataTables;

use Carbon\Carbon;

use DB;

use URL;

use App\Models\SamraddhBankAccount;

class BankAccountController extends Controller

{





	public function __construct(){

		$this->middleware('auth');

	}



	// BankAccount  Report 

	public function index(){

		
		if(check_my_permission( Auth::user()->id,"59") != "1"){
		  return redirect()->route('admin.dashboard');
		} 
		
		$data['title'] = "Account Head Management | BankAccount";

	

		return view('templates.admin.bank_account.index',$data);

	}

	

	public function create_bank_account()

	{

	    $data['title'] = "Account Head Management | Add Bank Account";

	

	    return view('templates.admin.bank_account.create_bank_account',$data); 

	}

	

	public function store_bank_account (Request $request)

	{

	     $head_id =  AccountHeads::orderBy('head_id','desc')->first('head_id');

	     $rules = [

            'bank_name' => 'required',

             'branch_name' => 'required',

            'account_number' => 'required',

            'ifsc' => 'required',

            'address' => 'required',

          

        ];

         $customMessages = [

            'required' => 'The :attribute field is required.'

        ];

        $this->validate($request, $rules, $customMessages);

	    try{

	        $head_data['sub_head'] = $request->bank_name;

	        $head_data['head_id'] = $head_id->head_id + 1 ;

	        $head_data['parent_id'] = 27;

	        $head_data['labels'] = 4;

	        $head_data['status'] = 0;

	        $checkExist = AccountHeads::where('sub_head','=',$head_data['sub_head'])->where('labels',$head_data['labels'])->where('parent_id',27)->exists();

	       $checkExistBank = SamraddhBank::where('bank_name','=',$head_data['sub_head'])->exists();


          if($checkExist)
          {
            return back()->with('alert','Bank Already exist !');
          }
          else if($checkExistBank)
          {
              return back()->with('alert','Bank Already exist !');
          }
          else

	       {

	       	 $accountHeads = AccountHeads::create($head_data);

	         $account_head_id = AccountHeads::where('id',$accountHeads->id)->orderBy('id','DESC')->first('head_id');

	        

	        $bank['bank_name'] = $request->bank_name;

	        $bank['account_head_id'] = $account_head_id->head_id;

	        $bank['status'] = 1;

	        

	        $samraddhbank = SamraddhBank::create($bank);

	        $bank_id = SamraddhBank::where('id',$samraddhbank->id)->orderBy('id','desc')->first('id');

	        $bank_account['account_head_id']  = $account_head_id->head_id ;

	        $bank_account['bank_id'] = $bank_id->id;

	        $bank_account['ifsc_code'] = $request->ifsc;

	        $bank_account['address'] = $request->address;

	        $bank_account['status'] = 1;

	        $bank_account['account_no'] = $request->account_number;

	        $bank_account['branch_name'] = $request->branch_name;

	        $samraddhBankAccount = SamraddhBankAccount::create($bank_account);

	        $encodeDate = json_encode($bank_account);
			$arrs = array("bank_account_id" => $samraddhBankAccount->id, "type" => "16", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Bank Account Create", "data" => $encodeDate);
			DB::table('user_log')->insert($arrs);

	       }

	       

	     DB::commit();

        }

        catch (\Exception $ex){

            DB::rollback();

            return back()->with('alert',$ex->getMessage());

        }

         return redirect()->route('admin.bank_account')->with('success', 'Bank Account Created Successfully!');

	}

	

	public function bankAccountListing(Request $request)

	{

	     if ($request->ajax()) {

	        $data = SamraddhBank::select('id','bank_name')->with(['bankAccount'=>function($q){
                $q->select('id', 'account_no', 'ifsc_code', 'bank_id', 'branch_name','address','status','account_head_id')->with(['accountHeads'=>function($q){
                    $q->select('id','head_id','labels','sub_head');
                }]);
            }])->where('status', 1)->get();

	        

	         return Datatables::of($data)

            ->addIndexColumn() 

          ->addColumn('bank_name', function($row){

                return $row->bank_name;

            })

            ->rawColumns(['bank_name']) 

            ->addColumn('branch_name', function($row){

                return $row['bankAccount']->branch_name;

            })

            ->rawColumns(['branch_name']) 

            ->addColumn('account_number', function($row){

                return  $row['bankAccount']->account_no;

            })

            ->rawColumns(['account_number']) 

            ->addColumn('ifsc_code', function($row){

                return $row['bankAccount']->ifsc_code;

            })

            ->rawColumns(['ifsc_code']) 

            ->addColumn('address', function($row){

                return $row['bankAccount']->address;

            })

            ->rawColumns(['address']) 

            

            ->addColumn('status', function($row){

                $status = 'Active';

                if ($row['bankAccount']->status==1)

                {

                    $status = 'Active';

                }                

                if($row['bankAccount']->status==0)

                {

                    $status = 'Inactive';

                }

               

                return $status;

            })

            ->rawColumns(['status']) 

             

            ->addColumn('action', function($row){ 

               $head_id=$row['bankAccount']['accountHeads'];

                 $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">'; 



                $url2 = URL::to("admin/bank_account/edit/".$row['bankAccount']->id."");  

                // $statusUrl = URL::to("admin/eli-loan/updateStatus/".$row->id."");

  
                $headLedger = URL::to("admin/account_head_ledger/transaction/".$row['bankAccount']->account_head_id.'/'.$head_id->labels."");

  

               
                $btn .= '<a class="dropdown-item" href="'.$headLedger.'" target="blank"><i class="icon-list mr-2"></i>Transactions</a>';       
                $btn .= '<a class="dropdown-item" href="'.$url2.'" title="Edit"><i class="icon-pencil7  mr-2"></i>Edit</a>  ';                    

                 $statusUrl = URL::to("admin/eli-loan/updateStatus/".$row['bankAccount']->id."");

  

                                 

                if($row->status == 0){

                    $btn .= '<button class="dropdown-item" href="#" title="Status" onclick="statusUpdate('.$row['bankAccount']->account_head_id.','.$row['bankAccount']->id.','.$row['bankAccount']->bank_id.')"><i class="icon-checkmark4 mr-2"></i>Active</button>  ';

                }else{

                  $btn .= '<button class="dropdown-item" href="#" title="Status" onclick="statusUpdate('.$row['bankAccount']->account_head_id.','.$row['bankAccount']->id.','.$row['bankAccount']->bank_id.')"><i class="icon-checkmark4 mr-2""></i>DeActive</button>  ';

              }

                return $btn;

            })

            ->rawColumns(['action'])

            ->make(true);

	     }

	}



    public function edit_bank_account ($id)

    {

        $data['title'] = "Account Head Management | Edit BankAccount";

	    $data['bank_account'] = SamraddhBankAccount::find($id);

	   

	    return view('templates.admin.bank_account.edit_bank_account',$data); 

    }

    

    public function update_bank_account(Request $request)

    {
      //dd($request->all());
        //  $rules = [
            
        //     'bank_name' => 'unique:account_heads,sub_head,'.$request->head_id,
            

        // ];

        //  $customMessages = [
        //     'required' => 'The :attribute field is unique.'
        // ];

        // $this->validate($request, $rules, $customMessages);


        try{


        	 $checkExist = AccountHeads::where('head_id','!=',$request->head_id)->where('sub_head','=',$request->bank_name)->where('labels',4)->where('parent_id',27)->exists();

           $checkExistBank = SamraddhBank::where('account_head_id','!=',$request->head_id)->where('bank_name','=',$request->bank_name)->exists();


        	if($checkExist)
        	{
        		return back()->with('alert','Bank Already exist !');
        	}
          else if($checkExistBank)
          {
              return back()->with('alert','Bank Already exist !');
          }
        	else{

        		AccountHeads::where('head_id',$request->head_id)->update(['sub_head'=>  $request->bank_name]);

           	SamraddhBank::where('account_head_id',$request->head_id)->update(['bank_name'=>  $request->bank_name]);

           

		        $bank_account['ifsc_code'] = $request->ifsc;

		        $bank_account['address'] = $request->address;

		        // $bank_account['status'] = 1;

		        $bank_account['account_no'] = $request->account_number;

		        $bank_account['branch_name'] = $request->branch_name;

		        SamraddhBankAccount::where('account_head_id',$request->head_id)->update($bank_account);

        	}

           

          DB::commit(); 

        }

        catch (\Exception $ex){

            DB::rollback();

            return back()->with('alert',$ex>getMessage());

        }

        return redirect()->route('admin.bank_account')->with('success', 'Bank Account Updated Successfully!');



    }

    

     public function updateStatus(Request $request)

    {

      

        $headStatus = AccountHeads::select(['status','id'])->where('head_id',$request->headid)->first();


        $bankStatus = SamraddhBank::select('status')->where('id',$request->bankId)->first();

        $bankAccountStatus = SamraddhBankAccount::select('status')->where('id',$request->id)->first();

        
     	$updateHeadStatus = AccountHeads::findOrFail($headStatus->id);

     	$updateBankStatus = SamraddhBank::findOrFail($request->bankId);

     	$updateBankAccountStatus = SamraddhBankAccount::findOrFail($request->id);



        if($headStatus->status == 0 && $bankStatus->status == 0 && $bankAccountStatus->status ==0){



           $updateHeadStatus->status = 1;

           $updateBankStatus->status = 1;

           $updateBankAccountStatus->status = 1;

        }

        else if($headStatus->status == 0 && $bankStatus->status == 1 && $bankAccountStatus->status ==1){



           $updateHeadStatus->status = 1;

           $updateBankStatus->status = 0;

           $updateBankAccountStatus->status = 0;

        }

         else if($headStatus->status == 1 && $bankStatus->status == 0 && $bankAccountStatus->status ==0){



           $updateHeadStatus->status = 0;

           $updateBankStatus->status = 1;

           $updateBankAccountStatus->status = 1;

        }

        else{

            $updateHeadStatus->status = 0;

            $updateBankStatus->status=0;

            $updateBankAccountStatus->status=0;



        }

       
      $updateHeadStatus=$updateHeadStatus->update();

      $updateBankStatus=$updateBankStatus->save();

      $updateBankAccountStatus=$updateBankAccountStatus->save();

      $message = [ $updateHeadStatus, $updateBankStatus,$updateBankAccountStatus];

       return response()->json($message);

    }



}