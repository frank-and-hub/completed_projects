<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use DB;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->menu = '5'; 
    }
    public function index(){
        $path = user_permission($this->menu,'view');
        if(!$path){
            $data['user'] = User::whereRoleId('4')->get();
            return view('admin.user.index',$data);
        }else{
            return redirect()->route($path);
        }
    }
    public function create(){
        $path = user_permission($this->menu,'add');
        if(!$path){
            $data['user'] = null;
            $data['title'] = 'create';
            return view('admin.user.edit',$data);
        }else{
            return redirect()->route($path);
        }
    }
    public function store(Request $request) {
        $path = user_permission($this->menu,'edit');
        if(!$path){
            $validator = Validator::make($request->all(), [
                'last_name' => 'required|string|max:255',
                'first_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
                // 'avatar' => 'required',
                'phone_number' => 'required|min:10|max:12',
            ]);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        
            try {
                // if (!empty($_FILES)) {
                //     if ($_FILES['avatar']['name'] != "") {
                //         $imageName = date('Y_m_d_H_i_s_a') . '_' . time() . '.' . request()->avatar->getClientOriginalExtension();
                //         request()->avatar->move(public_path('img/profile/'), $imageName);
                //         $imagePath = 'img/profile/' . $imageName;
                //     } else {
                //         $imagePath = $request->avatar_hidden;
                //     }
                // } else {
                //     $imagePath = $request->avatar_hidden;
                // }
                $user = User::create([
                    'first_name' => $request['first_name'],
                    'last_name' => $request['last_name'],
                    'email' => $request['email'],
                    'password' => Hash::make($request['password']), // Fixed password hashing
                    'role_id' => '4',
                    // 'avatar' => $imagePath,
                    'phone_number'=>$request['phone_number'],
                ]);
                
            } catch (\Exception $e) {
                return redirect()->back()->with(['error',$e->getMessage()]); // Fixed variable name
            }
            return redirect()->route('user.index')->with('success','new user created sucessfully !');
        }else{
            return redirect()->route($path);
        }
    }
    public function edit(Request $request,$id){
        $path = user_permission($this->menu,'edit');
        if(!$path){
            $data['user'] = User::whereRoleId('4')->whereId($id)->first();
            $data['title'] = 'edit';
            return view('admin.user.edit',$data);
        }else{
            return redirect()->route($path);
        }
    }
    public function show(Request $request,$id){
        $path = user_permission($this->menu,'view');
        if(!$path){
            $data['user'] = User::whereRoleId('4')->whereId($id)->first();
            $data['title'] = 'show';
            return view('admin.user.edit',$data);
        }else{
            return redirect()->route($path);
        }
    }
    public function update(Request $request){
        $path = user_permission($this->menu,'edit');
        if(!$path){
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email',
                'password' => 'required|string|min:6|confirmed',
                // 'avatar' => 'required',
                'phone_number' => 'required|min:10|max:12',
            ]);
        
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            try {
                // if (!empty($_FILES)) {
                //     if ($_FILES['avatar']['name'] != "") {
                //         $imageName = date('Y_m_d_H_i_s_a') . '_' . time() . '.' . request()->avatar->getClientOriginalExtension();
                //         request()->avatar->move(public_path('img/profile/'), $imageName);
                //         $imagePath = 'img/profile/' . $imageName;
                //     } else {
                //         $imagePath = $request->avatar_hidden;
                //     }
                // } else {
                //     $imagePath = $request->avatar_hidden;
                // }
                $update = [
                    'first_name' => $request['first_name'],
                    'last_name' => $request['last_name'],
                    'email' => $request['email'],
                    'password' => Hash::make($request['password']), // Fixed password hashing
                    'role_id' => '4',
                    // 'avatar' => $imagePath,
                    'phone_number'=>$request['phone_number'],
                ];
                $user = User::find((int)$request->id)->update($update);
            } catch (\Exception $e) {
                dd($e->getMessage());
                return redirect()->back()->with(['error',$e->getMessage()]); // Fixed variable name
            }
            return redirect()->route('user.index')->with('success','user details updated sucessfully !');
        }else{
            return redirect()->route($path);
        }
    }
    public function delete($id){
        $path = user_permission($this->menu,'delete');
        if(!$path){
            User::whereRoleId('4')->whereId($id)->delete();
            return redirect()->route('user.index')->with('success','user deleted sucessfully !');
        }else{
            return redirect()->route($path);
        }
    }
    public function permission($id){
        $path = user_permission($this->menu,'edit');
        if(!$path){
            $user = User::whereRoleId('4')->whereId($id)->first();
            if($user){
                $data['id'] = $id;
                $data['p'] = Permission::whereUserId($id)->get()->toArray();
                $data['menu'] = DB::table('menues')->get();
                return view('admin.user.permission',$data);
            }else{
                return redirect()->back();
            }
        }else{
            return redirect()->route($path);
        }
    }
    public function storePermission(Request $request){
        $path = user_permission($this->menu,'edit');
        if(!$path){
            $insert = [];
            try{
                $user = User::whereRoleId('4')->whereId($request->user_id)->first();
                if($user){
                    Permission::whereUserId($user->id)->delete();
                    $menu = DB::table('menus');
                    foreach($request['m'] as $k => $v){
                        
                        $insert[$k]['view'] = $request['v'][$k] ?? '0';
                        $insert[$k]['add'] = $request['a'][$k] ?? '0';
                        $insert[$k]['edit'] = $request['e'][$k] ?? '0';
                        $insert[$k]['delete'] = $request['d'][$k] ?? '0';
                        $insert[$k]['menu_id'] = $v;
                        $insert[$k]['user_id'] = $request['user_id'];
                        $insert[$k]['status'] = '1';
                    }
                    $created = Permission::insert($insert);
                }else{
                    return redirect()->route('user.permission',$request->user_id);
                }
            }catch (\Exception $e) {
                return redirect()->route('user.permission',$request->user_id)->with(['error',$e->getMessage()]); // Fixed variable name
            }
            return redirect()->route('user.permission',$request->user_id)->with('success','user permission created sucessfully !');
        }else{
            return redirect()->route($path);
        }
    }
}