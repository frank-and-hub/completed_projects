<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AboutUs;

class AboutusController extends Controller
{
    public function __construct()
    {
        $this->menu = '4';
    }
    public function index(){
        $path = user_permission($this->menu,'view');
        if(!$path){
            $data['about'] = AboutUs::first();
            return view('admin.page.about-us',$data);
        }else{
            return redirect()->route($path);
        }
    }
    public function store(Request $request){
        $path = user_permission($this->menu,'edit');
        if(!$path){
            $data = [
                'session_title'=>$request->session_title,    
                'session_title_second'=>$request->session_title_second,    
                'session_description'=>$request->session_description,    
                'session_description_second'=>$request->session_description_second,    
                'title_1'=>$request->title_1,    
                'title_2'=>$request->title_2,    
                'title_3'=>$request->title_3,    
                'description_1'=>$request->description_1,    
                'description_2'=>$request->description_2,    
                'description_3'=>$request->description_3,    
                'status'=>'1',
            ];
            if (!empty($_FILES)) {
                if ($_FILES['session_image']['name'] != "") {
                    $imageName = date('Y_m_d_H_i_s_a') . '_' . time() . '.' . request()->session_image->getClientOriginalExtension();
                    request()->session_image->move(public_path('img/'), $imageName);
                    $imagePath = 'img/' . $imageName;
                    $data['session_image'] = $imagePath;
                } else {
                    $data['session_image'] = $request->hidden_session_image;
                }
            } else {
                $data['session_image'] = $request->hidden_session_image;
            }
            if (!empty($_FILES)) {
                if ($_FILES['session_image_second']['name'] != "") {
                    $imageName = date('Y_m_d_H_i_s_a') . '_2_' . time() . '.' . request()->session_image_second->getClientOriginalExtension();
                    request()->session_image_second->move(public_path('img/'), $imageName);
                    $imagePath = 'img/' . $imageName;
                    $data['session_image_second'] = $imagePath;
                } else {
                    $data['session_image_second'] = $request->hidden_session_image_second;
                }
            } else {
                $data['session_image_second'] = $request->hidden_session_image_second;
            }
            AboutUs::whereId('1')->update($data);
            return redirect()->route('admin.about.us');
        }else{
            return redirect()->route($path);
        }
    }
  

}
