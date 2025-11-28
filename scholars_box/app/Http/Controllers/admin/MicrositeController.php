<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Microsite;
use App\Models\User;

class MicrositeController extends Controller
{
    public function index(){
        $microsites = Microsite::with('company')->get();
        return view('admin.microsites.index',compact('microsites'));
    }

    public function create(){
        $companies =  User::whereRoleId('3')->get();
            
        return view('admin.microsites.add',compact('companies'));
    }

    public function store(Request $request){
  
        $validatedData = $request->validate([
            'logo' => 'required|image',
            'banner' => 'required|image',
            'banner_title' => 'required|string',
            'banner_link' => 'required|url',
            'about_title' => 'required|string',
            'video' => 'required|mimetypes:video/mp4,video/mpeg,video/quicktime',
            'about_description' => 'required|string',
            'detail_description' => 'required|string',
            'compmny' => 'required'
        ]);

        $comapnyName = User::select('id','company_name')->where('id',$validatedData['compmny'])->first();
        // Process and save the form data
        $microsite = new Microsite();
        $microsite->banner_titile = $validatedData['banner_title'];
        $microsite->banner_link = $validatedData['banner_link'];
        $microsite->about_title = $validatedData['about_title'];
        $microsite->about_description = $validatedData['about_description'];
        $microsite->detail_description = $validatedData['detail_description'];
        $microsite->company_id = $validatedData['compmny'];
        $microsite->company_name = $comapnyName->company_name ?? '';

        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $logo = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads'), $logo); // Save the image to the 'uploads' directory
        }

        if ($request->hasFile('banner')) {
            $image = $request->file('banner');
            $bannerImage = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads'), $bannerImage); // Save the image to the 'uploads' directory
        }

        if ($request->hasFile('video')) {
            $image = $request->file('video');
            $video = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads'), $video); // Save the image to the 'uploads' directory
        }


        $microsite->video = $video;
        $microsite->banner = $bannerImage;
        $microsite->logo = $logo;

        // Assign file paths to the microsite object
     


        // Save the microsite data to the database
        $microsite->save();

        // Optionally, you can redirect the user after successful form submission
        return redirect()->back()->with('success', 'Microsite created successfully!');
    }

    public function view($id){
        $microsite = Microsite::find($id);
        
        return view('admin.microsites.view',compact('microsite'));

    }

    public function edit($id){
        $companies =  User::whereRoleId('3')->get();
$ids = $id;
        $microsite = Microsite::find($id);
        return view('admin.microsites.edit',compact('microsite','companies','ids'));

    }




    public function updateasd(Request $request, $id)
    {
        
       
        $microsite = Microsite::find($id);
       
        
           
        
       
        
            // Update the blog entry
            $microsite->banner_titile = $request->banner_title;
        $microsite->banner_link = $request->banner_link;
        $microsite->about_title = $request->about_title;
        $microsite->about_description = $request->about_description;
        $microsite->detail_description = $request->detail_description;
        $microsite->company_id = $request->compmny;
            
            if ($request->hasFile('banner')) {
                $image = $request->file('banner');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads'), $imageName);
                $microsite->banner = $imageName;
            }
            if ($request->hasFile('logo')) {
                $image = $request->file('logo');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads'), $imageName);
                $microsite->logo = $imageName;
            }
            if ($request->hasFile('video')) {
                $image = $request->file('video');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads'), $imageName);
                $microsite->video = $imageName;
            }
            

            $microsite->save();
        
            return redirect()->route('admin.microsite.index')->with('success', 'Blog entry updated successfully');
      
    }



    

    public function delete($id){
        $deleteMicro = Microsite::find($id);
        $deleteMicro->delete();
        return redirect()->back()->with('success', 'Microsite deleted successfully!');


    }
}
