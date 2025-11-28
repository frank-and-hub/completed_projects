<?php

namespace App\Http\Controllers\Admin\HrManagement;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\Member;
use App\Models\EmployeeDiploma; 
use App\Models\EmployeeExperience; 
use App\Models\EmployeeQualification;   
use App\Models\EmployeeTerminate; 
use App\Models\EmployeeTransfer; 
use App\Models\EmployeeApplication; 
use App\Models\Employee; 
use App\Models\Branch;  
use App\Models\SavingAccount;
use App\Models\Designation; 
use Carbon\Carbon;
use DB;
use URL;
use Image; 
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmployeeStatusController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
	public function index()
    {
        if(check_my_permission( Auth::user()->id,"304") != "1"){
		  return redirect()->route('admin.dashboard');
		}
        $data['title']='Employee Status | Change Status'; 
        $data['employee_code'] = '';
        return view('templates.admin.hr_management.employee_status.index', $data);
    }
	public function status_check(Request $request)
    {
		
        $data=Employee::with('company:id,name')
				->with(['branch' => function($query){ $query->select('id', 'name','branch_code');}])
				->with(['designation' => function($query){ $query->select('id', 'designation_name');}])
				->where('employee_code',$request->employee_code); 
		$data = $data->first(['id','employee_name','employee_code','is_employee','status','designation_id','branch_id','company_id','is_resigned','is_terminate']);
        $type=$request->type;
        if($data){
			$company = $data->company->name;			
			$name = $data->employee_name;
			$code = $data->employee_code;
			$branch = $data->branch->name;
			$branch_code = $data->branch->branch_code;
			$designation = $data->designation->designation_name;
			$status = $data->status==1?'Active':'Inactive';
			$is_resigne = $data->is_resigned;
			$is_terminate = $data->is_terminate;
			return \Response::json(['view' => view('templates.admin.hr_management.employee_status.partials.employee_detail' ,['name' => $name,'code' => $code,'branch' => $branch,'branch_code' => $branch_code,'designation' => $designation,'status' => $status,'company'=> $company])->render(),'msg_type'=>'success','status'=>$data->status??'N/A','is_resigne'=>$is_resigne,'is_terminate'=>$is_terminate]);
		}else{	
			return \Response::json(['view' => 'No data found','msg_type'=>'error1']);
		} 
    }
    public function show(Request $request)
    { 
		$input['status'] = $request->status==1?'0':'1';
        $data = Employee::where('is_employee',1)->where('employee_code',$request->employee_code)->update($input);
		if($input){		
			return back()->with('success', 'Employee Status Changed Successfully!');
		}else{
			return back()->with('error', 'Employee Status Not Changed !');
		};
    }
	public function listing(Request $request)
	{
		if ($request->ajax()) {
            $data = Employee::with('company:id,name')->select('id','employee_name','employee_code','is_employee','status','designation_id','branch_id','company_id','is_resigned','is_terminate')
					->with(['branch' => function($query){ $query->select('id', 'name','branch_code');}])
					->with(['designation' => function($query){ $query->select('id', 'designation_name');}])
					->where('status','0')
					->orderby('updated_at','DESC');   
			$arrFormData = array();
			$arrFormData['employee_code'] = $request->employee_code;
			if($arrFormData['employee_code'] !=''){
				$data = $data->where('employee_code','like','%' . $arrFormData['employee_code'] .'%');
			}else{
				$data = $data->where('employee_code','>','0');
			}
			$cache_data = $data->get();
            $count = $data->count('id');
            $result=$data->offset($_POST['start'])->limit($_POST['length'])->get();
			$totalCount = $count;
            $sno=$_POST['start'];
            $rowReturn = array(); 
            foreach ($result as $row)
            {  
                $sno++; 
				$val['DT_RowIndex'] = $sno;
				$val['company_name'] = $row->company->name?? 'N/A';
				$val['name'] = $row->employee_name;
				$val['code'] = $row->employee_code;
				$val['branch'] = $row->branch->name.'('.$row->branch->branch_code.')'; 
				$val['designation'] = $row->designation->designation_name;
				$state = '';
				if($row->is_resigned == 1){
					$state = '(Resigned)';
				} elseif ($row->is_terminate) {
					$state = '(Terminated)';
				}
				$val['status'] = $row->status==1?'Active':'Inactive';
				if($val['status'] == 'Inactive'){
					$val['status'] = $val['status'].$state;
				}
            $rowReturn[] = $val; 
          } 
			$Cache = Cache::put('emplyee_status_list', $cache_data);
			Cache::put('emplyee_status_count', $count);
          $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
          return json_encode($output);
        }
	}
	
	public function export(Request $request){
		
		$data = Cache::get('emplyee_status_list')->toArray();
        $count = Cache::get('emplyee_status_count');
		$input = $request->all();
		$start = $input["start"];
		$limit = $input["limit"];
		$returnURL = URL::to('/') . "/report/employeestatusexport.csv";
		$fileName = env('APP_EXPORTURL') . "report/employeestatusexport.csv";
		
		global $wpdb;
		$postCols = array(
			'post_title',
			'post_content',
			'post_excerpt',
			'post_name',
		);
		header("Content-type: text/csv");
		$totalResults=$count;
		
		$result = 'next';
		if (($start + $limit) >= $totalResults) {
			$result = 'finished';
		}
		if ($start == 0) {
			$handle = fopen($fileName, 'w');
		} else {
			$handle = fopen($fileName, 'a');
		}
		if ($start == 0) {
			$headerDisplayed = false;
		} else {
			$headerDisplayed = true;
		}
		$sno=$_POST['start'];
		$rowReturn = [];			
		$record = array_slice($data,$start,$limit);
		$totalCount = count($record);
		foreach ($record as $row) 
		{			 
			$sno++;
			$val['S/N']=$sno;
			$val['Company Name']=$row['company']['name'];
			$val['Employee Name']=$row['employee_name'];
			$val['Employee code']=$row['employee_code'];
			$val['Branch Name']=$row['branch']['name'];
			$val['Branch Code']=$row['branch']['branch_code'];
			$val['Employee Designation ']=$row['designation']['designation_name'];
			$state = '';
				if($row['is_resigned'] == 1){
					$state = '(Resigned)';
				} elseif ($row['is_terminate']) {
					$state = '(Terminated)';
				}
				$val['Status'] = $row['status']==1?'Active':'Inactive';
				if($val['Status'] == 'Inactive'){
					$val['Status'] = $val['Status'].$state;
				}
						
			if (!$headerDisplayed) {
				fputcsv($handle, array_keys($val));
				$headerDisplayed = true;
			}
			fputcsv($handle, $val);
			}
			// Close the file
			fclose($handle);
			if($totalResults == 0)
			{
				$percentage=100;
			}
			else{
				$percentage = ($start+$limit)*100/$totalResults;
				$percentage = number_format((float)$percentage, 1, '.', '');
			}
		// Output some stuff for jquery to use
		$response = array(
			'result'        => $result,
			'start'         => $start,
			'limit'         => $limit,
			'totalResults'  => $totalResults,
			'fileName' => $returnURL,
			'percentage' => $percentage
		);
		echo json_encode($response);
	}
}
