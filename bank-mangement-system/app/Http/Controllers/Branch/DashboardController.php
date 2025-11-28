<?php

namespace App\Http\Controllers\Branch;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use App\Models\Branch;

use Carbon\Carbon;
use Session;
use Image;
use Redirect;




class DashboardController extends Controller
{

	protected $user;
        
    public function __construct(  )
    {
        $this->middleware('auth');

    }

        
    public function index()
    {

        $data['title']='Dashboard';
        return view('templates.branch.dashboard.index', $data);
    }
    public function logout()
    {
        Auth::guard()->logout();
        session()->forget('fakey');
        $id = Session::get('uId');
        User::where('id', $id)->update(['branch_token' => null]);
        session()->flash('message', 'Just Logged Out!');
        return redirect('/login');
    }
  
}
