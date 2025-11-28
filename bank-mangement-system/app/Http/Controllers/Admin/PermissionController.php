<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Auth;
use App\Models\RoleHasPermission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Branch;
use App\Models\User;
use Session;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
	    if(check_my_permission( Auth::user()->id,"92") != "1"){
		  return redirect()->route('admin.dashboard');
		}
	   
	    $data['title']='Branch Permission';
	    //$data['roles'] = Role::whereNotIn('id', [1,2,4,5])->get();
		
		   if(Auth::user()->branch_id>0){
			 $id=Auth::user()->branch_id;
			 $data['branches'] = Branch::where('id','=',$id)->pluck('name','id');
		    }
			else{
			  $data['branches'] = Branch::pluck('name','id');
			}
		
		
	    //$data['permissions'] = Permission::get();
	    $arr = Permission::select('id','name','parent_id')->get()->toArray();

	    $new = array();
	    foreach ($arr as $a){
		    $new[$a['parent_id']][] = $a;
	    }

	    $data['permissions'] = $this->createTree($new, $new[0]);

	   // dd( "TR", auth()->user(), User::find(75) );
	    return view('templates.admin.permission.index', $data);
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create( Request $request )
    {
	    $this->validate(request(), ['name' => 'required | unique:permissions,name']);
	    Permission::create(['name' => request('name'), 'guard_name' => 'web', 'status'=>1]);
	    $role = Role::where('name', 'Super Admin')->first();
	    $role->givePermissionTo(request('name'));
	    if ($role) {
		    return back()->with('success', 'Permission created Successfully!');
	    } else {
		    return back()->with('alert', 'Problem With Creating Permission');
	    }
    	/*$permission = new Permission();
    	dd(":DD", $permission);
	    $permission->name = $request->name;
	    $permission->guard_name = 'web';
	    $permission->save();

	    $assignPermission = RoleHasPermission::insert(['role_id' => 1, 'permission_id' => $permission->id ]);

	    if ( $assignPermission ) {
		    return redirect()->route('admin.permission')->with('success', 'Permission create Successfully!');
	    } else {
		    return back()->with('alert', 'Problem With Creating Permission');
	    }*/
    }

    public function createRole( Request $request )
    {
	    $role = Role::create(['name' => $request->name, 'guard_name'=>'web']);
	    if ($role) {
		    return back()->with('success', 'Role Created Successfully');
	    } else {
		    return back()->with('alert', 'Problem With Creating Role');
	    }
	    /*$permission = Permission::create(['name' => 'edit articles', 'guard_name'=>'web']);
	    $role->givePermissionTo($permission);
	    $permission->assignRole($role);
	   // $role->syncPermissions($permissions);
	   // $permission->syncRoles($roles);*/

    }

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function saveUserAccess()
	{
		$roles = Role::where('name', '!=', 'Super Admin')->get();

		$permissions = Permission::get();
		$branches = Branch::whereIn('branch.id', request('branch') )->pluck('manager_id');

		$users = User::whereIn('id', $branches )->get();
		//dd( "DD", $users);
		/*dd(request('branch'),  $branches);*/
		foreach ( $users as $user ) {
			//dd("TTT", $user);
			foreach ($permissions as $permission) {
				$user->revokePermissionTo($permission);
				if ( request( str_replace(' ', '_',$permission->name ) ) ) {
					$user->givePermissionTo($permission);
				}

				/*if(request(str_replace(' ', '_', $role->name).'-'.str_replace(' ', '_',$permission->name))) {
					$role->givePermissionTo($permission);
				}*/
			}
		}
		/*foreach ($roles as $role) {
			foreach ($permissions as $permission) {
				$role->revokePermissionTo($permission);
				if(request(str_replace(' ', '_', $role->name).'-'.str_replace(' ', '_',$permission->name))) {
					$role->givePermissionTo($permission);
				}
			}
		}*/
		return response()->json(['success'=>'Permission Updated Successfully']);
	}

	public function getPermission( Request $request )
	{
		$managerId = Branch::find($request->branchId)->manager_id;
		return User::find($managerId)->getPermissionNames()->toArray();

	}
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function show(Permission $permission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function edit(Permission $permission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permission $permission)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        //
    }
}
