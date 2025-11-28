<?php
namespace App\Http\Controllers\SuperAdmin;
use Illuminate\Http\Request;
use Auth;
use App\Models\Settings;
use App\Http\Controllers\Controller;

class AdminLoginController extends Controller
{


	public function __construct(){
		$Gset = Settings::first();
		$this->sitename = $Gset->site_name;
	}


	public function index()
	{
		if(Auth::guard('superAdmin')->check()){
			return redirect()->route('Admin.dashboard');
		}
		$data['title'] = "Super Admin";
		return view('templates.super_admin.index', $data);
	}

	public function authenticate(Request $request){
		if (Auth::guard('superAdmin')->attempt([
			'username' => $request->username,
			'password' => $request->password,
		])) {
			return redirect()->route('Admin.dashboard');
		}else{
			return back()->with('alert', 'Oops! You have entered invalid credentials');
		}
	}
}
