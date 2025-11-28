<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Banner;
use App\Models\Partner;
use App\Models\Study;
use App\Models\User;
use App\Models\JobRequest;
use App\Models\JobPosition;





use Illuminate\Support\Facades\Storage;


class HomeController extends Controller
{
    // public function __construct()
    // {
    //     $this->menu = '4';
    // }
    public function bannerList(){
       
      
            $banners = Banner::all();
            return view('admin.page.home.banners',compact('banners'));
       
    }

    public function bannerAdd(){
       
           
            return view('admin.page.home.addBanner');
       
           
      
    }
    
    public function bannerStore(Request $request){

     
        $imageName = null; // Initialize the variable with a default value
        
            if ($request->hasFile('banner_image')) {
                $image = $request->file('banner_image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads'), $imageName); // Save the image to the 'uploads' directory
            }
        
            $bannerHome = new Banner;
            $bannerHome->title = $request->banner_title;
            $bannerHome->image = $imageName;
            
            
        
            $bannerHome->save();
            return redirect()->route('admin.home.banner.list')->with('success', 'Banner saved successfully');
    }

    
    public function bannerEdit($id){
     
            $baneer = Banner::find($id);
            return view('admin.page.home.edit',compact('baneer'));
       

    }

    
    public function bannerUpdate(Request $request, $id)
    {
    
            $request->validate([
                'banner_title' => 'required|string|max:255',
                'banner_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Use "sometimes" to allow optional image updates
                
            ]);
        
            $banner = Banner::find($id);
        
            if (!$banner) {
                return redirect()->back()->with('error', 'banner entry not found');
            }
        
            // Update the blog entry
            $banner->title = $request->banner_title;
            
            if ($request->hasFile('banner_image')) {
                $image = $request->file('banner_image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads'), $imageName);
                $banner->image = $imageName;
            }
            
          
            $banner->update();
        
            return redirect()->route('admin.home.banner.list')->with('success', 'Banner entry updated successfully');
       
    }

    
    public function bannerView($id){
      
            $banner = Banner::find($id);
            return view('admin.page.home.view',compact('banner'));
       
    }

    public function bannerDelete($id){
      
        $banner = Banner::find($id)->delete();
        if($banner == 1){
            
            return redirect()->route('admin.home.banner.list')->with('success', 'Banner Deleted successfully');
        }else{
            return redirect()->route('admin.home.banner.list')->with('error', 'something went wrong !');
        }

   
}


// partner


public function partnerList(){
       
      
    $partners = Partner::all();
    return view('admin.page.home.partners',compact('partners'));

}

public function partnerAdd(){

   
    return view('admin.page.home.addpartners');

   

}

public function partnerStore(Request $request){


$imageName = null; // Initialize the variable with a default value

    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('uploads'), $imageName); // Save the image to the 'uploads' directory
    }

    $partnerHome = new Partner;
    $partnerHome->title = $request->title;
    $partnerHome->link = $request->link;
    $partnerHome->image = $imageName;
    $partnerHome->description = $request->description;
    
    

    $partnerHome->save();
    return redirect()->route('admin.home.partner.list')->with('success', 'Partner saved successfully');
}


public function partnerEdit($id){

    $partners = Partner::find($id);
    return view('admin.page.home.partnersedit',compact('partners'));


}


public function partnerUpdate(Request $request, $id)
{

    // $request->validate([
    //     'title' => 'required|string|max:255',
    //     'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Use "sometimes" to allow optional image updates
        
    // ]);

    $partner = Partner::find($id);

    if (!$partner) {
        return redirect()->back()->with('error', 'banner entry not found');
    }

    // Update the blog entry
    $partner->title = $request->title;
    $partner->description = $request->description;
     $partner->link = $request->link;
    
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('uploads'), $imageName);
        $partner->image = $imageName;
    }
    
  
    $partner->update();

    return redirect()->route('admin.home.partner.list')->with('success', 'Partner Data updated successfully');

}


public function partnerView($id){

    $partner = Partner::find($id);
    return view('admin.page.home.partnersview',compact('partner'));

}

public function partnerDelete($id){

$partner = Partner::find($id);
$partner->delete();
return redirect()->route('admin.home.partner.list')->with('success', 'Partner Deleted successfully');


}

public function studyIndex(){


    $studyData = Study::get();
    
    return view('admin.study.index', compact('studyData'));
}

public function studySave(Request $request)
{
    $data = new Study();
    $data->title = $request->study_title;
    $data->description = $request->study_content;
    $data->icon = $request->study_image;

    // Get the uploaded PDF file
    $pdfFile = $request->file('pdf_file');
    if (!empty($pdfFile)) {
        $fileName = time() . '.' . $pdfFile->getClientOriginalExtension();
        $pdfFile->move(public_path('receipts'), $fileName);
        $path = 'receipts/' . $fileName;

    }
    
    
$data->link = $path;
    // Save the Study model to the database
    $data->save();

    return redirect()->route('admin.study.list')->with('success', 'Data Added successfully');

}


public function studyDelete($id){
    $daat = Study::find($id);
    $daat->delete();
    return redirect()->route('admin.study.list')->with('success', 'Data Deleted successfully');

}

public function studyAdd(){
    return view('admin.study.create');

}

public function studyEdit($id)
{

    $data = Study::find($id);
    return view('admin.study.edit',compact('data'));


}


public function studyUpdate(Request $request) {

    // Find the Study record by ID 
    $data = Study::findOrFail($request->id);

    // Update the attributes
    $data->title = $request->study_title;
    $data->description = $request->study_content;

    // Handle the study image update
    if ($request->hasFile('study_image')) {
        $image = $request->file('study_image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        $data->icon = 'images/' . $imageName;
    }

    // Handle the PDF file update
    if ($request->hasFile('pdf_file')) {
        $pdfFile = $request->file('pdf_file');
        $pdfFileName = time() . '.' . $pdfFile->getClientOriginalExtension();
        $pdfFile->move(public_path('pdf_files'), $pdfFileName);
        $data->link = 'pdf_files/' . $pdfFileName;
    }

    // Save the updated Study model to the database
    $data->save();

    // Redirect to the study list page with success message
    return redirect()->route('admin.study.list')->with('success', 'Study Material updated successfully');
}


public function awadedStudents(){
    $students = User::whereHas('AmountDistribution')->where('role_id', '=', 2)->get();

    return view('admin.awared.list',compact('students'));
}




public function studentsList(){
    $data = JobRequest::get();
    return view('admin.jobs.list',compact('data'));
}

public function addPositions(Request $request){

    $data = new JobPosition();
    $data->name = $request->position;
    $data->status = 1;
    $data->save();
    return redirect()->back()->with('Success', 'Position Added Sucessfully !!');

}

   public function positionAdd(){
    $positions = JobPosition::get();
    return view('admin.jobs.positionAdd',compact('positions'));
   } 


   public function requestDetails($id){
    $data = JobRequest::find($id);
    return view('admin.jobs.details',compact('data'));
   }

}
