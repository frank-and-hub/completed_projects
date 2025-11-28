<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CmsPage;
use Illuminate\Support\Str;
use DB;

class CmsController extends Controller
{
    public function __construct()
    {
        $this->menu = '8';
    }
  public function index(){
    $path = user_permission($this->menu,'view');
    if(!$path){
        $cmsPages = cmsPage::get();
        return view('admin.cms.index',compact('cmsPages'));
    }else{
            return redirect()->route($path);
        }
  }

  public function add(){
      $path = user_permission($this->menu,'add');
    if(!$path){
    return view('admin.cms.add');
    }else{
            return redirect()->route($path);
        }
  }

  public function save(Request $request)
  {
    $saveCms = new CmsPage();
    $saveCms->content = $request->page_content;
    $saveCms->page_name = $request->page_title;
    $saveCms->slug = Str::slug($request->page_title);
    $saveCms->status = 1;
    $saveCms->save();

    
    return redirect()->route('admin.cms.list')->with('success', 'CMS form submitted successfully!');

  }

  public function edit($id){
      $path = user_permission($this->menu,'edit');
    if(!$path){
    $pageEdit = cmsPage::find($id);
    return view('admin.cms.edit',compact('pageEdit'));
  }else{
            return redirect()->route($path);
        }
  }
  public function view($id){
      $path = user_permission($this->menu,'view');
    if(!$path){
    $pageEdit = cmsPage::find($id);
    
    return view('admin.cms.view',compact('pageEdit'));
    }else{
            return redirect()->route($path);
        }
  }
  public function delete($id){
      $path = user_permission($this->menu,'delete');
    if(!$path){
    $pageEdit = cmsPage::find($id);
$pageEdit->delete();
return redirect()->route('admin.cms.list')->with('success', 'CMS Page deleted successfully!');
}else{
            return redirect()->route($path);
        }
  }

  public function update(Request $request)
  {
   $path = user_permission($this->menu,'view');
    if(!$path){
    $updateCms = CmsPage::find($request->id);
    $updateCms->content = $request->page_content;
    $updateCms->page_name = $request->page_title;
    $updateCms->slug = Str::slug($request->page_title);
    // $updateCms->status = $request->status;
    $updateCms->save();

    
    return redirect()->route('admin.cms.list')->with('success', 'CMS form Updated successfully!');
  }else{
            return redirect()->route($path);
        }

  }
  public function socialMedia(){
      $path = user_permission($this->menu,'view');
        if(!$path){
            $social = DB::table('social')->whereNotNull('id')->get();
            return view('admin.cms.social',compact('social'));
        }else{
            return redirect()->route($path);
        }
  }
    public function updateSocialMedia(Request $req)
    {
        $data = [
            'title' => $req->title,
            'icon' => $req->icon,
            'link' => $req->link,
            'status' => 1,
            'created_by'=>auth()->user()->first_name,
            'created_at'=>date('Y-m-d H:i:s')
        ];
    
        $conditions = [
            'id' => $req->submit, 
        ];
    
        // Update or create the record
        DB::table('social')->updateOrInsert($conditions, $data);
        return redirect()->route('admin.cms.social_media')->with('success','social media link updated successfully');
    }

}
