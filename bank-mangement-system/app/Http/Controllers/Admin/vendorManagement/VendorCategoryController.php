<?php

namespace App\Http\Controllers\Admin\vendorManagement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\Vendor;
use App\Models\VendorCategory;  
use Carbon\Carbon;
use DB;
use URL;
use Image;
use Yajra\DataTables\DataTables;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Vendor Management VendorCategoryController
    |--------------------------------------------------------------------------
    |
    | This controller handles Vendor Category  all functionlity.
*/

class VendorCategoryController extends Controller
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
     * Show Vendor Category .
     * Route: admin/vendor/category
     * Method: get 
     * @return  array()  Response
     */
    public function index()
    {       
        if(check_my_permission(Auth::user()->id,"162") != "1")
        {
            return redirect()->route('admin.dashboard');
         }    
		$data['title']='Vendor Management | Vendor Category  List'; 
        return view('templates.admin.vendor_management.category.index', $data);
    }
    /**
     * Get cheque list
     * Route: ajax call from - admin/vendor/category
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function list(Request $request)
    { 
        if ($request->ajax()) {

        $arrFormData = array();   
        if(!empty($_POST['searchform']))
        {
            foreach($_POST['searchform'] as $frm_data)
            {
                $arrFormData[$frm_data['name']] = $frm_data['value'];
            }
        } 

           
        /******* fillter query start ****/        
        if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
        { 
            if($arrFormData['status'] !='')
            {
                $status=$arrFormData['status'];
                $data=VendorCategory::select('id','name','status','created_at','updated_at')->where('status',$status)->orderby('id','DESC')->get();
            } 
        }
        else
        {
            $data=VendorCategory::select('id','name','status','created_at','updated_at')->orderby('id','DESC')->get();
        }


            return Datatables::of($data)
            ->addIndexColumn() 
           ->addColumn('cname', function($row){
                $name = $row->name;
                return $name;
            })
            ->rawColumns(['cname']) 
            
            ->addColumn('status', function($row){
                $status = 'Active';
                if($row->status==1)
                {
                    $status = 'Active';
                }                
                if($row->status==0)
                {
                    $status = 'Inactive';
                } 
                return $status;
            })
            ->rawColumns(['status']) 
            ->addColumn('created_at', function($row){
                $created_at = date("d/m/Y", strtotime($row->created_at));
                return $created_at;
            })
            ->rawColumns(['updated_at'])   
            ->addColumn('updated_at', function($row){
                $updated_at = date("d/m/Y", strtotime($row->updated_at));
                return $updated_at;
            })
            ->rawColumns(['updated_at'])        
            ->addColumn('action', function($row){ 
				
				$btn = "";
				
				if(check_my_permission( Auth::user()->id,"193") == "1" || check_my_permission( Auth::user()->id,"194") == "1"){
                 $btn .= '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';  
				}				 
                $url2 = URL::to("admin/vendor/category/edit/".$row->id."");   
				if(check_my_permission( Auth::user()->id,"193") == "1"){
					$btn .= '<a class="dropdown-item" href="'.$url2.'" title="Vendor Category Edit"><i class="icon-pencil7  mr-2"></i>Vendor Category Edit</a>  '; 
				}
                $chk=0;
                if($chk==0)
                {
					if(check_my_permission( Auth::user()->id,"194") == "1"){
						$btn .= '<a class="dropdown-item" href="javascript:void(0)" title="Vendor Category Delete" onclick=deleteCategory("'.$row->id.'");><i class="icon-trash-alt  mr-2"></i>Vendor Category Delete</a>  ';
					}
					$btn .= '</div></div></div>';  
                } 
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }
    /**
     * Vendor Category  delete.
     * Route: admin/vendor/category/delete
     * Method: get 
     * @return  array()  Response
     */
    public function categoryDelete($id)
    {
        DB::beginTransaction();
        try {
            $chk=Vendor::where('vendor_category', 'LIKE', '%' . $id)->count();

            if($chk==0)
            {
                $deleteupdate = VendorCategory::whereId($id)->delete();
            }  
            else{
                return redirect()->route('admin.vendor.category')->with('alert', 'You can not delete this category because it is already assigned to a vendor!.');
            }         
                 
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
         return redirect()->route('admin.vendor.category')->with('success', 'Vendor Category Deleted Successfully');
    }
   
     /**
     * Add  Vendor Category .
     * Route: admin/vendor/category/add
     * Method: get 
     * @return  array()  Response
     */
    public function add()
    { 
         if(check_my_permission(Auth::user()->id,"161") != "1")
        {
            return redirect()->route('admin.dashboard');
         }
		$data['title']='Vendor Management | Vendor Category  Add'; 
        return view('templates.admin.vendor_management.category.add', $data);
    }
    /**
     * save  Vendor Category .
     * Route: admin/vendor/category/add
     * Method: get 
     * @return  array()  Response
     */
    public function categorySave(Request $request)
    {

        DB::beginTransaction();
        try {

            $data['name'] = $request->name;             
            $data['status'] = $request->status; 
            $data['created_at'] = $request->created_at;            
            $create = VendorCategory::create($data);
            

                 
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
         return redirect('admin/vendor/category')->with('success', 'Vendor Category  Created Successfully');
    }

    /**
     * Edit  Vendor Category .
     * Route: admin/vendor/category/edit
     * Method: get 
     * @return  array()  Response
     */
    public function edit($id)
    {
		if(check_my_permission(Auth::user()->id,"193") != "1")
        {
            return redirect()->route('admin.dashboard');
         }
		 
        $data['title']='Vendor Management | Vendor Category  Edit'; 
        $data['category']=VendorCategory::where('id',$id)->first();
        return view('templates.admin.vendor_management.category.edit', $data);
    }
    /**
     * Update  Vendor Category .
     * Route: admin/vendor/category/edit
     * Method: get 
     * @return  array()  Response
     */
    public function categoryUpdate(Request $request)
    {
        DB::beginTransaction();
        try {
            
            
            $chk=Vendor::where('vendor_category', 'LIKE', '%' . $request->id)->count();  

            if($chk == 0)
            {
                $data['name'] = $request->name;             
                $data['status'] = $request->status; 
                // $data['created_at'] = $request->created_at; 
                $updatedata = VendorCategory::find($request->id);
                 $updatedata->update($data);
            }
            else{
                $data['name'] = $request->name;             
                // $data['created_at'] = $request->created_at;
                $updatedata =VendorCategory::find($request->id);
                $updatedata->update($data);
               
            }        
            
            
            

                 
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
        if($chk == 0)
        {
            return redirect('admin/vendor/category')->with('success', 'Vendor Category  Updated Successfully');
        
        }
        else{
            if($request->status == 0)
            {
                return redirect('admin/vendor/category')->with('alert', 'You can update only Name of category, because category already assigned to a vendor!');
            }
            else{
                return redirect('admin/vendor/category')->with('success', 'Vendor Category  Updated Successfully');

            }
            
        }
    }
    
}
