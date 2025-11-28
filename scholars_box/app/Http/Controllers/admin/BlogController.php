<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use Auth;
use Illuminate\Support\Str;


class BlogController extends Controller
{
    public function __construct()
    {
        $this->menu = '3';
    }
    public function index(){
        $path = user_permission($this->menu,'view');
        if(!$path){
            $blogs = Blog::get();
            return view('admin.blog.list', compact('blogs'));
        }else{
            return redirect()->route($path);
        }
    }

    public function add(){
        $path = user_permission($this->menu,'add');
        if(!$path){
            return view('admin.blog.add');
        }else{
            return redirect()->route($path);
        }
    }
    public function store(Request $request)
    {
        $path = user_permission($this->menu,'add');
        if(!$path){
            $request->validate([
                'blog_title' => 'required|string|max:255',
                // 'blog_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'blog_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',

                'blog_content' => 'required|string',
            ]);

            $imageName = null; // Initialize the variable with a default value

            if ($request->hasFile('blog_image')) {
                $image = $request->file('blog_image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads'), $imageName); // Save the image to the 'uploads' directory
            }

            $title = $request->input('blog_title');
            $slug = Str::slug($title);
            $originalSlug = $slug;
            $counter = 1;
            while (Blog::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $blog = new Blog;
            $blog->blog_title = $request->input('blog_title');
            $blog->image = $imageName;
            $blog->description = $request->input('blog_content');
            $blog->tags = $request->input('tags');
            $blog->teg = $request->input('blog_tag');
            $blog->created_by = 'scholarsbox';
            $blog->slug = $slug;
            $blog->save();

        return redirect()->route('admin.blog.list')->with('success', 'Blog entry saved successfully');
        }else{
            return redirect()->route($path);
        }

    }


    public function view($id){
        $path = user_permission($this->menu,'view');
        if(!$path){
            $blog = Blog::find($id);
            return view('admin.blog.view',compact('blog'));
        }else{
            return redirect()->route($path);
        }

    }

    public function edit($id){
        $path = user_permission($this->menu,'edit');
        if(!$path){
            $blog = Blog::find($id);
            return view('admin.blog.edit',compact('blog','id'));
        }else{
            return redirect()->route($path);
        }
    }

    public function delete($id){
        $path = user_permission($this->menu,'delete');
        if(!$path){
            $blog = Blog::whereId($id)->delete();
            return redirect()->route('admin.blog.list')->with('success', 'Blog Deleted successfully');
        }else{
            return redirect()->route($path);
        }

    }

    public function update(Request $request, $id)
    {

        $path = user_permission($this->menu,'edit');
        if(!$path){
            $request->validate([
                'blog_title' => 'required|string|max:255',
                'blog_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Use "sometimes" to allow optional image updates
                'blog_content' => 'required|string',
            ]);

            $blog = Blog::find($id);

            if (!$blog) {
                return redirect()->back()->with('error', 'Blog entry not found');
            }

            // Update the blog entry
            $blog->blog_title = $request->input('blog_title');
            $title = $request->input('blog_title');
            $slug = Str::slug($title);
            $originalSlug = $slug;
            $counter = 1;
            while (Blog::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            if ($request->hasFile('blog_image')) {
                $image = $request->file('blog_image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads'), $imageName);
                $blog->image = $imageName;
            }
            $blog->description = $request->input('blog_content');
            $blog->created_by = 'scholarsbox';
            $blog->teg = $request->input('blog_tag');
            $blog->tags = $request->input('tags');
            $blog->slug = $slug;

            $blog->save();

            return redirect()->route('admin.blog.list')->with('success', 'Blog entry updated successfully');
        }else{
            return redirect()->route($path);
        }
    }
}
