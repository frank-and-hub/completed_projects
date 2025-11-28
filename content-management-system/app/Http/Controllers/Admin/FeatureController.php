<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\YResponse;
use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\FeatureType;
use App\Models\Media;
use App\Models\ParkFeature;
use App\Services\RevalidateApiService;
use App\Traits\CommonTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class FeatureController extends Controller
{
    use CommonTraits;

    protected $revalidateApi;

    public function __construct(RevalidateApiService $revalidateApi)
    {
        $this->revalidateApi = $revalidateApi;
    }

    public function index(Request $request)
    {
        // $types = ['all','normal','popular'];
        // if(!in_array($type, $types)){
        //     return redirect()->back()->with('error',__('admin.default_error_message'));
        // }

        // $feature_type = $type;
        $user = $request->user();
        if (!$user->can('features-show')) {
            abort(404);
        }
        $active_page = "feature_type";
        $page_title = "Features";
        return view('admin.feature_type.index', compact('active_page', 'page_title'));
    }

    public function dt_list(Request $request)
    {
        $_order = request('order');
        $_columns = request('columns');
        $order_by = $_columns[$_order[0]['column']]['name'] ?? 'name';
        $order_dir = $_order[0]['dir'];
        $search = request('search')['value'];
        $start = $skip = request('start');
        $length = $take = request('length');
        $displayType = $request->input('display_by');
        $type = $request->input('type');

        if (false) {

            $query = FeatureType::withCount('parks');
            if (empty($request->type) || $request->type == 'all') {
                // $query = FeatureType::query();
            } else if ($request->type == 'seo') {
                $query->whereIn('slug', $this->metaFeatures(true));
            } else {
                $query->where('type', $request->type);
            }

            $recordsTotal = $query->count();

            if (isset($search['value'])) {
                $query->Where(function ($q) use ($search) {
                    $q->whereRaw('name LIKE "%' . $search['value'] . '%" ');
                });
            }

            $recordsFiltered = $query->count();
            $data = $query->orderBy($order_by, $order_dir)
                ->skip($skip)
                ->take($take)
                ->get();
        } else {

            $featureQuery = Feature::withCount('parks');
            $featureTypeQuery = FeatureType::withCount('parks');

            // Handle filtering by type
            if ($type) {
                if ($type === 'seo') {
                    $metaSlugs = $this->metaFeatures(true);
                    $featureQuery->whereIn('slug', $metaSlugs);
                    $featureTypeQuery->whereIn('slug', $metaSlugs);
                } else if (in_array($type, ['popular', 'normal'])) {
                    $featureQuery->where('type', $type);
                    $featureTypeQuery->where('type', $type);
                } else {
                    // $featureQuery->where('type', $type);
                    // $featureTypeQuery->where('type', $type);
                }
            }


            $recordsData = [
                'child' => $featureQuery->count(),
                'parent' => $featureTypeQuery->count(),
                // 'all' => $featureQuery->count() + $featureTypeQuery->count(),
                'all' => $featureTypeQuery->count(),
            ];

            $recordsTotal = $recordsData[$displayType];

            // Apply search
            if ($search) {
                $featureQuery->where('name', 'like', "%{$search}%");
                $featureTypeQuery->where('name', 'like', "%{$search}%");
            }

            // Get total and filtered counts
            $recordsFiltered = $recordsData[$displayType];

            // Get paginated, ordered results
            $dataArray = [
                'child' => $featureQuery->orderBy($order_by, $order_dir)->skip($start)->take($length)->get(),
                'parent' => $featureTypeQuery->orderBy($order_by, $order_dir)->skip($start)->take($length)->get(),
                // 'all' => collect(
                //     $featureQuery->orderBy($order_by, $order_dir)->skip($start)->take($length)->get()
                //         ->merge($featureTypeQuery->orderBy($order_by, $order_dir)->skip($start)->take($length)->get())
                // ),
                'all' => $featureTypeQuery->orderBy($order_by, $order_dir)->skip($start)->take($length)->get(),
            ];

            $data = $dataArray[$displayType];
        }

        foreach ($data as &$d) {
            $image = $d->image ? $d->image->full_path : asset('images/default.jpg');
            $d->type = $request->type == 'seo' ? 'SEO' : ucfirst($d->type);
            $d->name = "<img src='" . $image . "' alt='Logo' height='50px' width='50px' style='border-radius: 10px;'>" . ' ' . $d->name;

            $editRoute = route('admin.feature_type.edit', $d->id);

            if ($d->features) {
                $deleteRoute = route('admin.delete.parent_feature', $d->id);
                $statusRoute = route('admin.update.feature', $d->id);
            } else {
                $deleteRoute = route('admin.delete.child_feature', $d->id);
                $statusRoute = route('admin.update.child_feature', $d->id);
            }

            $status = ($d->active == 1) ? 'checked' : '';
            $d->total_child_features = $d->features ? count($d->features) : $d->feature_type->name;
            $id = $d->id;
            $d->priority = $d->priority ?? 'NA';
            $d->related_parks = $d->parks_count;
            $d->action = View::make('components.admin.actioncomponent', compact('editRoute', 'deleteRoute', 'status', 'id', 'statusRoute'))->render();
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
        if (!$user->can('features-show')) {
            abort(404);
        }
        $active_page = "feature_type";
        $page_title = "Create Feature Type";
        $feature_type = null;

        return view('admin.feature_type.create', compact('active_page', 'page_title', 'feature_type'));
    }

    public function edit(Request $request, FeatureType $feature_type)
    {
        $user = $request->user();
        if (!$user->can('features-show')) {
            abort(404);
        }
        $active_page = "feature_type";
        $page_title = "Edit Features";
        // $feature_type = FeatureType::find($id);
        return view('admin.feature_type.create', compact('active_page', 'page_title', 'feature_type'));
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'in:normal,popular'],
            // 'priority' => ['required_if:type,==,popular|','min:1', 'max:65535'],
            'image' => ['image', 'mimes:png,jpg', 'max:1024'],
            'meta_title' => 'nullable|string|max:75|min:10',
            'meta_description' => 'nullable|string|max:200|min:10'
        ], [
            'image.max' => 'The size of the image must not be greater than 1 MB',
        ]);

        $validator->sometimes('priority', 'required|min:1|max:65535', function () use ($request) {
            return $request->type == 'popular';
        });

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
        }

        $features_array = explode(',', $request->features);
        $slug = $this->generateUniqueSlug($request->name, null, FeatureType::class, 'slug');

        if ($request->id) {
            $feature_type = FeatureType::find($request->id);
            $message = __('admin.feature_update');
            $__feature_type = FeatureType::where('id', '!=', $request->id)->where('slug', $slug)->first();
            if ($__feature_type) {
                return back()->with('error', __('admin.name_already_exists'));
            }
        } else {
            $feature_type = new FeatureType;
            $message = __('admin.feature_create');
            $__feature_type = FeatureType::where('slug', $slug)->first();
            if ($__feature_type) {
                return back()->with('error', __('admin.name_already_exists'));
            }
        }

        $feature_type->name = $request->name;
        $feature_type->slug = $slug;
        $feature_type->priority = $request->priority;
        $feature_type->type = $request->type;
        $old_media_to_delete = null;

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $image = Media::save_media(file: $request->file('image'), dir: 'feature', tags: ['feature image'], store_as: 'image');
            $old_media_to_delete = $feature_type->image;
            $feature_type->image_id = $image->id;
        }

        if ($old_media_to_delete) {
            $old_media_to_delete->forceDelete();
        }

        $feature_type->save();

        $feature_type->meta()->updateOrCreate(
            [], // No condition needed if it's morphOne (will auto use the morph keys)
            [
                'title' => $request->meta_title ?? null,
                'description' => $request->meta_description ?? null
            ]
        );

        $this->revalidateApi->revalidateFeature($feature_type);
        // return redirect()->route('admin.feature_type.index')->with('success', $message);

        return redirect()->route('admin.feature_type.edit', $feature_type->id)->with('success', $message);
    }

    public function feature_index(Request $request, FeatureType $feature_type)
    {
        $user = $request->user();
        if (!$user->can('features-show')) {
            abort(404);
        }
        $active_page = "feature_type";
        // $feature_type = FeatureType::find($id);
        $page_title = $feature_type->name;
        return view('admin.feature.index', compact('active_page', 'page_title', 'feature_type'));
    }

    public function feature_dt_list(Request $request)
    {
        $_order = request('order');
        $_columns = request('columns');
        $order_by = $_columns[$_order[0]['column']]['name'];
        $order_dir = $_order[0]['dir'];
        $search = request('search');
        $skip = request('start');
        $take = request('length');
        $feature_type_id = request('feature_type_id');

        $query = Feature::withCount('parks')->where('feature_type_id', $feature_type_id);

        $recordsTotal = $query->count();

        if (isset($search['value'])) {
            $query->Where(function ($q) use ($search) {
                $q->whereRaw('name LIKE "%' . $search['value'] . '%" ');
            });
        }

        $recordsFiltered = $query->count();

        $data = $query
            ->orderBy($order_by, $order_dir)->skip($skip)->take($take)->get();
        foreach ($data as &$d) {
            $image = $d->image ? $d->image->full_path : asset('images/default.jpg');
            $d->name = "<img src='" . $image .
                "'alt='Logo' height='50px' width='50px' style='border-radius: 10px;'>" . ' ' . $d->name;
            $d->type = ucwords($d->type);
            // $d->prioriy = $d->priority;
            // if ($request->type !== 'seo') {
            $d->related_parks = $d->parks_count;
            $d->action = view('admin.feature._dt_action', compact('d'))->render();
            // }else{
            //     $d->action = '';
            // }
        }

        return [
            "draw" => request('draw'),
            "recordsTotal" => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            "data" => $data,
        ];
    }

    public function feature_create(Request $request, FeatureType $feature_type)
    {
        $user = $request->user();
        if (!$user->can('features-show')) {
            abort(404);
        }
        $active_page = "feature_type";
        // $feature_type = FeatureType::find($id);
        $page_title = $feature_type->name;
        $feature = null;
        return view('admin.feature.create', compact('active_page', 'page_title', 'feature_type', 'feature'));
    }

    public function feature_edit(Request $request, Feature $feature)
    {
        $user = $request->user();
        if (!$user->can('features-show')) {
            abort(404);
        }
        $active_page = "feature_type";
        $feature_type = FeatureType::find($feature->feature_type_id);
        $page_title = $feature_type->name;

        return view('admin.feature.create', compact('active_page', 'page_title', 'feature_type', 'feature'));
    }

    public function feature_save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'feature_type_id' => ['required', 'exists:feature_types,id'],
            'image' => ['image', 'mimes:png,jpg', 'max:1024'],
            // 'priority' => ['required_if:type,==,popular', 'numeric', 'min:1', 'max:65535'],
            'type' => ['nullable', 'in:normal,popular'],
            'meta_title' => 'nullable|string|max:75|min:10',
            'meta_description' => 'nullable|string|max:200|min:10'
        ], [
            'image.max' => 'The size of the image must not be greater than 1 MB',
        ]);

        $validator->sometimes('priority', 'required|min:1|max:65535', function () use ($request) {
            return $request->type == 'popular';
        });

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
        }

        $slug = $this->generateUniqueSlug($request->name, null, FeatureType::class, 'slug');
        $feature_type = FeatureType::find($request->feature_type_id);
        if ($request->id) {
            $feature = Feature::find($request->id);
            $message = __('admin.feature_update');
            $__feature = Feature::where('id', '!=', $request->id)->where('feature_type_id', $feature_type->id)->where('slug', $slug)->first();
            if ($__feature) {
                return back()->with('error', __('admin.name_already_exists'));
            }
        } else {
            $feature = new Feature;
            $message = __('admin.feature_create');
            $__feature = Feature::where('slug', $slug)->where('feature_type_id', $feature_type->id)->first();
            if ($__feature) {
                return back()->with('error', __('admin.name_already_exists'));
            }
        }

        $feature->feature_type_id = $feature_type->id;
        $feature->name = $request->name;
        $feature->slug = $slug;
        $feature->priority = $request->priority;
        $feature->type = $request->type;

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $image = Media::save_media(file: $request->file('image'), dir: 'feature', tags: ['feature image'], store_as: 'image');
            $old_media_to_delete = $feature->image;
            $feature->image_id = $image->id;
            if ($old_media_to_delete) {
                $old_media_to_delete->forceDelete();
            }
        }

        $feature->save();

        $feature->meta()->updateOrCreate(
            [], // No condition needed if it's morphOne (will auto use the morph keys)
            [
                'title' => $request->meta_title ?? null,
                'description' => $request->meta_description ?? null
            ]
        );

        $this->revalidateApi->revalidateFeature($feature_type);

        // return redirect()->route('admin.feature.index', $feature_type->id)->with('success', $message);
        return redirect()->route('admin.feature_type.edit', $feature_type->id)->with('success', $message);
    }

    public function get_features(Request $request)
    {
        $features = Feature::whereIn('feature_type_id', $request->feature_type_ids)->with('feature_type')->get();
        return YResponse::json(data: ['features' => $features]);
    }

    public function child_features_dt(Request $request)
    {
        $_order = request('order');
        $_columns = request('columns');
        $order_by = $_columns[$_order[0]['column']]['name'];
        $order_dir = $_order[0]['dir'];
        $search = request('search');
        $skip = request('start');
        $take = request('length');

        $query = Feature::withCount('parks');
        if (!empty($request->feature_type_id)) {
            if (empty($request->type) || $request->type === 'all') {
                $query->where('feature_type_id', $request->feature_type_id);
            } else {
                $query->where('feature_type_id', $request->feature_type_id)
                    ->where('type', $request->type);
            }
        } else {
            if (empty($request->type) || $request->type === 'all') {
                // $query = Feature::query();
            } else if ($request->type === 'seo') {
                $query->whereIn('slug', $this->metaFeatures(false));
            } else {
                $query->where('type', $request->type);
            }
        }

        $recordsTotal = $query->count();

        if (isset($search['value'])) {
            $query->Where(function ($q) use ($search) {
                $q->whereRaw('name LIKE "%' . $search['value'] . '%" ');
            });
        }

        $recordsFiltered = $query->count();

        $data = $query->orderBy($order_by, $order_dir)
            ->skip($skip)
            ->take($take)
            ->get();
        foreach ($data as &$d) {
            $image = $d->image ? $d->image->full_path : asset('images/default.jpg');
            $d->name = "<img src='" . $image . "'alt='Logo' height='50px' width='50px' style='border-radius: 10px;'>" . ' ' . $d->name;
            $d->type = $request->type == 'seo' ? 'SEO' : ucfirst($d->type);
            $deleteRoute = route('admin.delete.child_feature', $d->id);
            $editRoute = route('admin.feature.edit', $d->id);
            $statusRoute = route('admin.update.child_feature', $d->id);
            $status = ($d->active == 1) ? 'checked' : '';
            $id = $d->id;
            $d->parent_feature = ucfirst($d->feature_type->name);
            $d->image_ = "<img src='$image' alt='Logo' height='50px' width='50px' style='border-radius: 10px;'>";
            $d->related_parks = $d->parks_count;
            $d->action = View::make('components.admin.actioncomponent', compact('deleteRoute', 'editRoute', 'statusRoute', 'status', 'id'))->render();
        }

        return [
            "draw" => request('draw'),
            "recordsTotal" => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            "data" => $data,
        ];
    }

    public function delete_feature(Request $request, Feature $feature)
    {
        if ($request->ajax()) {
            $featureType = FeatureType::findOrFail($feature->feature_type_id);
            ParkFeature::where('feature_id', $feature->id)->delete();
            $delete_feature = $feature->delete();

            $this->revalidateApi->revalidateFeature($featureType);

            if ($delete_feature) {
                return response()->json([
                    'status' => 1,
                    'msg' => __('admin.child_feature_delete'),
                ]);
            }
        }
    }

    public function delete_parent_feature(Request $request, FeatureType $feature_type)
    {
        if ($request->ajax()) {
            ParkFeature::where('feature_type_id', $feature_type->id)->delete();
            $feature_type_id = collect(clone $feature_type->pluck('id'));
            Feature::whereIn('feature_type_id', $feature_type_id)->delete();

            $this->revalidateApi->revalidateFeature($feature_type);

            if ($feature_type->delete()) {
                return response()->json([
                    'status' => 1,
                    'msg' => "Parent feature deletes successfully."
                ]);
            }
        }
    }

    public function update_child_feature(Request $request, Feature $feature)
    {
        if ($request->ajax()) {
            $featureType = FeatureType::findOrFail($feature->feature_type_id);
            $featureState = $feature->update(['active' => (int) $request->status]);
            $parkFeature = ParkFeature::where('feature_id', $request->current_id)->get();

            if (!empty($parkFeature)) {
                ParkFeature::where('feature_id', $request->current_id)->update(['active' => (int) $request->status]);
            }

            $msg = 'Child feature has been activated';
            if ($request->status == 0) {
                $msg = "Child feature has been inactivated.";
            }
            $this->revalidateApi->revalidateFeature($featureType);

            if ($featureState) {
                return response()->json([
                    'msg' => $msg
                ]);
            }
        }
    }

    public function update_feature(Request $request, FeatureType $feature_types)
    {
        if ($request->ajax()) {
            $featureTypeStatue = $feature_types->update(['active' => (int) $request->status]);
            $msg = 'Parent feature has been activated';
            if ($request->status == 0) {
                $msg = "Parent feature has been inactivated.";
            }

            $this->revalidateApi->revalidateFeature($feature_types);

            if ($featureTypeStatue) {
                return response()->json([
                    'msg' => $msg
                ]);
            }
        }
    }

    public function popular_childFeature_dblist(Request $request)
    {
        if ($request->ajax()) {

            $_order = request('order');
            $_columns = request('columns');
            $order_by = $_columns[$_order[0]['column']]['name'];
            $order_dir = $_order[0]['dir'];
            $search = request('search');
            $skip = request('start');
            $take = request('length');
            $type = request('type');
            $displayBy = request('display_by');

            $query = Feature::withCount('parks')->where('active', 1);
            if ($type != 'seo') {
                $query->where('type', $type);
            } else {
                $query->whereIn('slug', $this->metaFeatures(false));
            }
            $query = $query->with('feature_type');

            $recordsTotal = $query->count();

            if (isset($search['value'])) {
                $query->Where(function ($q) use ($search) {
                    $q->whereRaw('name LIKE "%' . $search['value'] . '%" ');
                });
            }

            $recordsFiltered = $query->count();

            $data = $query
                ->orderBy($order_by, $order_dir)->skip($skip)->take($take)->get();
            foreach ($data as &$d) {
                $image = $d->image ? $d->image->full_path : asset('images/default.jpg');
                $d->name = "<img src='" . $image . "'alt='Logo' height='50px' width='50px' style='border-radius: 10px;'>" . ' ' . ucfirst($d->name);
                $d->parent_feature = $type == 'seo' ? 'SEO' : ucfirst($d->feature_type->name);
                $d->priority = $type == 'seo' ? ucfirst($d->feature_type->name) : ($d->priority ?? 'NA');
                $deleteRoute = route('admin.delete.child_feature', $d->id);
                $editRoute = route('admin.feature.edit', $d->id);
                $statusRoute = route('admin.update.child_feature', $d->id);
                $d->related_parks = $d->parks_count;
                $d->action = View::make('components.admin.actioncomponent', compact('deleteRoute', 'editRoute'))->render();
            }
            return [
                "draw" => request('draw'),
                "recordsTotal" => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                "data" => $data,
            ];
        }
    }

    public function reset_feature_img(Request $request, FeatureType $feature_types)
    {
        if ($request->ajax()) {
            $feature_types->image->delete();
            $update = $feature_types->update(['image_id' => null]);
            $this->revalidateApi->revalidateFeature($feature_types);
            return response()->json(['status' => $update]);
        }
    }

    public function reset_child_img(Request $request, Feature $feature)
    {
        if ($request->ajax()) {
            $feature_types = FeatureType::findOrFail($feature->id);
            $feature->image->delete();
            $update = $feature->update(['image_id' => null]);
            $this->revalidateApi->revalidateFeature($feature_types);
            return response()->json(['status' => $update]);
        }
    }

    public function new_dt_list(Request $request)
    {
        $order = $request->input('order')[0];
        $columns = $request->input('columns');
        $order_by = $columns[$order['column']]['name'] ?? 'name';
        $order_dir = $order['dir'] ?? 'asc';
        $search = $request->input('search.value');
        $start = $request->input('start');
        $length = $request->input('length');
        $type = $request->input('type');
        $displayType = $request->input('display_by');

        $featureQuery = Feature::withCount('parks');
        $featureTypeQuery = FeatureType::withCount('parks');

        if ($type) {
            if ($type === 'seo') {
                $metaSlugs = $this->metaFeatures(true);
                $featureQuery->whereIn('slug', $metaSlugs);
                $featureTypeQuery->whereIn('slug', $metaSlugs);
            } else if ($type != 'all') {
                $featureQuery->where('type', $type);
                $featureTypeQuery->where('type', $type);
            }
        }

        // Apply search
        if ($search) {
            $featureQuery->where('name', 'like', "%{$search}%");
            $featureTypeQuery->where('name', 'like', "%{$search}%");
        }

        // Get full datasets before paginating
        $featureResults = $featureQuery->get();
        $featureTypeResults = $featureTypeQuery->get();

        // Total record counts (before filtering)
        $recordsTotalArray = [
            'child' => Feature::count(),
            'parent' => FeatureType::count(),
            'all' => Feature::count() + FeatureType::count(),
        ];

        // Filtered record counts (after search & filters)
        $recordsFilteredArray = [
            'child' => $featureResults->count(),
            'parent' => $featureTypeResults->count(),
            'all' => $featureResults->count() + $featureTypeResults->count(),
        ];

        // Get paginated and sorted data
        if ($displayType === 'all') {
            // Merge both collections
            $merged = $featureResults->merge($featureTypeResults);

            // Sort by the desired column
            $sorted = $merged->sortBy($order_by, SORT_REGULAR, $order_dir === 'desc')->values();

            // Paginate manually
            $data = $sorted->slice($start, $length)->values();
        } elseif ($displayType === 'child') {
            $data = $featureResults->sortBy($order_by, SORT_REGULAR, $order_dir === 'desc')
                ->slice($start, $length)
                ->values();
        } else { // 'parent'
            $data = $featureTypeResults->sortBy($order_by, SORT_REGULAR, $order_dir === 'desc')
                ->slice($start, $length)
                ->values();
        }

        // Set counts
        $recordsTotal = $recordsTotalArray[$displayType];
        $recordsFiltered = $recordsFilteredArray[$displayType];

        foreach ($data as $d) {
            $isChild = $d->feature_type_id ? true : null;
            $image = $d->image ? $d->image->full_path : asset('images/default.jpg');
            $d->type = $type === 'seo' ? 'SEO' : ucfirst($d->type ?? '');
            $d->name = "<img src='{$image}' alt='Logo' height='50' width='50' style='border-radius:10px;'> {$d->name}";
            $statusRoute = null;

            if ($isChild) {
                $editRoute = route('admin.feature.edit', $d->id);
                $deleteRoute = route('admin.delete.child_feature', $d->id);
                $statusRoute = route('admin.update.child_feature', $d->id);
            } else {
                $editRoute = route('admin.feature_type.edit', $d->id);
                $deleteRoute = route('admin.delete.parent_feature', $d->id);
            }

            $status = ($d->active == 1) ? 'checked' : '';
            $d->parent_feature = $isChild ? $d->feature_type->name : (method_exists($d, 'features') ? $d->features->count() : 0);
            $id = $d->id;
            $d->related_parks = $d->parks_count;
            $d->action = View::make('components.admin.actioncomponent', compact('editRoute', 'deleteRoute', 'statusRoute', 'status', 'id'))->render();
        }

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data,
        ]);
    }
}
