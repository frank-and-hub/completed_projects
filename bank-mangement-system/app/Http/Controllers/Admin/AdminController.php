<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use App\Models\Member;
use App\Models\SamraddhBank;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Userpermission;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function dashboard()
    {
        $data['title'] = 'Dashboard';

        $dashboardData = [];
        $memberData = [];
        $memberData['title'] = 'Members';
        $stateData = [];
        $tempData = [];
        $tempData['label'] = 'Active Members';
        $tempData['count'] = Member::where('status', 1)->count('id');
        $stateData[] = $tempData;
        $tempData['label'] = 'Blocked Members';
        $tempData['count'] = Member::where('status', 0)->count('id');
        $stateData[] = $tempData;
        $memberData['data'] = $stateData;
        $memberData['total'] = Member::count('id');
        $dashboardData[] = $memberData;
        $memberData['title'] = 'Branches';
        $stateData = [];
        $tempData['label'] = 'Active Branch';
        $tempData['count'] = Branch::where('status', 1)->count('id');
        $stateData[] = $tempData;
        $tempData['label'] = 'Blocked Branch';
        $tempData['count'] = Branch::where('status', 0)->count('id');
        $stateData[] = $tempData;
        $memberData['data'] = $stateData;
        $memberData['total'] = Branch::count('id');
        $dashboardData[] = $memberData;
        $data['status'] = $dashboardData;
        return view('templates.admin.dashboard.index', $data);
    }

    public function Profileupdate(Request $request)
    {
        $data = User::findOrFail($request->id);
        $data->username = $request->username;
        $data->name = $request->name;
        $data->phone = $request->mobile;
        $data->country = $request->country;
        $data->city = $request->city;
        $data->zip_code = $request->zip_code;
        $data->address = $request->address;
        $data->balance = $request->balance;
        if (empty($request->email_verify)) {
            $data->email_verify = 0;
        } else {
            $data->email_verify = $request->email_verify;
        }
        if (empty($request->phone_verify)) {
            $data->phone_verify = 0;
        } else {
            $data->phone_verify = $request->phone_verify;
        }
        if (empty($request->upgrade)) {
            $data->upgrade = 0;
        } else {
            $data->upgrade = $request->upgrade;
        }
        $res = $data->save();
        if ($res) {
            return back()->with('success', 'Update was Successful!');
        } else {
            return back()->with('alert', 'An error occured');
        }
    }
    public function logout()
    {
        Auth::guard()->logout();
        session()->flash('message', 'Just Logged Out!');
        return redirect('/admin');
    }
    public function member()
    {
        // dd(Member::first()->signature );
        Member::where('created_at', '<', Carbon::now()->subMonth())->whereIn('signature', [null, ''])->whereIn('photo', [null, ''])->update(['status' => 0]);
        //Member::where()->
    }
    /* admin list */
    public function usermanagementdetails()
    {
        if (check_my_permission(Auth::user()->id, "76") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = "Users List";
        $data1 = \App\Models\Admin::where('id', '!=', 0)->get();
        return view('templates.admin.user_management.usermanagement-listing', $data);
    }
    /* admin list ajax */
    public function usermanagementdetailsListing(Request $request)
    {
        $search = $_POST['search']['value'];
        $where = '(admin.created_at LIKE "%' . $search . '%" OR admin.username LIKE "%' . $search . '%" OR admin.employee_code LIKE "%' . $search . '%" OR admin.employee_name LIKE "%' . $search . '%" OR admin.mobile_number LIKE "%' . $search . '%" OR admin.user_id LIKE "%' . $search . '%")';
        if ($request->ajax()) {
            // $getBranchId=getUserBranchId(Auth::user()->id);
            // $branch_id=$getBranchId->id;
            $arrFormData['start_date'] = $request->start_date;
            $arrFormData['end_date'] = $request->end_date;
            $arrFormData['is_search'] = $request->is_search;
            $data = \App\Models\Admin::where('id', '!=', 0)->where('role_id', '!=', 1)->whereRaw($where);
            /******* fillter query start ****/
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['start_date'] != '') {
                    $startDate = $arrFormData['start_date'];
                    $endDate = $arrFormData['end_date'];
                    $startDate = date("Y-m-d", strtotime(convertDate($startDate)));
                    $endDate = date("Y-m-d", strtotime(convertDate($endDate)));
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
                /*  if($arrFormData['scheme_account_number'] !=''){
                      $sAccountNumber=$arrFormData['scheme_account_number'];
                      $data=$data->where('account_number','LIKE','%'.$sAccountNumber.'%');
                  }
                  */
            }
            /******* fillter query End ****/
            $count = $data->orderby('id', 'DESC')->count();
            $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $totalCount = \App\Models\Admin::where('id', '!=', 0)->where('role_id', '!=', 1)->whereRaw($where)->count();
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['created_at'] = date("d/m/Y", strtotime($row['created_at']));
                $val['username'] = $row['username'];
                $val['employee_code'] = $row['employee_code'];
                $val['employee_name'] = $row['employee_name'];
                $val['mobile_number'] = $row['mobile_number'];
                $val['user_id'] = $row['user_id'];
                /*$url = URL::to("admin/permission/".$row['id']."");*/
                $url = "admin/usermanagement-permission/" . base64_encode($row['id']);
                $urlEmployee = "admin/usermanagement-register/" . base64_encode($row['id']);
                $btn = '<a class="btn bg-dark legitRipple" href="' . $urlEmployee . '" title="Edit User"><i class="fa fa-edit"></i></a> <a class="btn bg-dark legitRipple" href="' . $url . '" title="Manage Permission"><i class="icon-lock"></i></a>';
                if ($row['status'] == "1") {
                    $btn .= ' <button class="btn bg-dark legitRipple activedeactiveUser" data-row-id="' . $row['id'] . '"  data-row-status="' . $row['status'] . '" title="Deactive User"><i class="fa fa-ban"></i></button>';
                } else {
                    $btn .= ' <button class="btn bg-dark legitRipple activedeactiveUser" style="color:red" data-row-id="' . $row['id'] . '" data-row-status="' . $row['status'] . '" title="Active User"><i class="fa fa-ban"></i></button>';
                }
                $val['action'] = $btn;
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
            return json_encode($output);
        }
    }
    public function active_deactive_admin_user()
    {
        $user_id = $_POST["user_id"];
        $status = $_POST["status"];
        if ($status == "1") {
            $admin = Admin::whereId($user_id)->update(array("status" => "0"));
            $output = array("status" => "1", "message" => "User deactivated successfully");
        } else {
            $admin = Admin::whereId($user_id)->update(array("status" => "1"));
            $output = array("status" => "1", "message" => "User activated successfully");
        }
        return json_encode($output);
    }
    public function register()
    {
        if (check_my_permission(Auth::user()->id, "75") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $admin = Auth::user();
        $data['title'] = 'User Management | Register';
        $data['banks'] = SamraddhBank::get();
        $data['branchs'] = Branch::get();
        $data['users'] = $admin;
        return view('templates.admin.user_management.add', $data);
    }
    public function edit_register($i)
    {
        $id = base64_decode($i);
        $admin = Admin::whereId($id)->first();
        $data['title'] = 'User Management | Edit User';
        $data['banks'] = SamraddhBank::get();
        $data['branchs'] = Branch::get();
        $data['users'] = $admin;
        return view('templates.admin.user_management.add', $data);
    }
    public function save(Request $request)
    {
        if ($request['id'] == "") {
            $rules = [
                'username' => 'required | unique:admin',
                'employee_code' => 'required | unique:admin',
                'employee_name' => 'required',
                'mobile_number' => 'required | unique:admin',
                'user_id' => 'required | unique:admin',
                'password' => 'required | confirmed',
                'password_confirmation' => 'required',
                'bank_id' => 'required',
            ];
            $customMessages = [
                'required' => 'Please enter :attribute.',
                'unique' => ' :Attribute already exists.'
            ];
            $customMessages['bank_id.required'] = 'Please select branch.';
            $this->validate($request, $rules, $customMessages);
        } else {
            $rules = [
                'username' => 'required | unique:admin,username,' . $request['id'],
                'employee_code' => 'required | unique:admin,employee_code,' . $request['id'],
                'employee_name' => 'required',
                'mobile_number' => 'required | unique:admin,mobile_number,' . $request['id'],
                'user_id' => 'required | unique:admin,user_id,' . $request['id']
            ];
            $customMessages = [
                'required' => 'Please enter :attribute.',
                'unique' => ' :Attribute already exists.'
            ];
            $this->validate($request, $rules, $customMessages);
        }
        $data['username'] = $request['username'];
        $data['role_id'] = 2;
        $data['employee_code'] = $request['employee_code'];
        $data['employee_name'] = $request['employee_name'];
        $data['mobile_number'] = $request['mobile_number'];
        $data['user_id'] = $request['user_id'];
        if (trim($request['password']) != "") {
            $data['password'] = Hash::make($request['password']);
        }
        $data['bank_id'] = $request['bank_id'];
        $data['branch_id'] = $request['bank_id'];
        $data['created_at'] = $request['created_at'];
        if ($request['bank_id'] == 'all') {
            $data['bank_id'] = 0;
            $data['branch_id'] = 0;
        }
        if ($request['id'] == "") {
            $admin = Admin::create($data);
            $adminId = $admin->id;
            return redirect()->route('admin.usermanagement.usermanagementdetails', ['id' => $adminId])->with('success', 'User created successfully!');
        } else {
            $admin = Admin::whereId($request['id'])->update($data);
            $adminId = $request['id'];
            return redirect()->route('admin.usermanagement.usermanagementdetails')->with('success', 'User updated successfully!');
        }
    }
    public function userPermission($i)
    {
        $id = base64_decode($i);
        $user_id = $id;
        $data['title'] = 'User Permission';
        $arr = Userpermission::select('id', 'name', 'parent_id')->get()->toArray();
        $userPermissionArr = array();
        $user_given_permission = DB::table('user_given_permission')->where("user_id", $user_id)->get();
        for ($q = 0; $q < count($user_given_permission); $q++) {
            array_push($userPermissionArr, $user_given_permission[$q]->permission_id);
        }
        $new = array();
        foreach ($arr as $a) {
            $new[$a['parent_id']][] = $a;
        }
        $data['permissions'] = $this->createTree($new, $new[0]);
        $data['userPermissions'] = $userPermissionArr;
        $data['user_id'] = $user_id;
        return view('templates.admin.user_management.permission', $data);
    }
    public function createTree(&$list, $parent)
    {
        $tree = array();
        foreach ($parent as $k => $l) {
            if (isset($list[$l['id']])) {
                $l['children'] = $this->createTree($list, $list[$l['id']]);
            }
            $tree[] = $l;
        }
        return $tree;
    }
    public function save_user_permission_data()
    {
        $user_id = $_POST["user_id"];
        if (isset($_POST["permissionName"])) {
            $permissionArr = $_POST["permissionName"];
        } else {
            $permissionArr = array();
        }
        DB::table('user_given_permission')->where('user_id', $user_id)->delete();
        if (!empty($permissionArr)) {
            foreach ($permissionArr as $key => $value) {
                $array = array("permission_id" => $key, "user_id" => $user_id);
                DB::table('user_given_permission')->insert($array);
            }
        }
    }
    public function get_employee_name_to_code()
    {
        $employee_code = $_POST["employee_code"];
        $check_employee_exist = DB::table('employees')->where("employee_code", $employee_code)->get();
        if (count($check_employee_exist) > 0) {
            $employee_name = $check_employee_exist[0]->employee_name;
            $arr = array("status" => "1", "employee_name" => $employee_name);
        } else {
            $employee_name = "";
            $arr = array("status" => "0", "employee_name" => $employee_name);
        }
        echo json_encode($arr);
    }
}