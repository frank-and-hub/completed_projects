<?php

namespace App\Http\Controllers\admin;

use App\Traits\HasSearch;
use App\Http\Controllers\Controller;
use App\Models\BodyType;
use Illuminate\Http\Request;
use App\Traits\Common_trait;
use Illuminate\Support\Facades\File; // Add at top if not already
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;


class BodyTypeController extends Controller
{
    use Common_trait;
    public function index(Request $request)
    {

        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $page = $request->input('page', 1);

        $query = BodyType::query();
        $this->applySearch($query, $search); // Apply search logic if needed

        $total = $query->count();
        $maxPage = ceil($total / $limit);

        // Redirect to named route if current page is invalid
        if ($page > 1 && $page > $maxPage && $total > 0) {
            return redirect()->route('admin.bodyTypeIndex', ['limit' => $limit]);
        }


        $bodyTypes = $query->orderBy('id', 'desc')->paginate($limit);

        if ($request->has('search')) {
            $bodyTypes->appends(['search' => $search]);
        }

        // Append limit parameter to pagination links
        $bodyTypes->appends(['limit' => $limit]);

        return view('admin.body-type.index', compact('bodyTypes'));
    }

    public function bodyTypeSave(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique(config('tables.body_type'))->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
            ],
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $this->file_upload($request->file('image'), 'body_type');
        }

        BodyType::create([
            'name' => Str::title(preg_replace('/\s+/', ' ', trim($request->name))),
            'image' => $imagePath,5
        ]);

        return response()->json(['success' => true]);
    }



    public function bodyTypeEdit($id)
    {
        $bodyType = BodyType::findOrFail($id);
        return response()->json($bodyType);
    }

    public function bodyTypeUpdate(Request $request, $id)
    {
        // print_r($request);die();
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique(config('tables.body_type'))
                    ->ignore($id) // ✅ Ignore current record
                    ->where(function ($query) {
                        return $query->whereNull('deleted_at'); // ✅ Handle soft deletes
                    }),
            ],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $bodyType = BodyType::findOrFail($id);

        // If new image is uploaded, delete old image first
        if ($request->hasFile('image') && $bodyType->image) {
            $oldImagePath = public_path($bodyType->image);
            if (File::exists($oldImagePath)) {
                File::delete($oldImagePath);
            }
        }

        // If image removal requested (manual remove)
        if ($request->has('remove_image') && $bodyType->image) {
            $imagePath = public_path($bodyType->image);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
            $bodyType->image = null;
        }

        // Handle new image upload (after deletion)
        if ($request->hasFile('image')) {
            $imagePath = $this->file_upload($request->file('image'), 'body_type');
            $bodyType->image = $imagePath;
        }

        // Update name and save
        $bodyType->name = Str::title(preg_replace('/\s+/', ' ', trim($request->name)));
        $bodyType->save();

        return response()->json(['success' => true]);
    }


    public function bodyTypeDelete($id)
    {
        $bodyType = BodyType::findOrFail($id);
        $bodyType->delete();

        return response()->json(['success' => true]);
    }
}
