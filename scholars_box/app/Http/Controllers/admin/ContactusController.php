<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Contact;
use DB;

class ContactusController extends Controller
{
    public function __construct()
    {
        $this->menu = '4';
    }
    public function index(){
        $path = user_permission($this->menu,'view');
        if(!$path){
            $data['contact'] = Contact::whereId('1')->first();
            return view('admin.page.contact-us',$data);
        }else{
            return redirect()->route($path);
        }
    }
    public function login(){
        $path = user_permission($this->menu,'view');
        if(!$path){
            $data['log'] = DB::table('loginpage')->whereId('1')->first();
            return view('admin.page.login',$data);
        }else{
            return redirect()->route($path);
        }
    }
    
    public function store(Request $request){
        $path = user_permission($this->menu,'edit');
        if(!$path){
            $data = [
                'description'=>$request->description,    
                'email'=>$request->email,    
                'number'=>$request->number,    
                'long_description'=>$request->long_description,    
                'address'=>$request->address,    
                'map'=>$request->map,    
                'title'=>$request->title,    
            ];
            Contact::whereId('1')->update($data);
            return redirect()->route('admin.contact.us');
        }else{
            return redirect()->route($path);
        }
    }
    public function loginStore(Request $request){
        $path = user_permission($this->menu,'edit');
        if(!$path){
            $data = [
                'description'=>$request->description,    
                'title'=>$request->title,    
                'url'=>$request->url??null,   
                'created_by'=>auth()->user()->first_name,    
                'created_at'=>date('Y-m-d H:i:s'), 
            ];
            DB::table('loginpage')->whereId('1')->update($data);
            return redirect()->route('admin.login.page')->with('success','Log in page details updated successfully');
        }else{
            return redirect()->route($path);
        }
    }

}
