<?php

namespace App\Http\Controllers\Admin\HrManagement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\Designation;  
use Carbon\Carbon;
use DB;
use URL;
use Image;
use Yajra\DataTables\DataTables;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Designation Management DesignationController
    |--------------------------------------------------------------------------
    |
    | This controller handles Designation all functionlity.
*/

class DesignationController extends Controller
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
     * Show Designation.
     * Route: admin/hr/designation
     * Method: get 
     * @return  array()  Response
     */
    public function index()
    {
        if(check_my_permission( Auth::user()->id,"107") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		$data['title']='Designation Management | Designation List'; 
        return view('templates.admin.hr_management.designation.index', $data);
    }
    /**
     * Get cheque list
     * Route: ajax call from - admin/hr/designation
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function designationListing(Request $request)
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
                $status = $arrFormData['status'];
                $data = Designation::select('id','designation_name','category','basic_salary','daily_allowances','hra','hra_metro_city','uma','convenience_charges','maintenance_allowance','communication_allowance','prd','ia','ca','fa','pf','tds','status','created_at')->where('status',$status);
            } 
        }
        else
        {
            $data = Designation::select('id','designation_name','category','basic_salary','daily_allowances','hra','hra_metro_city','uma','convenience_charges','maintenance_allowance','communication_allowance','prd','ia','ca','fa','pf','tds','status','created_at')->where('status','!=',9);
        }
        $count = $data->count('id');
        $totalCount = Designation::orderby('id','DESC')->count('id');
        $data = $data->orderby('id','DESC')->offset($_POST['start'])->limit($_POST['length'])->get();

        $sno=$_POST['start'];
        $rowReturn = array(); 

        foreach ($data as $row)
        {  
            $sno++;                
            $val['DT_RowIndex'] = $sno;
            $val['designation_name'] = $row->designation_name;

            if($row->category==1)
            {
                $category = 'On-rolled';
            }                
            if($row->category==2)
            {
                $category = 'Contract';
            }

            $val['category'] = $category;

            $sum = $row->basic_salary+$row->daily_allowances+$row->hra+$row->hra_metro_city+$row->uma +$row->convenience_charges+$row->maintenance_allowance+$row->communication_allowance+$row->prd+$row->ia+$row->ca+$row->fa;

                $deduction = $row->pf+$row->tds;
                $total = $sum-$deduction;
                $gross_salary = number_format((float)$total, 2, '.', '');

            $val['gross_salary'] = $gross_salary;
            $val['basic_salary'] = number_format((float)$row->basic_salary, 2, '.', '');
            $val['daily_allowances'] = number_format((float)$row->daily_allowances, 2, '.', '');
            $val['hra'] = number_format((float)$row->hra, 2, '.', '');
            $val['hra_metro_city'] = number_format((float)$row->hra_metro_city, 2, '.', '');
            $val['uma'] = number_format((float)$row->uma, 2, '.', '');
            $val['convenience_charges'] = number_format((float)$row->convenience_charges, 2, '.', '');
            $val['maintenance_allowance'] = number_format((float)$row->maintenance_allowance, 2, '.', '');
            $val['communication_allowance'] = number_format((float)$row->communication_allowance, 2, '.', '');
            $val['prd'] = number_format((float)$row->prd, 2, '.', '');
            $val['ia'] = number_format((float)$row->ia, 2, '.', '');
            $val['ca'] = number_format((float)$row->ca, 2, '.', '');
            $val['fa'] = number_format((float)$row->fa, 2, '.', '');
            $val['pf'] = number_format((float)$row->pf, 2, '.', '');
            $val['tds'] = number_format((float)$row->tds, 2, '.', '');

            $status = 'Active';
            if($row->status==1)
            {
                $status = 'Active';
            }                
            if($row->status==0)
            {
                $status = 'Inactive';
            }
            if($row->status==9)
            {
                $status = 'Deleted';

            }

            $val['status'] = $status;
            $val['created_at'] = date("d/m/Y", strtotime($row->created_at));

            $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">'; 

            //$url = URL::to("admin/hr/designation/delete/".$row->id."");  
            $url2 = URL::to("admin/hr/designation/edit/".$row->id."");  
            $url4 = URL::to("admin/hr/designation/detail/".$row->id."");   
            $btn .= '<a class="dropdown-item" href="'.$url2.'" title="Designation Edit"><i class="icon-pencil7  mr-2"></i>Designation Edit</a>  ';                    
            $btn .= '<a class="dropdown-item" href="'.$url4.'" title="Designation View"><i class="icon-eye8  mr-2"></i>Designation View</a>  ';
            if($row->status!=9)
            {
            $btn .= '<a class="dropdown-item" href="javascript:void(0)" title="Designation Delete" onclick=deleteDesignation("'.$row->id.'");><i class="icon-trash-alt  mr-2"></i>Designation Delete</a>  ';
            $btn .= '</div></div></div>';            
            }

            $val['action'] = $btn;            
            $rowReturn[] = $val;
        

        }
        $output = array("branch_id"=>Auth::user()->branch_id, "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
        return json_encode($output);
    }
}
    /**
     * Designation delete.
     * Route: admin/hr/designation/delete
     * Method: get 
     * @return  array()  Response
     */
    public function designationDelete($id)
    {
        DB::beginTransaction();
        try {
                $delete['status'] = 9;          
                $deleteupdate = Designation::find($id);
                $deleteupdate->update($delete);           
                 
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
         return redirect()->route('admin.hr.designation_list')->with('success', 'Designation Deleted Successfully');
    }
    /**
     * Show Designation Detail.
     * Route: admin/hr/designation/detail
     * Method: get 
     * @return  array()  Response
     */
    public function detail($id)
    {
        $data['title']='Designation Management | Designation Detail'; 
        $data['designation']=Designation::select('id','designation_name','category','basic_salary','daily_allowances','hra','hra_metro_city','uma','convenience_charges','maintenance_allowance','communication_allowance','prd','ia','ca','fa','pf','tds','status','created_at')->where('id',$id)->first();
        return view('templates.admin.hr_management.designation.detail', $data);
    }
     /**
     * Add  Designation.
     * Route: admin/hr/designation/add
     * Method: get 
     * @return  array()  Response
     */
    public function add()
    {
        if(check_my_permission( Auth::user()->id,"106") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		$data['title']='Designation Management | Designation Add'; 
        return view('templates.admin.hr_management.designation.add', $data);
    }
    /**
     * save  Designation.
     * Route: admin/hr/designation/add
     * Method: get 
     * @return  array()  Response
     */
    public function designationSave(Request $request)
    {

        DB::beginTransaction();
        try {

            $data['designation_name'] = $request->designation_name; 
            $data['category'] = $request->category;
            $data['basic_salary'] = $request->basic_salary;   
            $data['daily_allowances'] = $request->daily_allowances;
            $data['hra'] = $request->hra; 
            $data['hra_metro_city'] = $request->hra_metro_city;
            $data['uma'] = $request->uma;   
            $data['convenience_charges'] = $request->convenience_charges;
            $data['maintenance_allowance'] = $request->maintenance_allowance; 
            $data['communication_allowance'] = $request->communication_allowance;
            $data['prd'] = $request->prd;   
            $data['ia'] = $request->ia;
            $data['ca'] = $request->ca; 
            $data['fa'] = $request->fa;
            $data['pf'] = $request->pf;   
            $data['tds'] = $request->tds;
            $data['status'] = 1;
            $data['created_at'] = $request->created_at;            
            $create = Designation::create($data);
            

                 
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
         return redirect('admin/hr/designation')->with('success', 'Designation Created Successfully');
    }

    /**
     * Edit  Designation.
     * Route: admin/hr/designation/edit
     * Method: get 
     * @return  array()  Response
     */
    public function edit($id)
    {
        $data['title']='Designation Management | Designation Edit'; 
        $data['designation'] = Designation::select('id','designation_name','category','basic_salary','daily_allowances','hra','hra_metro_city','uma','convenience_charges','maintenance_allowance','communication_allowance','prd','ia','ca','fa','pf','tds')->where('id',$id)->first();
        return view('templates.admin.hr_management.designation.edit', $data);
    }
    /**
     * Update  Designation.
     * Route: admin/hr/designation/edit
     * Method: get 
     * @return  array()  Response
     */
    public function designationUpdate(Request $request)
    {

        DB::beginTransaction();
        try {
            
            $data['designation_name'] = $request->designation_name; 
            $data['category'] = $request->category;
            $data['basic_salary'] = $request->basic_salary;   
            $data['daily_allowances'] = $request->daily_allowances;
            $data['hra'] = $request->hra; 
            $data['hra_metro_city'] = $request->hra_metro_city;
            $data['uma'] = $request->uma;   
            $data['convenience_charges'] = $request->convenience_charges;
            $data['maintenance_allowance'] = $request->maintenance_allowance; 
            $data['communication_allowance'] = $request->communication_allowance;
            $data['prd'] = $request->prd;   
            $data['ia'] = $request->ia;
            $data['ca'] = $request->ca; 
            $data['fa'] = $request->fa;
            $data['pf'] = $request->pf;   
            $data['tds'] = $request->tds;
            $data['status'] = 1;
            $data['created_at'] = $request->created_at;            
            $updatedata = Designation::find($request->id);
            $updatedata->update($data);
            

                 
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
         return redirect('admin/hr/designation')->with('success', 'Designation Updated Successfully');
    }
    
}
