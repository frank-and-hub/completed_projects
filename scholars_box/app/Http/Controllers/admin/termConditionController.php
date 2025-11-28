<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\termcondition;
use App\Models\User;
use Auth;
use Illuminate\Support\Str;


class termConditionController extends Controller
{
    public function __construct()
    {
        $this->menu = '3';
    }
    public function index(){
        $path = user_permission($this->menu,'view');
        if(!$path){
            $term = termcondition::with('user')->get();
      
         
            return view('admin.termCondition.list',compact('term'));
        }else{
            return redirect()->route($path);
        }
    }

    public function add(){
        $path = user_permission($this->menu,'add');
        if(!$path){
            $microsite = User::where('company_name','!=',null)->get();
            return view('admin.termCondition.add',compact('microsite'));
        }else{
            return redirect()->route($path);
        }
    }
    public function store(Request $request)
    { 
        $path = user_permission($this->menu,'add');
        if(!$path){

       
           

            $title = $request->input('scholarship_name');
            
        
        
            $blog = new termcondition;
            $blog->ani1 = $title;
            
            $blog->desc = $request->input('desc');
          
        
            $blog->save();
        
        return redirect()->route('admin.term.list')->with('success', 'Term entry saved successfully');
        }else{
            return redirect()->route($path);
        }

    }


    // public function view($id){
    //     $path = user_permission($this->menu,'view');
    //     if(!$path){
    //         $blog = Blog::find($id);
    //         return view('admin.blog.view',compact('blog'));
    //     }else{
    //         return redirect()->route($path);
    //     }

    // }

    public function edit($id){
        $path = user_permission($this->menu,'edit');
        if(!$path){
            $ids = $id;
            $blog = termcondition::find($id);
            $microsite = User::where('company_name','!=',null)->get(); 

            return view('admin.termCondition.edit',compact('blog','ids','microsite'));
        }else{
            return redirect()->route($path);
        }
    }

    public function delete($id){
        $path = user_permission($this->menu,'delete');
        if(!$path){
            $blog = termcondition::whereId($id)->delete();
            return redirect()->route('admin.term.list')->with('success', 'T & C Deleted successfully');
        }else{
            return redirect()->route($path);
        }

    }

    public function update(Request $request)
    {
        
        $path = user_permission($this->menu,'edit');
        if(!$path){
      
        
            $blog = termcondition::find($request->id);
        
            if (!$blog) {
                return redirect()->back()->with('error', 'Blog entry not found');
            }
        
            // Update the blog entry
            $blog->ani1 = $request->scholarship_name ?? 'MicroSite T&C';
            
            $blog->desc = $request->input('desc');

            $blog->save();
        
            return redirect()->route('admin.term.list')->with('success', ' T & C updated successfully');
        }else{
            return redirect()->route($path);
        }
    }
}
