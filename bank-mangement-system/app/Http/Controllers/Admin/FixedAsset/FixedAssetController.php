<?php

namespace App\Http\Controllers\Admin\FixedAsset;

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



class FixedAssetController extends Controller

{





	public function __construct(){

		$this->middleware('auth');

	}



	// EliLoan  Report 

	public function index(){

		
		if(check_my_permission( Auth::user()->id,"58") != "1"){
		  return redirect()->route('admin.dashboard');
		} 
		
		
		$data['title'] = "Account Head Management | Fixed Asset";

	    $data['head'] = AccountHeads::where('id',9)->first();



		return view('templates.admin.fixed_asset.index',$data);

	}

	

	public function create_fixed_asset ()

	{

	    $data['title'] = "Account Head Management | Add Fixed Asset";

	    $data['sub_assets'] = AccountHeads::where('parent_id',9)->get();

	

	    return view('templates.admin.fixed_asset.create_fixed_asset',$data); 

	}

	

    public function store_fixed_asset(Request $request)

    {

        

         $rules = [

            'title' => 'required',

            'asset' => 'required',

        ];

         $customMessages = [

            'required' => 'The :attribute field is required.'

        ];

        $this->validate($request, $rules, $customMessages);

        $asset = $request->asset;

        $child_asset = $request->child_asset;

        $sub_child_asset = $request->sub_child_asset;

        $head_id =  AccountHeads::orderBy('id','desc')->first('head_id');

        try{





        if($child_asset == NULL && $sub_child_asset == NULL && $asset != NULL)

        {

            $assetData = AccountHeads::where('id',$asset)->first();

            $head_data['sub_head'] = $request->title;

            $head_data['head_id'] = $head_id->head_id+1;

            $head_data['parent_id'] = 9;

            $head_data['labels'] = $assetData->labels+1;

            $head_data['status'] = 0;

        }

        elseif($asset != NULL && $child_asset != NULL && $sub_child_asset == NULL )

        {

            $assetData = AccountHeads::where('id',$child_asset)->first();



            $head_data['sub_head'] = $request->title;

            $head_data['head_id'] = $head_id->head_id+1;

            $head_data['parent_id'] = $child_asset;

            $head_data['labels'] = $assetData->labels+1;

            $head_data['status'] = 0;

        }

        elseif($asset != NULL && $child_asset != NULL && $sub_child_asset != NULL)

        {

            $assetData = AccountHeads::where('id',$sub_child_asset)->first();

            $head_data['sub_head'] = $request->title;

            $head_data['head_id'] = $head_id->head_id+1;

            $head_data['parent_id'] = $sub_child_asset;

            $head_data['labels'] = $assetData->labels+1;

            $head_data['status'] = 0;

        }

        $accountHead = AccountHeads::create($head_data);
		
		$encodeDate = json_encode($head_data);
		$arrs = array("branch_id" => 0, "type" => "16", "account_head_id" => $accountHead->id, "user_id" => Auth::user()->id, "message" => "Account Head Create", "data" => $encodeDate);
		DB::table('user_log')->insert($arrs);

        DB::commit();

        }

        catch (\Exception $ex){

            DB::rollback();

            return back()->with('alert',$ex->getMessage());

        }

         return redirect()->route('admin.fixed_asset')->with('success', 'Asset Created Successfully!');

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

            // return view('templates.admin.fixed_asset.index',$data); 

	}



    public function edit_fixed_asset($id)

    {



      $data['title'] = "Account Head Management | Edit Eli Loan";

	    $data['head'] = AccountHeads::find($id);

      if($data['head']->labels == 5)

      {

        $data['sub_child_assets'] = AccountHeads::where('head_id',$data['head']->parent_id)->where('labels', 4)->first();

         $data['child_assets'] = AccountHeads::where('head_id',$data['sub_child_assets']->parent_id)->where('labels',3)->first();

      }

      else{



       $data['child_assets'] = AccountHeads::where('head_id',$data['head']->parent_id)->where('labels',3)->first();

      }

	    $data['sub_assets'] = AccountHeads::where('parent_id',9)->get();



	    return view('templates.admin.fixed_asset.edit_fixed_asset',$data); 

    }

    





	public function getChildAsset(Request $request)

	{

		 $data['sub_child_assets'] = AccountHeads::where('parent_id',$request->child_asset_id)->get();

		return response()->json($data);

	}



	public function update_fixed_asset(Request $request)

	{



		 $rules = [

            'title' => 'required',

            'asset' => 'required',

        ];

         $customMessages = [

            'required' => 'The :attribute field is required.'

        ];

        $this->validate($request, $rules, $customMessages);

        $asset = $request->asset;

        $child_asset = $request->child_asset;

        $sub_child_asset = $request->sub_child_asset;

        $head_id =  AccountHeads::orderBy('id','desc')->first('head_id');

        try{



        

        if($child_asset == NULL && $sub_child_asset == NULL && $asset != NULL)

        {

        	$assetData = AccountHeads::where('id',$asset)->first();

        	$head_data['sub_head'] = $request->title;

        	$head_data['labels'] = $assetData->labels+1;



        	$head_data['parent_id'] = 9;

        	

        }

        elseif($asset != NULL && $child_asset != NULL && $sub_child_asset == NULL )

        {

        	$assetData = AccountHeads::where('id',$child_asset)->first();

        	$head_data['sub_head'] = $request->title;

        	$head_data['labels'] = $assetData->labels+1;

            

        	$head_data['parent_id'] = $child_asset;



        

        }

        elseif($asset != NULL && $child_asset != NULL && $sub_child_asset != NULL)

        {

          $head_data['labels'] = $assetData->labels+1;

        	$head_data['sub_head'] = $request->title;

        	$head_data['parent_id'] = $sub_child_asset;

        

        }

        AccountHeads::where('id',$request->id)->update($head_data);

        DB::commit();

        }

        catch (\Exception $ex){

            DB::rollback();

            return back()->with('alert',$ex->getMessage());

        }

         return redirect()->route('admin.fixed_asset')->with('success', 'Asset Update Successfully!');

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