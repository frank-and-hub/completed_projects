<?php

namespace App\Http\Controllers\Admin\IndirectExpense;

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



class IndirectExpenseController extends Controller

{





	public function __construct(){

		$this->middleware('auth');

	}



	// EliLoan  Report 

	public function index(){

		
		if(check_my_permission( Auth::user()->id,"60") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		
		$data['title'] = "Account Head Management | Indirect Expense";

		$data['head'] = AccountHeads::where('id',86)->first();



		return view('templates.admin.indirect_expense.index',$data);

	}

	

	public function create_indirect_expense()

	{

	    $data['title'] = "Account Head Management | Add Indirect Expense";

		 $data['indirect_expense'] = AccountHeads::where('parent_id',86)->get();

	    return view('templates.admin.indirect_expense.create_indirect_expense',$data); 

	}

	

    public function store_indirect_expense(Request $request)

    {

        

         $rules = [

            'title' => 'required',

            'indirect_expense' => 'required',

        ];

         $customMessages = [

            'required' => 'The :attribute field is required.'

        ];

        $this->validate($request, $rules, $customMessages);

        $indirect_expense = $request->indirect_expense;

        $child_indirect_expense = $request->child_indirect_expense;

        $sub_child_indirect_expense = $request->sub_child_indirect_expense;

        $head_id =  AccountHeads::orderBy('head_id','desc')->first('head_id');

        try{

        



        if($child_indirect_expense == NULL && $sub_child_indirect_expense == NULL && $indirect_expense != NULL)

        {

            $assetData = AccountHeads::where('id',$indirect_expense)->first();

            $head_data['sub_head'] = $request->title;

            $head_data['head_id'] = $head_id->head_id+1;

            $head_data['parent_id'] = 86;

            $head_data['labels'] = $assetData->labels+1;

            $head_data['status'] = 0;

        }

        elseif($indirect_expense != NULL  && $child_indirect_expense != NULL && $sub_child_indirect_expense == NULL )

        {

            $assetData = AccountHeads::where('id',$child_indirect_expense)->first();

            $head_data['sub_head'] = $request->title;

            $head_data['head_id'] = $head_id->head_id+1;

            $head_data['parent_id'] = $child_indirect_expense;

            $head_data['labels'] = $assetData->labels+1;

            $head_data['status'] = 0;

        }

        elseif($indirect_expense != NULL && $child_indirect_expense != NULL && $sub_child_indirect_expense != NULL)

        {

            $assetData = AccountHeads::where('id',$sub_child_indirect_expense)->first();

            $head_data['sub_head'] = $request->title;

            $head_data['head_id'] = $head_id->head_id+1;

            $head_data['parent_id'] = $sub_child_indirect_expense;

            $head_data['labels'] = $assetData->labels+1;

            $head_data['status'] = 0;

        }

        $accountHeads = AccountHeads::create($head_data);
		
		$encodeDate = json_encode($head_data);
		$arrs = array("bank_account_id" => 0, "type" => "16", "account_head_id" => $accountHeads->id, "user_id" => Auth::user()->id, "message" => "Creation of indirect expense ", "data" => $encodeDate);
		DB::table('user_log')->insert($arrs);

        DB::commit();

        }

        catch (\Exception $ex){

            DB::rollback();

            return back()->with('alert',$ex->getMessage());

        }

         return redirect()->route('admin.indirect_expense')->with('success', 'Asset Created Successfully!');

    }

	

	public function fixedAssetListing (Request $request)

	{

	     // if ($request->ajax()) {

	        $data = AccountHeads::where('id',9)->get();

	        

	     //     return Datatables::of($data)

      //       ->addIndexColumn() 

      //     ->addColumn('name', function($row){

      //           return $row->sub_head;

      //       })

      //       ->rawColumns(['name']) 

            

      //       ->addColumn('status', function($row){

      //           $status = 'Active';

      //           if($row->status==1)

      //           {

      //               $status = 'Active';

      //           }                

      //           if($row->status==0)

      //           {

      //               $status = 'Inactive';

      //           }

               

      //           return $status;

      //       })

      //       ->rawColumns(['status']) 

             

      //       ->addColumn('action', function($row){ 



      //            $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">'; 



      //           $url2 = URL::to("admin/fixed_asset/edit/".$row->id."");  

      //           $statusUrl = URL::to("admin/eli-loan/updateStatus/".$row->id."");

  

      //           $btn .= '<a class="dropdown-item" href="'.$url2.'" title="Edit"><i class="icon-pencil7  mr-2"></i>Edit</a>  ';                    

      //           if($row->status == 0){

      //               $btn .= '<button class="dropdown-item" href="#" title="Ledger  Detail" onclick="statusUpdate('.$row->id.')"><i class="icon-checkmark4 mr-2"></i>Active</button>  ';

      //           }else{

      //             $btn .= '<button class="dropdown-item" href="#" title="Ledger  Detail" onclick="statusUpdate('.$row->id.')"><i class="icon-checkmark4 mr-2"></i>Deactive</button>  ';  

      //           }

      //           return $btn;

      //       })

      //       ->rawColumns(['action'])

      //       ->make(true);

	     // }

             return view('templates.admin.fixed_asset.index',$data); 

	}



    public function edit_indirect_expense($id)

    {



        $data['title'] = "Account Head Management | Edit  Indirect Expense";

	    $data['head'] = AccountHeads::find($id);

	     if($data['head']->labels == 5)

      {

        $data['sub_child_expense'] = AccountHeads::where('head_id',$data['head']->parent_id)->where('labels', 4)->first();

         $data['child_expense'] = AccountHeads::where('head_id',$data['sub_child_expense']->parent_id)->where('labels',3)->first();

      }

      else{



       $data['child_expense'] = AccountHeads::where('head_id',$data['head']->parent_id)->where('labels',3)->first();

      }

	    

	    $data['sub_expense'] = AccountHeads::where('parent_id',86)->get();

	    return view('templates.admin.indirect_expense.edit_indirect_expense',$data); 

    }

    





	public function getChildAsset(Request $request)

	{

		 $data['sub_child_assets'] = AccountHeads::where('parent_id',$request->child_asset_id)->get();

		return response()->json($data);

	}



	public function update_indirect_expense(Request $request)

	{



		

		 $rules = [

            'title' => 'required',

            'indirect_expense' => 'required',

        ];

         $customMessages = [

            'required' => 'The :attribute field is required.'

        ];

        $this->validate($request, $rules, $customMessages);

        $indirect_expense = $request->indirect_expense;

        $child_indirect_expense = $request->child_indirect_expense;

        $sub_child_indirect_expense = $request->sub_child_indirect_expense;

        $head_id =  AccountHeads::orderBy('head_id','desc')->first('head_id');

        try{

        	

        if($child_indirect_expense == NULL && $sub_child_indirect_expense == NULL && $indirect_expense != NULL)

        {

        	$assetData = AccountHeads::where('id',$indirect_expense)->first();

        	$head_data['sub_head'] = $request->title;

        	$head_data['labels'] = $assetData->labels+1;

        	$head_data['parent_id'] = 9;

        	

        }

        elseif($indirect_expense != NULL && $sub_child_indirect_expense == NULL && $child_indirect_expense != NULL )

        {

        	$assetData = AccountHeads::where('id',$child_indirect_expense)->first();

        	$head_data['sub_head'] = $request->title;

        	$head_data['labels'] = $assetData->labels+1;

        	$head_data['parent_id'] = $child_indirect_expense;

        	

        }

        elseif($indirect_expense != NULL && $child_indirect_expense != NULL && $sub_child_indirect_expense != NULL)

        {

        	$assetData = AccountHeads::where('id',$sub_child_indirect_expense)->first();

        	$head_data['labels'] = $assetData->labels+1;

        	$head_data['sub_head'] = $request->title;

        	$head_data['parent_id'] = $sub_child_indirect_expense;

        

        }

        AccountHeads::where('id',$request->id)->update($head_data);

        DB::commit();

        }

        catch (\Exception $ex){

            DB::rollback();

            return back()->with('alert',$ex->getMessage());

        }

         return redirect()->route('admin.indirect_expense')->with('success', 'Asset Update Successfully!');

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