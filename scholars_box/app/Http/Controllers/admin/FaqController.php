<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;

class FaqController extends Controller
{
    public function __construct()
    {
        $this->menu = '7';
    }
    public function index(){
        $path = user_permission($this->menu,'view');
        if(!$path){
            $faqs = Faq::get();
            return view('admin.faq.list',compact('faqs'));
        }else{
            return redirect()->route($path);
        }
    }

    public function add(){
        $path = user_permission($this->menu,'add');
        if(!$path){
            return view('admin.faq.add');
        }else{
            return redirect()->route($path);
        }
    }

    public function store(Request $request){
        $path = user_permission($this->menu,'add');
        if(!$path){
            $request->validate([
                'faq_title' => 'required|string|max:255',
                'faq_content' => 'required|string',
            ]);
    
            // Create a new FAQ record
            $faq = new Faq();
            $faq->title = $request->input('faq_title');
            $faq->description = $request->input('faq_content');
            $faq->status = 1;
    
            // Save the FAQ record to the database
            $faq->save();
    
            return redirect()->route('admin.faq.list')->with('success', 'FAQ entry saved successfully');
        }else{
            return redirect()->route($path);
        }
        
    }

    public function edit($id){
        $path = user_permission($this->menu,'edit');
        if(!$path){
            $faq = Faq::find($id);
            return view('admin.faq.edit',compact('faq'));
        }else{
            return redirect()->route($path);
        }

    }

    public function update(Request $request, $id)
    {
        $path = user_permission($this->menu,'edit');
        if(!$path){
            $faq = Faq::find($id);
            // Validate the form data
            $request->validate([
                'faq_title' => 'required|string|max:255',
                'faq_content' => 'required|string',
            ]);
        
            // Update the FAQ with the new data
            $faq->title = $request->input('faq_title');
            $faq->description = $request->input('faq_content');
            $faq->status = 1;;
        
            // Save the updated FAQ
            $faq->save();
        
            return redirect()->route('admin.faq.list')->with('success', 'FAQ updated successfully');
        }else{
            return redirect()->route($path);
        }

        
    }

    public function delete($id){
        $path = user_permission($this->menu,'delete');
        if(!$path){
            $faq = Faq::whereId($id)->delete();
            return redirect()->route('admin.faq.list')->with('success', 'FAQ Deleted successfully');
        }else{
            return redirect()->route($path);
        }
        
    }

    public function view($id){
        $path = user_permission($this->menu,'view');
        if(!$path){
            $faq = Faq::find($id);
            return view('admin.faq.view',compact('faq'));
        }else{
            return redirect()->route($path);
        }
        

        
    }

}
