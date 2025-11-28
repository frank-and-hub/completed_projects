<?php 
namespace App\Http\Controllers\Admin; 

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Spatie\Permission\Models\Permission;
use App\Models\RoleHasPermission;
use App\Models\UserEmployees;
use App\Models\Employee;
use Carbon\Carbon;
use URL;
use DB;
use View;

/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Member Management CorrectionController
    |--------------------------------------------------------------------------
    |
    | This controller handles members all functionlity.
*/
class UserController extends Controller
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
    public function index()
    {
        if(check_my_permission( Auth::user()->id,"76") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		$data['title']='User Listing';
        return view('templates.admin.user.user-listing', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function userListing(Request $request)
    { 
        if ($request->ajax()) {

            $data=User::with('userEmployee')->where('role_id',5)->orderBy('id', 'desc')->get();

            return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('user_name', function($row){
                $username = $row->username;
                return $username;
            })
            ->rawColumns(['user_name'])
            ->addColumn('employee_code', function($row){
                $account_type = $row['userEmployee']->employee_code;
                return $account_type;
            })
            ->rawColumns(['employee_code'])
            ->addColumn('employee_name', function($row){
                $account_number = $row['userEmployee']->employee_name;
                return $account_number;
            })
            ->rawColumns(['employee_name'])
            ->addColumn('user_id', function($row){
                $user_id = $row['userEmployee']->employee_user_id;
                return $user_id;
            })
            ->rawColumns(['user_id'])
            ->addColumn('status', function($row){
                if($row->status == 0){
                    $status = 'Active';
                }else{
                    $status = 'Deactive';        
                }
                
                return $status;
            })
            ->rawColumns(['status'])
            ->addColumn('action', function($row){
                $url = URL::to("admin/user/edituser/".$row->id."");
                $statusUrl = URL::to("admin/user/updatestatus/".$row->id."");

                $viewUrl = URL::to("admin/user/view/".$row->id."");

                $deleteurl = URL::to("admin/user/deleteuser/".$row->id."");
                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                $btn .= '<a class="dropdown-item" href="'.$url.'"><i class="icon-pencil7 mr-2"></i>Edit</a>';

                if($row->status == 0){
                    $btn .= '<a class="dropdown-item" href="'.$statusUrl.'"><i class="icon-pencil7 mr-2"></i>Deactive</a>';
                }else{
                    $btn .= '<a class="dropdown-item" href="'.$statusUrl.'"><i class="icon-pencil7 mr-2"></i>Active</a>';   
                }
                $btn .= '<a class="dropdown-item" href="'.$viewUrl.'"><i class="fas fa-eye"></i>View</a>';
                $btn .= '<a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-history"></i>User History</a>';
                $btn .= '<a class="dropdown-item delete-user" href="'.$deleteurl.'"><i class="fas fa-trash-alt"></i>Delete</a>';

                $btn .= '</div></div></div>';          
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }

    /**
     * Get employee code by employee name.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getEmployeeCode(Request $request)
    {
        $employee_code = $request->employee_code;
        $employeeName = Employee::select('employee_name')->where('employee_code',$employee_code)->get();
        $resCount = count($employeeName);
        $return_array = compact('employeeName','resCount');
        return json_encode($return_array); 
    }

    /**
     * Add Account Head View.
     * Route: /member/passbook
     * Method: get 
     * @return  array()  Response
     */
    public function addUser()
    {
        if(check_my_permission( Auth::user()->id,"75") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'Add User';
        $arr = Permission::select('id','name','parent_id')->get()->toArray();
        $new = array();
        foreach ($arr as $a){
            $new[$a['parent_id']][] = $a;
        }
        $data['permissions'] = $this->createTree($new, $new[0]);
        return view('templates.admin.user.adduser',$data);
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

    /**
     * Save Account Head.
     * Route: /save-account-head
     * Method: get 
     * @return  array()  Response
     */
    public function saveUser(Request $request)
    {
        $rules = [
            'username' => 'required|unique:users|max:255',
            'employee_code' => 'required',
            'employee_name' => 'required',
            'user_id' => 'required',
            'password' => 'required',
        ];


        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];

        $this->validate($request, $rules, $customMessages);
        
        $user = new User();
        $user->password = Hash::make($request->password);
        $user->name = $request->username;
        $user->username = $request->username;
        $user->role_id = 5;
        $user->status = 0;
        //$user->created_at = $request->created_at;
        $userCheck = $user->save();
		
		$encodeDate = json_encode($user);
		$arrs = array("register_user_id" => $user->id, "type" => "10", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "User create 1", "data" => $encodeDate);
		DB::table('user_log')->insert($arrs);
		

        $employee = new UserEmployees();
        $employee->user_id = $user->id;
        $employee->employee_code = $request->employee_code;
        $employee->employee_name = $request->employee_name;
        $employee->employee_user_id = $request->user_id;
        $employee = $employee->save();

        $user->assignRole('User');

        if($request['set_permission'] != ''){
	        foreach ($request->userpermission as $key => $value) {
	            $user->givePermissionTo(''.$value.'');
	        }
	    }

        if ($userCheck) {
            return redirect()->route('admin.users')->with('success', 'User Created Successfully!');
        } else {
            return back()->with('alert', 'Problem With Creating New User');
        }
    }

    /**
     * Edit user View.
     * Route: /member/passbook
     * Method: get sss
     * @return  array()  Response
     */
    public function editUser($id)
    {
        $data['title'] = 'Edit User';
        $data['users'] = User::with('userEmployee')->where('id',$id)->where('role_id',5)->first();
        $data['employee'] = $data['users']['userEmployee'];
        $data['id'] = $id;
        $arr = Permission::select('id','name','parent_id')->get()->toArray();
        $new = array();
        foreach ($arr as $a){
            $new[$a['parent_id']][] = $a;
        }
        $data['permissions'] = $this->createTree($new, $new[0]);

        $data['userPermissions'] = User::find($id)->getPermissionNames()->toArray();
        return view('templates.admin.user.edituser', $data);
    }

    /**
     * User View.
     * Route: /member/passbook
     * Method: get sss
     * @return  array()  Response
     */
    public function viewUser($id)
    {
        $data['title'] = 'User Details';
        $data['user'] = User::with('userEmployee')->where('id',$id)->where('role_id',5)->first();
        $data['employee'] = $data['user']['userEmployee'];
        $data['id'] = $id;
        $arr = Permission::select('id','name','parent_id')->get()->toArray();
        $new = array();
        foreach ($arr as $a){
            $new[$a['parent_id']][] = $a;
        }
        $data['permissions'] = $this->createTree($new, $new[0]);

        $data['userPermissions'] = User::find($id)->getPermissionNames()->toArray();
        return view('templates.admin.user.user-details', $data);
    }

    /**
     * Update the specified accounthead.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateUser(Request $request)
    {
        $rules = [
            'username' => 'required',
            'employee_code' => 'required',
            'employee_name' => 'required',
            'user_id' => 'required',
        ];


        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];

        $this->validate($request, $rules, $customMessages);  

        $data = User::findOrFail($request->userid);
        if($request->password != ''){
          $data->password = Hash::make($request->password);  
        }
        $data->name = $request->username;
        $data->username = $request->username;
        $res=$data->save();

        $data->assignRole('User');

        $edata = UserEmployees::findOrFail($request->employeeid);
        $edata->employee_code = $request->employee_code;
        $edata->employee_name = $request->employee_name;
        $edata->employee_user_id = $request->user_id;
        $eres=$edata->save();

        $permissions = User::find($request->userid)->getPermissionNames()->toArray();

        if(!empty($permissions)){
        	foreach ($permissions as $key => $value) {
	            $data->revokePermissionTo(''.$value.'');
	        }	
        }
        
        if($request['set_permission'] != ''){
	        foreach ($request->userpermission as $key => $value) {
	            $data->givePermissionTo(''.$value.'');
	        }
	    }

        //$userPermissions = User::find($request->userid)->getPermissionNames()->toArray();
        //echo "<pre>"; print_r($userPermissions); die;

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
    public function updateStatus($id)
    {
        $userStatus = User::select('status')->where('id',$id)->first();
        $udata = User::findOrFail($id);
        if($userStatus->status == 0){
            $udata->status = 1;
        }else{
            $udata->status = 0;
        }
        $udata=$udata->save();
        return back()->with('success', 'Update user status successfully!');
    }


    /**
     * Delete account head.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteUser($id)
    {
        $uEmployee = UserEmployees::where('user_id',$id)->delete();
        $user = User::where('id',$id)->delete();
        return back()->with('success', 'User deleted successfully!');
    }

}
