<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\YResponse;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\ParkCategories;
use App\Models\ParkImage;
use App\Models\Subcategory;
use App\Services\RevalidateApiService;
use App\Traits\CommonTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use CommonTraits;


    protected $revalidateApi;

    public function __construct(RevalidateApiService $revalidateApi)
    {
        $this->revalidateApi = $revalidateApi;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->can('categories-show')) {
            abort(404);
        }
        $active_page = "category";
        $page_title = "Categories";
        return view('admin.category.index', compact('active_page', 'page_title'));
    }

    public function dt_list(Request $request)
    {
        $_order = request('order');
        $_columns = request('columns');
        $order_by = $_columns[$_order[0]['column']]['name'];
        $order_dir = $_order[0]['dir'];
        $search = request('search');
        $skip = request('start');
        $take = request('length');

        if ($request->type == 'parent' || $request->type == 'no-child') {
            $query = Category::where('type', $request->type)->where('special_category', 0);
        } else if ($request->type == 'all_special') {
            $query = Category::Where('special_category', 1);
        } else if ($request->type == "parent_special") {
            $query = Category::where('type', 'parent')->where('special_category', 1);
        } else if ($request->type == 'standalone_special') {
            $query = Category::where('type', 'no-child')->where('special_category', 1);
        } else {
            $query = Category::query();
        }

        $recordsTotal = $query->count();

        if (isset($search['value'])) {
            $query->Where(function ($q) use ($search) {
                $q->whereRaw('name LIKE "%' . $search['value'] . '%" ');
            });
        }

        $recordsFiltered = $query->count();
        $data = $query
            ->orderBy($order_by, $order_dir)
            ->skip($skip)
            ->take($take)
            ->get();

        foreach ($data as &$d) {
            $image = $d->image ? $d->image->full_path : asset('images/default.jpg');
            $d->is_set_as_home = $d->is_set_as_home ? 'Yes' : 'No';
            $special_category = ($d->special_category == 1) ? ' (Seasonal Category)' : null;
            $d->type = $d->type == 'no-child' ? 'Standalone' . $special_category : ucwords($d->type) . $special_category;
            $d->priority = $d->priority ?? 'N/A';
            $parkcategorylist = route('admin.category.parkcategory.list', $d->id);
            $d->name = "<a href='" . $parkcategorylist . "'><img src='" . $image . "'alt='Logo' height='50px' width='50px' style='border-radius: 10px;'>" . ' ' . $d->name . "<a/>";
            $editRoute = route('admin.category.edit', $d->id);
            // $ShowChlidRoute =  ($d->type != 'Standalone') ? route('admin.subcategory.index', $d->id) : null;
            $deleteRoute = route('admin.delete.category', $d->id);
            $statusRoute = route('admin.update.status', $d->id);
            $status = ($d->active == 1) ? 'checked' : '';
            $d->total_child_categories = Subcategory::where('category_id', $d->id)->count();
            $id = $d->id;
            // $d->action = view('admin.category._dt_action', compact('d'))->render();
            $d->action = View::make('components.admin.actioncomponent', compact('editRoute', 'deleteRoute', 'statusRoute', 'status', 'id'))->render();
        }

        return [
            "draw" => request('draw'),
            "recordsTotal" => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            "data" => $data,
        ];
    }

    public function create(Request $request)
    {
        $user = $request->user();
        if (!$user->can('categories-show')) {
            abort(404);
        }
        $active_page = "category";
        $page_title = "Create New Category";
        $category = null;
        return view('admin.category.create', compact('active_page', 'page_title', 'category'));
    }

    public function edit(Request $request, Category $category)
    {
        $user = $request->user();
        if (!$user->can('categories-show')) {
            abort(404);
        }
        $active_page = "category";
        $page_title = "Edit Category";
        return view('admin.category.create', compact('active_page', 'page_title', 'category'));
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'image' => ['image', 'mimes:png,jpg', 'max:2048'],
            'type' => ['required', 'in:no-child,parent,special'],
            'priority' => ['required', 'numeric', 'max:65535', 'min:1'],
            'season' => ['required_if:special_category,1', 'in:summer,winter,autumn,spring'],
            // 'description'=>['max:2000']
            'meta_title' => 'nullable|string|max:75|min:10',
            'meta_description' => 'nullable|string|max:200|min:10'
        ], [
            'image.max' => 'The size of the image must not be greater than 1 MB',
        ]);

        $user = $request->user();

        if (!$user->can('categories-show')) {
            abort(404);
        }

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
        }

        if (empty($request->is_set_as_home) && empty($request->is_set_as_carousel) && empty($request->is_display_by_itself)) {
            return back()->with('error', "Please select minimum one display checkbox");
        }

        $slug = $this->generateUniqueSlug($request->name, null, Category::class, 'slug');

        if ($request->id) {
            $category = Category::find($request->id);
            $message = __('admin.category_update');
            $__category = Category::where('id', '!=', $request->id)->where('slug', $slug)->first();

            if ($__category) {
                return back()->with('error', __('admin.name_already_exists'));
            }
        } else {
            $category = new Category;
            $message = __('admin.category_create');
            $__category = Category::where('slug', $slug)->first();

            if ($__category) {
                return back()->with('error', __('admin.name_already_exists'));
            }
        }

        $category->name = $request->name;
        $category->slug = $slug;
        $category->priority = $request->priority;
        $category->type = $request->type;
        $category->description = $request->description;
        $category->is_set_as_home = $request->is_set_as_home ? true : false;
        $category->is_set_as_carousel = $request->is_set_as_carousel ? true : false;
        $category->is_display_by_itself = $request->is_display_by_itself ? true : false;
        $category->special_category = $request->special_category ? true : false;

        if ($request->special_category) {
            $category->season = $request->season;
        } else {
            $category->season = null;
        }

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $image = Media::save_media(file: $request->file('image'), dir: 'category', tags: ['category image'], store_as: 'image');
            $old_media_to_delete = $category->image;
            $category->image_id = $image->id;
            if ($old_media_to_delete) {
                $old_media_to_delete->forceDelete();
            }
        }

        $category->save();

        $category->meta()->updateOrCreate(
            [], // No condition needed if it's morphOne (will auto use the morph keys)
            [
                'title' => $request->meta_title ?? null,
                'description' => $request->meta_description ?? null
            ]
        );

        $this->revalidateApi->revalidateCategory($category);

        // return redirect()->route('admin.category.index')->with('success', $message);
        return redirect()->route('admin.category.edit', $category->id)->with('success', $message);
    }

    public function subcategory_index(Request $request, Category $category)
    {
        $user = $request->user();
        if (!$user->can('categories-show')) {
            abort(404);
        }
        $active_page = "category";
        $page_title = $category->name;

        return view('admin.subcategory.index', compact('active_page', 'page_title', 'category'));
    }

    public function subcategory_dt_list()
    {

        $_order = request('order');
        $_columns = request('columns');
        $order_by = $_columns[$_order[0]['column']]['name'];
        $order_dir = $_order[0]['dir'];
        $search = request('search');
        $skip = request('start');
        $take = request('length');
        $categoryId = request('category_id');

        $query = Subcategory::where('category_id', $categoryId);

        $recordsTotal = $query->count();

        if (isset($search['value'])) {
            $query->Where(function ($q) use ($search) {
                $q->whereRaw('name LIKE "%' . $search['value'] . '%" ');
            });
        }

        $recordsFiltered = $query->count();

        $data = $query
            ->orderBy($order_by, $order_dir)
            ->skip($skip)
            ->take($take)
            ->get();

        foreach ($data as &$d) {
            $image = $d->image ? $d->image->full_path : asset('images/default.jpg');
            $categoryId = $d->category->id;
            $image = "<img src='$image'alt='Logo' height='50px' width='50px' style='border-radius: 10px;'>";

            $d->name = "<span class='role-btn btn-role categoryName' role='button' childid='$d->id' value='$categoryId'>$image $d->name </span>";

            $editRoute = route('admin.subcategory.edit', $d->id);
            $deleteRoute = route('admin.delete.child.category', $d->id);
            $statusRoute = route('admin.update.child.status', $d->id);
            $status = ($d->active == 1) ? 'checked' : '';
            $id = $d->id;
            $d->action = View::make('components.admin.actioncomponent', compact('editRoute', 'deleteRoute', 'statusRoute', 'status', 'id'))->render();
        }

        return [
            "draw" => request('draw'),
            "recordsTotal" => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            "data" => $data,
        ];
    }

    public function subcategory_create(Request $request, Category $category)
    {
        $user = $request->user();
        if (!$user->can('categories-show')) {
            abort(404);
        }
        $active_page = "category";
        $page_title = $category->name;
        $subcategory = null;
        return view('admin.subcategory.create', compact('active_page', 'page_title', 'category', 'subcategory'));
    }

    public function subcategory_edit(Request $request, Subcategory $subcategory)
    {
        $user = $request->user();
        if (!$user->can('categories-show')) {
            abort(404);
        }
        $active_page = "category";

        $category = Category::find($subcategory->category_id);
        $page_title = $category->name;

        return view('admin.subcategory.create', compact('active_page', 'page_title', 'category', 'subcategory'));
    }

    public function category_parks_dt_list(Request $request)
    {
        $user = $request->user();
        if (!$user->can('categories-show')) {
            abort(404);
        }

        $active_page = "category";

        $categoryId = $request->category_id;
        $subcategoryId = $request?->subcategory_id ?? null;

        // Try to find the category or subcategory
        $category = Subcategory::whereCategoryId($categoryId)->find($subcategoryId);
        $query = collect();

        if ($category) {
            $query = $category->parkcategory->pluck('park');
        } else {
            $category = Category::find($categoryId);
            if (!$category) {
                abort(404, 'Category not found');
            }
            $query = $category->parks->pluck('park');
        }

        // Convert to collection if not already
        if (!$query instanceof \Illuminate\Support\Collection) {
            $query = collect($query);
        }

        // Filter by subadmin's parks
        if ($user->hasRole('subadmin')) {
            $query = $query->where('created_by_id', $user->id);
        }

        // Convert to Eloquent builder to apply pagination, order, etc.
        $query = $query->sortBy(request('order.0.dir') === 'desc' ? request('columns.' . request('order.0.column') . '.name') : null)
            ->values(); // Reset keys after sort

        $recordsTotal = $query->count();

        // Optional: implement search logic
        $searchValue = request('search.value');
        if (!empty($searchValue)) {
            $query = $query->filter(function ($item) use ($searchValue) {
                return str_contains(strtolower($item->name), strtolower($searchValue));
            });
        }

        $recordsFiltered = $query->count();

        // Pagination
        $start = request('start', 0);
        $length = request('length', 10);
        $pagedData = $query->slice($start, $length)->values();
        $data = $pagedData;

        foreach ($data as &$d) {
            $detailsRoute = route('admin.park.details', $d->id);
            $park_image_1 = $d->park_images()->where('set_as_banner', '1')->first();

            $image = $park_image_1 ? $park_image_1->media->full_path : asset('images/default.jpg');
            $d->name = "<img src=' $image' alt='Logo' height='50px' width='50px' style='border-radius: 10px;'> <a class='text-reset' style='text-decoration:none;' href='$detailsRoute' rel='tooltip' title='Go To Details'>" . ucfirst($d->name) . "</a>";
            $d->city = $d->city ?? 'N/A';
        }

        return [
            "draw" => request('draw'),
            "recordsTotal" => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            "data" => $data,
        ];
    }

    public function subcategory_save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'image' => ['image', 'mimes:png,jpg', 'max:2048'],
            'meta_title' => 'nullable|string|max:75|min:10',
            'meta_description' => 'nullable|string|max:200|min:10'
        ], [
            'image.max' => 'The size of the image must not be greater than 2 MB',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
        }

        $slug = $this->generateUniqueSlug($request->name, null, Subcategory::class, 'slug');
        $category = Category::find($request->category_id);
        if ($request->id) {
            $subcategory = Subcategory::find($request->id);
            $message = __('admin.subcategory_update');
            $__subcategory = Subcategory::where('id', '!=', $request->id)->where('slug', $slug)->first();
            if ($__subcategory) {
                return back()->with('error', __('admin.name_already_exists'));
            }
        } else {
            $subcategory = new Subcategory;
            $message = __('admin.subcategory_create');
            $__subcategory = Subcategory::where('slug', $slug)->first();
            if ($__subcategory) {
                return back()->with('error', __('admin.name_already_exists'));
            }
        }

        $subcategory->category_id = $category->id;
        $subcategory->name = $request->name;
        $subcategory->description = $request->description;

        $subcategory->slug = $slug;

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $image = Media::save_media(file: $request->file('image'), dir: 'subcategory', tags: ['subcategory image'], store_as: 'image');
            $old_media_to_delete = $subcategory->image;
            $subcategory->image_id = $image->id;
            if ($old_media_to_delete) {
                $old_media_to_delete->forceDelete();
            }
        }
        $subcategory->save();

        $subcategory->meta()->updateOrCreate(
            [], // No condition needed if it's morphOne (will auto use the morph keys)
            [
                'title' => $request->meta_title ?? null,
                'description' => $request->meta_description ?? null
            ]
        );

        $this->revalidateApi->revalidateCategory($category);

        return redirect()->route('admin.category.edit', $category->id)->with('success', $message);
        // return redirect()->route('admin.subcategory.index', $category->id)->with('success', $message);
    }

    public function get_subcategories(Request $request)
    {
        // $subcategories = Subcategory::whereIn('category_id', $request->categories_id)->with('category')->get();
        $subcategories = Subcategory::where('category_id', $request->categories_id)->with('category')->get();
        return YResponse::json(data: ['subcategories' => $subcategories]);
    }

    public function categories(Request $request)
    {
        if ($request->ajax()) {
            $category = Category::where('type', $request->type)->with('subcategories', function ($q) {
                $q->with('parkcategory');
            })->get();
            return response()->json([
                'data' => $category
            ]);
        }
    }

    public function delete_childCategory(Request $request, Subcategory $subcategory)
    {
        if ($request->ajax()) {
            $category = Category::findOrFail($subcategory->category_id);
            ParkCategories::where('subcategory_id', $subcategory->id)->delete();

            $this->revalidateApi->revalidateCategory($category);

            if ($subcategory) {
                $subcategory->delete();
                return response()->json([
                    'status' => 1,
                    'msg' => "Child category deleted successfully.",
                ]);
            }
        }
    }

    public function delete_category(Request $request, Category $category)
    {
        if ($request->ajax()) {
            $categoryId = $category->id;
            ParkCategories::where('category_id', $categoryId)->delete();
            Subcategory::whereIn('category_id', $categoryId)->delete();

            $this->revalidateApi->revalidateCategory($category);

            if ($category) {
                $category->delete();
                return response()->json([
                    'status' => 1,
                    'msg' => "Category deleted successfully.",
                ]);
            }
        }
    }

    public function update_child_status(Request $request, Subcategory $subcategory)
    {
        if ($request->ajax()) {
            try {

                $category = Category::findOrFail($subcategory->category_id);

                $subcategory_status = $subcategory->update(['active' => $request->status]);
                $parkCategories = ParkCategories::where('subcategory_id', $subcategory->id)->get();

                if (!empty($parkCategories)) {
                    ParkCategories::where('subcategory_id', $subcategory->id)->update(['active' => $request->status]);
                }

                $this->revalidateApi->revalidateCategory($category);

                $msg = 'Child category has been activated';

                if ($request->status == 0) {
                    $msg = "Child category has been inactivated.";
                }

                if ($subcategory_status) {
                    return response()->json([
                        'msg' => $msg
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 0,
                    'msg' => 'An error occurred while updating the status: ' . $e->getMessage()
                ]);
            }
        }
    }

    public function update_status(Request $request, Category $category)
    {
        if ($request->ajax()) {
            $subcategory_status = $category->update(['active' => $request->status]);

            $parkCategories = ParkCategories::where('category_id', $category->id)->get();
            if (!empty($parkCategories)) {
                ParkCategories::where('category_id', $category->id)->update(['active' => $request->status]);
            }

            $msg = 'Category has been activated';
            if ($request->status == 0) {
                $msg = "Category has been inactivated.";
            }

            $this->revalidateApi->revalidateCategory($category);

            if ($subcategory_status) {
                return response()->json([
                    'msg' => $msg
                ]);
            }
        }
    }

    function deleteImg(Request $request, Category $category)
    {
        if ($request->ajax()) {
            $category->image->delete();
            $update = $category->update(['image_id' => null]);
            $this->revalidateApi->revalidateCategory($category);
            return response()->json(['status' => $update]);
        }
    }

    function deleteChildImg(Request $request, Subcategory $subcategory)
    {
        if ($request->ajax()) {
            $category = Category::findOrFail($subcategory->current_id);
            $subcategory->image->delete();
            $update = $subcategory->update(['image_id' => null]);

            $this->revalidateApi->revalidateCategory($category);

            return response()->json(['status' => $update]);
        }
    }

    public function parkcategorylist(Request $request, Category $category)
    {
        $active_page = "park";
        $page_title = "Park";
        $breadcrumbs = collect([['route' => route('admin.category.index'), 'name' => 'Categories']]);

        $active_page = "category";
        $page_title = ucfirst($category->name);

        return view('admin.category.park_category', compact('active_page', 'page_title', 'breadcrumbs', 'category'));
    }

    public function parkCategoryDt(Request $request)
    {
        $_order = request('order');
        $_columns = request('columns');
        $order_by = $_columns[$_order[0]['column']]['name'];
        $order_dir = $_order[0]['dir'];
        $search = request('search');
        $skip = request('start');
        $take = request('length');
        $user = $request->user();
        $query = '';
        if ($request->type == 'with_child') {
            $query = ParkCategories::select(
                '*',
                DB::raw('(select name from parks where parks.id=park_categories.park_id) as name')
            )
                ->where('subcategory_id', $request->subcategory_id);
            // ->where('subcategory_id',5);

        } else {
            $query = ParkCategories::select(
                '*',
                DB::raw('(select name from parks where parks.id=park_categories.park_id) as name')
            )->where('category_id', $request->id);
        }

        $recordsTotal = $query->count();

        if (isset($search['value'])) {
            $query->WhereHas('park', function ($q) use ($search) {
                $q->WhereRaw("name LIKE '%" . $search['value'] . "%'");
            });
        }

        $recordsFiltered = $query->count();
        $data = $query->skip($skip)->take($take)->orderByRaw("$order_by $order_dir")->groupBy('park_id')->get();

        foreach ($data as &$d) {
            $detailsRoute = route('admin.park.details', $d->park->id);
            $park_image_1 = $d->park->park_images()->where('set_as_banner', '1')->first();
            $park_images = ParkImage::where('park_id', $d->park->id)
                ->where('status', '1')->get();

            $image = $park_image_1 ? $park_image_1->media->full_path : asset('images/default.jpg');
            $d->name = "<img src=' $image' alt='Logo' height='50px' width='50px' style='border-radius: 10px;'> <a class='text-reset' style='text-decoration:none;' href='$detailsRoute' rel='tooltip' title='Go To Details'>" . ucfirst($d->park->name) . "</a>";

            $editRoute = $user->hasRole('admin') ? route('admin.park.edit', $d->park->id) : null;
            $deleteRoute = $user->hasRole('admin') ? route('admin.delete.park', $d->park->id) : null;

            $imageEditRoute = $imageUplodRoute = null;
            if (count($park_images) == 0) {
                $imageUplodRoute = route('admin.park.image.upload', $d->park->id);
            } else {
                $imageEditRoute = route('admin.park.image.edit', $d->park->id);
            }

            $statusRoute = $user->hasRole('admin') ? route('admin.update.park.status', $d->park->id) : null;
            $status = ($d->park->active == 1) ? 'checked' : '';
            $d->action = View::make('components.admin.actioncomponent', compact('editRoute', 'deleteRoute', 'imageEditRoute', 'imageUplodRoute', 'statusRoute', 'status'))->render();
        }

        return [
            "draw" => request('draw'),
            "recordsTotal" => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            "data" => $data,
        ];
    }
}
