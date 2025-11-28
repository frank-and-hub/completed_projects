<?php 

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Request1;
use Validator;
use App\Models\Member; 
use App\Http\Controllers\Admin\CommanController;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Services\Sms; 
use App\Models\AppPermissions; 



/*
    |---------------------------------------------------------------------------

    | Admin Panel -- Associate Management AssociateController

    |--------------------------------------------------------------------------

    |

    | This controller handles associate all functionlity.

*/
class AssociateAppController extends Controller
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
     * Associate Change status.
     * Route: /admin/associate-upgrade
     * Method: get appStatus
     * @return  array()  Response
     */
    public function appStatus()
    {
        if(check_my_permission(Auth::user()->id,"150") != "1")
        {
            return redirect()->route('admin.dashboard');
         }
        $data['title']='Associate App | Status'; 
        return view('templates.admin.associate_app.status', $data);
        
    }

/**
     * Get Member detail through member code.
     * Route: ajax call from -admin/associate-register
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */

    public function getAssociateDataAll(Request $request)
    {
        $data=Member::where('associate_no',$request->code)->where('is_deleted',0);  

        if(Auth::user()->branch_id>0){

          $data=$data->where('branch_id',Auth::user()->branch_id);

        }
        $data=$data->first();   
        $type=$request->type;
        if($data)
        {            

         if($data->is_block==1)
        {
            return \Response::json(['view' => 'No data found','msg_type'=>'error2']);
        }
        else
        {
                $id=$data->id;
                $carder=$data->current_carder_id;

            return \Response::json(['view' => view('templates.admin.associate_app.partials.associate_detail' ,['memberData' => $data,'carder' => $carder,'type'=>$type])->render(),'msg_type'=>'success','id'=>$id,'carder'=>$carder]);

            }
        }
        else
        {
            return \Response::json(['view' => 'No data found','msg_type'=>'error']);
        }
    }
    /**
     * Route: /admin/associate/status
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * upgrade associate carder.
     * @return  array()  Response
     */

    public function status_save(Request $request)
    {
        Session::put('created_at', $request['created_at']);
         $globaldate=$request['created_at']; 
        $rules = [
            'associate_code' => ['required','numeric'],
            'app_status' => ['required'], 
        ];
        $customMessages = [
            'required' => 'Please enter :attribute.',
            'numeric' => ':Attribute - Please enter valid.',
            'unique' => ' :Attribute already exists.'        
        ];
         $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {
            $id=$request['member_id'];
            $app_status=$request['app_status']; 

            $member['associate_app_status'] = $app_status;
            $memberDataUpdate = Member::find($id);
            $memberDataUpdate->update($member);

            DB::commit();

        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        } 
        return back()->with('success', 'Associate App Status Changed Successfully!');
    }



    /**
     * Get associate list
     * Route: ajax call from - /admin/associate
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */

    public function inactiveAssociateListing(Request $request)
    {
        
        $data = Member::select('id','associate_join_date','associate_no','first_name','last_name','email','mobile_no',
            'associate_senior_code','associate_status','is_block','associate_app_status','associate_branch_id','associate_senior_id','member_id')
                ->with(['associate_branch' => function($q){ $q->select('id','name','branch_code','sector','regan','zone'); }])
                ->with(['seniorData' => function($q){ $q->select('id','first_name','last_name','associate_no'); }])
                ->where('id','!=',1)->where('is_associate',1)->where('associate_app_status',0);            

            if(Auth::user()->branch_id>0){

             $id=Auth::user()->branch_id;

             $data=$data->where('associate_branch_id','=',$id);

            }

            $count = $data->count('id');
            
            $data=$data->orderby('associate_join_date','DESC')->offset($_POST['start'])->limit($_POST['length'])->get();                  

                        
            $totalCount =$count;

           

            $sno=$_POST['start'];
            $rowReturn = array();
            foreach ($data as $row)
            {   
               // print_r($row['seniorData']->associate_no);die;
                $sno++;
                $val['DT_RowIndex']=$sno;
                $val['join_date']=date("d/m/Y", strtotime($row->associate_join_date));
                $val['member_id']=$row->member_id;
                $val['branch']='';
                if($row['associate_branch'])
                {
                    $val['branch']=$row['associate_branch']->name;
                }
                 
                // $val['branch_code']=$row['associate_branch']->branch_code;
                // $val['sector_name']=$row['associate_branch']->sector;
                // $val['region_name']=$row['associate_branch']->regan;
                
                $val['associate_no']=$row->associate_no;
                $val['name']=$row->first_name.' '.$row->last_name;
                $val['email']=$row->email;
                $val['mobile_no']=$row->mobile_no;
                $val['associate_code']='N/A';
                $val['associate_name']='N/A';
                if($row['seniorData'])
                {
                    $val['associate_code']=$row['seniorData']->associate_no;
                    $val['associate_name']= $row['seniorData']->first_name.' '.$row['seniorData']->last_name; //getSeniorData($row->associate_senior_id,'first_name').' '.getSeniorData($row->associate_senior_id,'last_name');
                
                }
                if($row->is_block==1)
                {
                    $status = 'Blocked';
                }
                else
                {
                        if($row->associate_status==1)
                        {
                          $status = 'Active';
                        }
                        else
                        {
                            $status = 'Inactive';
                        }
                }  

                $val['status']=$status; 
                if($row->associate_app_status==1)
                {
                    $app_status = 'Active';
                }
                else
                {
                    $app_status = 'Inactive';
                }
                $val['app_status']=$app_status; 
            $rowReturn[] = $val; 

          } 

          $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );

          return json_encode($output);
        
    }



    /**
     * Associate app transaction .
     * Route: /admin/associate-app-transaction
     * Method: get appStatus
     * @return  array()  Response
     */
    public function transactionDetail()
    {
        if(check_my_permission(Auth::user()->id,"151") != "1")
        {
            return redirect()->route('admin.dashboard');
         }
         
        $data['title']='Associate App | Status'; 
        return view('templates.admin.associate_app.transaction', $data);
    }

    public function transactionList(Request $request)
    { 

        $type = $request->type;
        $associate_code = $request->associate_code;
        $associate_name = $request->associate_name;
        $associate_id = $request->associate_id;
        $is_search = $request->is_search;

            
            $pid=1;
            $data =\App\Models\Daybook::with(['dbranch','member','associateMember','investment' => function($query){ $query->select('id', 'plan_id','account_number','tenure');}])->whereHas('investment', function ($query) use ($pid) {$query->where('member_investments.plan_id','!=',$pid); })->where('transaction_type',4);  

            $data1=$data->orderby('created_at','DESC')->get();
            $count=count($data1);
            $data=$data->orderby('created_at','DESC')->offset($_POST['start'])->limit($_POST['length'])->get();



            $dataCount = Member::where('member_id','!=','9999999')->where('is_associate',1);
            $totalCount =$dataCount->count();                    

            $sno=$_POST['start'];
            $rowReturn = array(); 
            foreach ($data as $row)
            {  

                $sno++;
                $val['DT_RowIndex']=$sno;
                $val['created_at']=date("d/m/Y", strtotime( $row['created_at']));
                 $val['branch']=$row['dbranch']['name'];
                 $val['branch_code']=$row['dbranch']['branch_code'];
                 $val['sector_name']=$row['dbranch']['sector'];
                 $val['region_name']=$row['dbranch']['sector'];
                 $val['zone_name']=$row['dbranch']['zone'];
                 $val['member_id']=$row['member']['member_id'];
                 $val['account_number']=$row['account_no'];
                 //Account Holder Name
                 $val['member']=$row['member']['first_name'].' '.$row['member']['last_name'];
                         $planId=$row['investment']['plan_id'];
                $planName='';
                if($planId>0){
                $PlanDetail=getPlanDetail($planId);
                 if(!empty($PlanDetail)){
                 $planName=$PlanDetail->toArray()['name'];
                 }
                }   

                 $val['plan']=$planName;
                 $tenure='';
                 if($planId==1)
                        {
                          $tenure = 'N/A';
                        }
                        else
                        {
                          $tenure = $row['investment']['tenure'].' Year';
                        }
                         $val['tenure']=$tenure;
                 $val['amount']=number_format((float)$row['amount'], 2, '.', '');
                 $val['associate_code']=$row['associateMember']['associate_no'];
                 $val['associate_name']=$row['associateMember']['first_name'].' '.$row['associateMember']['last_name'];
                 $mode = '';
                 if($row->payment_mode == 0)
                 {
                    $mode = "Cash";
                 }
                 elseif($row->payment_mode == 1)
                 {
                     $mode = "Cheque";
                 }
                  elseif($row->payment_mode == 2)
                 {
                     $mode = "DD";
                 }
                  elseif($row->payment_mode == 3)
                 {
                     $mode = "Online";
                 }
                 elseif($row->payment_mode == 4)
                 {
                     $mode = "By Saving Account";
                 }
                 elseif($row->payment_mode == 5)
                 {
                     $mode = "From Loan Amount";
                 }         
                  $val['payment_mode']=$mode;
            $rowReturn[] = $val; 
          } 
          $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
          return json_encode($output);

        
    }
    
    // App Permission functionality start here
    public function Permission(){
        $data['title']='Associate Permission';
        $arr = AppPermissions::select('id','name','parent_id')->get()->toArray();
        
        $data['mem_data'] = Member::select('id', 'first_name', 'last_name')->where('is_associate', '1')->where('associate_status', 1)->where('id', '>', 1)->get();
        
        $new = array();
        foreach ($arr as $a){
            $new[$a['parent_id']][] = $a;
        }
        $data['permissions'] = $this->createTree($new, $new[0]);
        return view('templates.admin.associate_management.permission', $data);
    }
    
    public function createTree(&$list, $parent){
        $tree = array();
        foreach ($parent as $k=>$l){
            if(isset($list[$l['id']])){
                $l['children'] = $this->createTree($list, $list[$l['id']]);
            }
            $tree[] = $l;
        }
        return $tree;
    }
    
    public function app_permission_save(Request $request){
        $user_id = $request->user_id;
        
        if(isset($_POST["permissionName"])){
            $permissionArr = $_POST["permissionName"];
        } else {
            $permissionArr = array();
        }
        //dd($permissionArr);
        DB::table('associate_given_permission')->where('user_id', $user_id)->delete();
        
        if(!empty($permissionArr)){
            foreach ($permissionArr as $key => $value){
                $array = array("permission_id" => $key, "user_id" => $user_id);
                DB::table('associate_given_permission')->insert($array);
            }
            return redirect()->route('admin.associate.app_permission')->with('success', 'Permission Added Successfully!');
        }
    }
    
    public function associatePermission(Request $request){
        $user_id = $request->user_id;

        $arr = AppPermissions::select('id','name','parent_id')->get()->toArray();
        
        $userPermissionArr = array();
        $user_given_permission  = DB::table('associate_given_permission')->where("user_id", $user_id)->get();
        
        for($q=0; $q<count($user_given_permission); $q++){
            array_push($userPermissionArr, $user_given_permission[$q]->permission_id);
        }

        Session::forget('userPermissions');
        Session::put('userPermissions', $userPermissionArr);
        $otp_get = Session::get('userPermissions');
        //dd($otp_get);
        
        $new = array();
        foreach ($arr as $a){
            $new[$a['parent_id']][] = $a;
        }

        $data['permissions'] = $this->createTree($new, $new[0]);
        //dd($data['permissions']);
        $data['userPermissions'] = $userPermissionArr;
        $data['user_id'] = $user_id;
        
        return json_encode($data);
        //return view('templates.admin.associate_management.permission', $data);
    }
    // App Permission functionality end here
}

