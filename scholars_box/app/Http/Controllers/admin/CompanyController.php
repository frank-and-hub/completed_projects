<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Models\CompanyDetails;
use DB;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->menu = '6'; 
    }
    public function index(){
        $path = user_permission($this->menu,'view');
        if(!$path){
            $data['company'] = User::whereRoleId('3')->get();
            return view('admin.company.index',$data);
        }else{
            return redirect()->route($path);
        }
    }
    public function create(){
        $path = user_permission($this->menu,'add');
        if(!$path){
            $data['company'] = null;
            $data['title'] = 'create';
            return view('admin.company.edit',$data);
        }else{
            return redirect()->route($path);
        }
    }
    public function store(Request $request) {
        $path = user_permission($this->menu,'edit');
        if(!$path){
            $validator = Validator::make($request->all(), [
                'company_name' => 'required|string|max:255',
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
                    'name' => $request['company_name'],
                    'email' => $request['email'],
                    'company_name' => $request['company_name'],
                    'social_id' => $request['password'],
                    'password' => $request['password'], // Fixed password hashing
                    'role_id' => '3',
                    // 'avatar' => $imagePath,
                    'phone_number'=>$request['phone_number'],
                ]);
        
            } catch (\Exception $e) {
                return redirect()->back()->with(['error',$e->getMessage()]); // Fixed variable name
            }
            return redirect()->route('company.index')->with('success','new company created successfully !');
        }else{
            return redirect()->route($path);
        }
    }
    public function edit(Request $request,$id){
        $path = user_permission($this->menu,'edit');
        if(!$path){
            $data['company'] = User::whereRoleId('3')->whereId($id)->first();
            $data['title'] = 'edit';
            return view('admin.company.edit',$data);
        }else{
            return redirect()->route($path);
        }
    }
    public function show(Request $request,$id){
        $path = user_permission($this->menu,'view');
        if(!$path){
            $data['company'] = User::whereRoleId('3')->whereId($id)->first();
            $data['title'] = 'show';
            return view('admin.company.edit',$data);
        }else{
            return redirect()->route($path);
        }
    }
    public function update(Request $request){
        $path = user_permission($this->menu,'edit');
        if(!$path){
            $validator = Validator::make($request->all(), [
                'company_name' => 'required|string|max:255',
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
                    'name' => $request['company_name'],
                    'email' => $request['email'],
                    'company_name' => $request['company_name'],
                    'password' => $request['password'], // Fixed password hashing
                    'role_id' => '3',
                     'social_id' => $request['password'],
                    'phone_number'=>$request['phone_number'],
                ];
                $user = User::find((int)$request->id)->update($update);
            } catch (\Exception $e) {
                dd($e->getMessage());
                return redirect()->back()->with(['error',$e->getMessage()]); // Fixed variable name
            }
            return redirect()->route('company.index')->with('success','company details Updated successfully !');
        }else{
            return redirect()->route($path);
        }
    }
    public function delete($id){
        $path = user_permission($this->menu,'delete');
        if(!$path){
            User::whereRoleId('3')->whereId($id)->delete();
            return redirect()->route('company.index')->with('success','company deleted successfully !');
        }else{
            return redirect()->route($path);
        }
    }
    public function permission($id){
        $path = user_permission($this->menu,'edit');
        if(!$path){
            $user = User::whereRoleId('3')->whereId($id)->first();
            if($user){
                $data['id'] = $id;
                $data['p'] = Permission::whereUserId($id)->get()->toArray();
                $data['menu'] = DB::table('menues')->whereNotIn('id',['5','6'])->get();
                return view('admin.company.permission',$data);
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
                $user = User::whereRoleId('3')->whereId($request->user_id)->first();
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
                    return redirect()->route('company.permission',$request->user_id);
                }
            }catch (\Exception $e) {
                return redirect()->route('company.permission',$request->user_id)->with(['error',$e->getMessage()]); // Fixed variable name
            }
            return redirect()->route('company.permission',$request->user_id)->with('success','Company permissions updated successfully !');
        }else{
            return redirect()->route($path);
        }
    }
    
     public function detailStore(Request $request)
    {
        // $request->validate([
            
            
        //     'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        //     'about_company' => 'required|string',
        //     'page_content' => 'required|string',
        // ]);

        $companyDetail = new CompanyDetails;
        $companyDetail->company_name = $request->id;
        
        $companyDetail->about_company = $request->about_company;
        $companyDetail->desc = $request->page_content;
        $companyDetail->about_image_title1 = $request->about_image_title1;
        $companyDetail->about_image_title2 = $request->about_image_title2;
        $companyDetail->about_title = $request->about_title;
        $companyDetail->about_listing1 = $request->about_listing1;
        $companyDetail->about_listing2 = $request->about_listing2;
        $companyDetail->circle_listing1 = $request->circle_listing1;
        $companyDetail->circle_listing2 = $request->circle_listing2;
        $companyDetail->samall_title = $request->samall_title;
        $companyDetail->main_title = $request->main_title;
       

       if ($request->hasFile('logo')) {
    $logoImage = $request->file('logo');
    $logoImageName = time() . '.' . $logoImage->getClientOriginalExtension();
    $logoImage->move(public_path('uploads/company_logos'), $logoImageName);
    $companyDetail->logo = 'uploads/company_logos/' . $logoImageName;
}

if ($request->hasFile('banner')) {
    $bannerImage = $request->file('banner');
    $bannerImageName = time() . '.' . $bannerImage->getClientOriginalExtension();
    $bannerImage->move(public_path('uploads/company_logos'), $bannerImageName);
    $companyDetail->banner = 'uploads/company_logos/' . $bannerImageName;
}

if ($request->hasFile('about_image1')) {
    $aboutImage1 = $request->file('about_image1');
    $aboutImage1Name = time() . '.' . $aboutImage1->getClientOriginalExtension();
    $aboutImage1->move(public_path('uploads/company_logos'), $aboutImage1Name);
    $companyDetail->about_image1 = 'uploads/company_logos/' . $aboutImage1Name;
}

if ($request->hasFile('about_image2')) {
    $aboutImage2 = $request->file('about_image2');
    $aboutImage2Name = time() . '.' . $aboutImage2->getClientOriginalExtension();
    $aboutImage2->move(public_path('uploads/company_logos'), $aboutImage2Name);
    $companyDetail->about_image2 = 'uploads/company_logos/' . $aboutImage2Name;
}
      

        $companyDetail->save();

        return redirect()->route('company.index')->with('success', 'Company details saved successfully.');
    }

 public function detailUpdate(Request $request, $id)
{ 
    // $request->validate([
    //     'email' => 'required|email',
    //     'about_company' => 'required|string',
    //     'page_content' => 'required|string',
    // ]);
    
       

    $companyDetail = CompanyDetails::findOrFail($request->companyDetailTableid);

    $companyDetail->company_name = $request->id;

    $companyDetail->about_company = $request->about_company;
    $companyDetail->desc = $request->page_content;
    $companyDetail->about_image_title1 = $request->about_image_title1;
    $companyDetail->about_image_title2 = $request->about_image_title2; // Corrected this line
    $companyDetail->about_title = $request->about_title;
    $companyDetail->about_listing1 = $request->about_listing1;
    $companyDetail->about_listing2 = $request->about_listing2;
    $companyDetail->circle_listing1 = $request->circle_listing1;
    $companyDetail->circle_listing2 = $request->circle_listing2;
    $companyDetail->samall_title = $request->samall_title;
    $companyDetail->main_title = $request->main_title;

    

if ($request->hasFile('logo')) {
    $request->validate([
        'logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    $logoImage = $request->file('logo');
    $logoImageName = time() . '_' . $logoImage->getClientOriginalName(); // Use original name
    $logoImage->move(public_path('uploads/company_logos'), $logoImageName);
    $companyDetail->logo = 'uploads/company_logos/' . $logoImageName;
}

if ($request->hasFile('banner')) {
    $bannerImage = $request->file('banner');
    $bannerImageName = time() . '_' . $bannerImage->getClientOriginalName(); // Use original name
    $bannerImage->move(public_path('uploads/company_logos'), $bannerImageName);
    $companyDetail->banner = 'uploads/company_logos/' . $bannerImageName;
}

if ($request->hasFile('about_image1')) {
    $aboutImage1 = $request->file('about_image1');
    $aboutImage1Name = time() . '_' . $aboutImage1->getClientOriginalName(); // Use original name
    $aboutImage1->move(public_path('uploads/company_logos'), $aboutImage1Name);
    $companyDetail->about_image1 = 'uploads/company_logos/' . $aboutImage1Name;
}

if ($request->hasFile('about_image2')) {
    $aboutImage2 = $request->file('about_image2');
    $aboutImage2Name = time() . '_' . $aboutImage2->getClientOriginalName(); // Use original name
    $aboutImage2->move(public_path('uploads/company_logos'), $aboutImage2Name);
    $companyDetail->about_image2 = 'uploads/company_logos/' . $aboutImage2Name;
}

    $companyDetail->save();

    return redirect()->route('company.index')->with('success', 'Company details updated successfully.');
}



    public function detailIndex($id){
        $path = user_permission($this->menu,'view');
       
           $companyDetail = User::find($id);
           $companyDetailTable = CompanyDetails::where('company_name',$id)->first();
          
            return view('admin.companyDetails.index',compact('companyDetail','companyDetailTable'));
        
    }
}
