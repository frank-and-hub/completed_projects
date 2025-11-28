<?php

namespace App\Http\Controllers\Admin\EliLoan;

use Illuminate\Http\Request;

use Auth;

use App\Models\Settings;

use App\Http\Controllers\Controller;

use App\Models\Branch;

use App\Models\AccountHeads;

use Yajra\DataTables\DataTables;

use Carbon\Carbon;

use DB;

use URL;



class EliLoanController extends Controller

{





	public function __construct(){

		$this->middleware('auth');

	}



	// EliLoan  Report 

	public function index(){

		
	    if(check_my_permission( Auth::user()->id,"52") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		
		$data['title'] = "Account Head Management | Eli Loan";

	

		return view('templates.admin.eli_loan.index',$data);

	}

	

	public function createEliLoan()

	{

	    $data['title'] = "Account Head Management | Add Eli Loan";

	

	    return view('templates.admin.eli_loan.createEliLoan',$data); 

	}

	

	public function storeEliLoan (Request $request)

	{

	     $head_id =  AccountHeads::orderBy('head_id','desc')->first('head_id');

	     $rules = [

            'title' => 'required',

        ];

         $customMessages = [

            'required' => 'The :attribute field is required.'

        ];

        $this->validate($request, $rules, $customMessages);

	    try{

	        $data['sub_head'] = $request->title;

	        $data['head_id'] = $head_id->head_id + 1 ;

	        $data['parent_id'] = 96;

	        $data['labels'] = 4;

	        $data['status'] = 0;

	        

	        $accountHead = AccountHeads::create($data);
			
			$encodeDate = json_encode($data);
			$arrs = array("type" => "16", "account_head_id" => $accountHead->id, "user_id" => Auth::user()->id, "message" => "Eli Loan Create- Head Creation", "data" => $encodeDate);
			DB::table('user_log')->insert($arrs);

	     DB::commit();

        }

        catch (\Exception $ex){

            DB::rollback();

            return back()-with('alert',$ex->getMessage());

        }

         return redirect()->route('admin.eli-loan')->with('success', 'Eli Created Successfully!');

	}

	

	public function eliLoanListing (Request $request)

	{

	     if ($request->ajax()) {

	        $data = AccountHeads::where('parent_id',96)->orderBy('id','desc')->get();

	        

	         return Datatables::of($data)

            ->addIndexColumn() 

           ->addColumn('name', function($row){

                return $row->sub_head;

            })

            ->rawColumns(['name']) 

            

            ->addColumn('status', function($row){

                $status = 'Active';

                if($row->status==0)

                {

                    $status = 'Active';

                }                

                if($row->status==1)

                {

                    $status = 'Inactive';

                }

               

                return $status;

            })

            ->rawColumns(['status']) 

             

            ->addColumn('action', function($row){ 



                 $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">'; 



                $url2 = URL::to("admin/eli_loan/edit/".$row->id."");  

                $statusUrl = URL::to("admin/eli-loan/updateStatus/".$row->id."");
                $headLedger = URL::to("admin/account_head_ledger/".$row->head_id.'/'.$row->labels."");
                  
                $btn .= '<a class="dropdown-item" href="'.$headLedger.'" target="blank"><i class="icon-list mr-2"></i>Transactions</a>';       

  

                $btn .= '<a class="dropdown-item" href="'.$url2.'" title="Edit"><i class="icon-pencil7  mr-2"></i>Edit</a>  ';                    

                if($row->status == 1){

                    $btn .= '<button class="dropdown-item" href="#" title="Ledger  Detail" onclick="statusUpdate('.$row->id.')"><i class="icon-checkmark4 mr-2"></i>Active</button>  ';

                }else{

                   $btn .= '<button class="dropdown-item" href="#" title="Ledger  Detail" onclick="statusUpdate('.$row->id.')"><i class="icon-checkmark4 mr-2"></i>Deactive</button>  ';  

                }

                return $btn;

            })

            ->rawColumns(['action'])

            ->make(true);

	     }

	}



    public function editEliLoan($id)

    {

        $data['title'] = "Account Head Management | Edit Eli Loan";

	    $data['head'] = AccountHeads::find($id);

	   

	    return view('templates.admin.eli_loan.edit_eliLoan',$data); 

    }

    

    public function updateEliLoan(Request $request)

    {

        try{

            AccountHeads::where('id',$request->id)->update(['sub_head'=>  $request->title]);

           DB::commit(); 

        }

        catch (\Exception $ex){

            DB::rollback();

            return back()->with('alert',$ex>getMessage());

        }

        return redirect()->route('admin.eli-loan')->with('success', 'Eli Updated Successfully!');



    }

    

     public function updateStatus(Request $request)

    {

       

        $headStatus = AccountHeads::select('status')->where('id',$request->id)->first();

       

        $updateStatus = AccountHeads::findOrFail($request->id);

        if($headStatus->status == 0){

             $updateStatus->status = 1;

        }else{

            $updateStatus->status = 0;

        }

       

       $updateStatus=$updateStatus->save();

        

        return response()->json($updateStatus);

    }



}