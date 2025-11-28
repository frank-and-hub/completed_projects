<?php

namespace App\Http\Controllers\Admin;

use App\Models\Container;
use App\Models\ContainerFeature;
use App\Models\Feature;
use App\Models\Location;
use App\Http\Controllers\Controller;
use App\Models\FeatureType;
use App\Models\ParkImage;
use App\Models\Parks;
use App\Models\Media;
use App\Services\RevalidateApiService;
use App\Traits\CommonTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ContainerController extends Controller
{

    use CommonTraits;

    protected $active_page = "locations";
    protected $revalidateApi;

    public function __construct(RevalidateApiService $revalidateApi)
    {
        $this->revalidateApi = $revalidateApi;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, Location $location)
    {
        $data['user'] = $user = $data['user'] = $request->user();
        // if (!$user->can('location-show')) {
        //     abort(404);
        // }
        $data['custom_headings'] = "Add Container page for $location->city";
        $data['active_page'] = $this->active_page;
        $data['page_title'] = "Add Container";
        $data['custom_headings'] = "Add Container page for $location->city";
        $data['location'] = $location;
        $data['features'] = Feature::pluck('id', 'name');
        $data['featuresType'] = FeatureType::pluck('id', 'name')->toArray();
        $data['disabledfeatures'] = ContainerFeature::whereHas('container.location', fn($q) => $q->whereCity($location->city))->with('feature')->get()->pluck('feature.name')->unique()->values()->toArray();
        $data['topFeatureList'] = $this->topFeatureList(true);

        return response()->view('admin.container.edit', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Location $location)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string', 'max:255'],
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'max:2000'],
                'image' => ['required', 'image', 'mimes:png,jpg', 'max:2048'], // 2mb
                'selected_parks' => 'required',
                'feature_id' => 'required',
                'feature_type' => 'required|in:parent,child', // Assuming 'parent' and 'child' are valid types
            ],
            [
                'selected_parks.required' => 'Please select at least one park in a container.',
                'feature_id.required' => 'Please select a corresponding feature for the container.',
            ]
        );

        try {
            DB::beginTransaction();

            if ($request->selected_parks) {
                $selected_parks = explode(',', $request->selected_parks);
            }

            $validator->sometimes('priority', 'required|min:1|max:65535', function () use ($request) {
                return $request->type == 'popular';
            });

            if ($validator->fails()) {
                return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
            }

            $featureParksCount = Feature::withCount('parks')->find($request->feature_id);
            if ($featureParksCount->parks_count < 5) {
                return back()->with('error', __('errors.seo_feature_error'));
            }

            // $containerFeature = ContainerFeature::where('feature_id', $featureParksCount->id)->exists();
            // if($containerFeature){
            //      return back()->with('error', "Please select features which not in other container corresponding feature");
            // }

            $token = session()->get('_token');
            $cacheKey = 'selected_park_ids' . $token;

            Cache::put($cacheKey, []);

            $old_media_to_delete = null;
            $container = new Container;
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $image = Media::save_media(file: $request->file('image'), dir: 'container', tags: ['container image'], store_as: 'container_image');
                $old_media_to_delete = $container->image;
                $container->image_id = $image->id;
            }

            if ($old_media_to_delete) {
                $old_media_to_delete->forceDelete();
            }

            $container->name = $request->name;
            $container->title = $request->title;
            $container->description = $request->description;
            $container->location_id = $location->id;
            $container->save();

            if ($request->feature_id) {
                $featureId = $request->feature_id;
                $container->feature()->sync([]);
                $container->feature_type()->sync([]);

                if ($request->feature_type === 'parent') {
                    $container->feature_type()->sync($featureId);
                } else {
                    $container->feature()->sync($featureId);
                }
            }

            if (!$location->default_container_id) {
                $location->default_container_id = $container->id;
                $location->save();
            }

            if (isset($selected_parks) && count($selected_parks) > 0) {
                $container->parks()->sync($selected_parks);
            }

            $this->revalidateApi->revalidateLocation($location);

            $message = __('admin.container_create');
            DB::commit();
            return redirect()->route('admin.locations.edit', [$location->id])->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Container  $container
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Location $location, Container $container)
    {
        $user = $data['user'] = $request->user();
        // if (!$user->can('location-show')) {
        //     abort(404);
        // }
        $data['active_page'] = $this->active_page;
        $data['page_title'] = "Edit Container";
        $data['custom_headings'] = "Edit Container page for $location->city";
        $data['location'] = $location;
        $data['container'] = $container;
        $data['features'] = Feature::pluck('id', 'name')->toArray();
        $data['featuresType'] = FeatureType::pluck('id', 'name')->toArray();
        $selectedFeature = $container?->feature()?->first();
        $disabledfeatures = ContainerFeature::whereHas('container.location', fn($q) => $q->whereCity($location->city))->with('feature')->get()->pluck('feature.name')->unique()->values()->toArray();
        $data['disabledfeatures'] = $selectedFeature ? array_filter($disabledfeatures, fn($value) => $value !== $selectedFeature->name) : [];
        $data['topFeatureList'] = $this->topFeatureList(true);
        return response()->view('admin.container.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Container  $container
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Location $location, Container $container)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string', 'max:255'],
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'max:2000'],
                'image' => ['image', 'mimes:png,jpg', 'max:2048'],
                'selected_parks' => 'required',
                'feature_id' => 'required',
                'feature_type' => 'required|in:parent,child', // Assuming 'parent' and 'child' are valid types
            ],
            [
                'selected_parks.required' => 'Please select at least one park in a container.',
                'feature_id.required' => 'Please select a corresponding feature for the container.',
            ]
        );

        if ($request->selected_parks) {
            $selected_parks = explode(',', $request->selected_parks);
        }

        $validator->sometimes('priority', 'required|min:1|max:65535', function () use ($request) {
            return $request->type == 'popular';
        });

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
        }

        try {
            DB::beginTransaction();


            $featureParksCount = Feature::withCount('parks')->find($request->feature_id);
            if ($featureParksCount->parks_count < 5) {
                return back()->with('error', __('errors.seo_feature_error'));
            }

            $token = session()->get('_token');
            $cacheKey = 'selected_park_ids' . $token;

            Cache::put($cacheKey, []);

            $old_media_to_delete = null;
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $image = Media::save_media(file: $request->file('image'), dir: 'container', tags: ['container image'], store_as: 'container_image');
                $old_media_to_delete = $container->image;
                $container->image_id = $image->id;
            }

            if ($old_media_to_delete) {
                $old_media_to_delete->forceDelete();
            }

            $container->name = $request->name;
            $container->title = $request->title;
            $container->description = $request->description;
            $container->location_id = $location->id;
            $container->save();

            if ($request->feature_id) {
                $container->feature()->sync([]);
                $container->feature_type()->sync([]);

                if ($request->feature_type === 'parent') {
                    $container->feature_type()->sync($request->feature_id);
                } else {
                    $container->feature()->sync($request->feature_id);
                }
            }

            if (isset($selected_parks) && count($selected_parks) > 0) {
                $container->parks()->sync($selected_parks);
            }

            $message = __('admin.container_update');

            $this->revalidateApi->revalidateLocation($location);

            DB::commit();
            return redirect()
                ->route('admin.locations.edit', [
                    $location->id
                ])
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    public function dt_park_list(Request $request, Location $location, Container $container)
    {
        $feature_id = $request->feature_id ?? null;
        $_order = request('order');
        $_columns = request('columns');
        $order_by = $_columns[$_order[0]['column']]['name'];
        $order_dir = $_order[0]['dir'];
        $search = request('search');
        $skip = request('start');
        $take = request('length');
        $user = $request->user();

        $park_id = $request?->id;
        $type = $request->type;

        $token = session()->get('_token');
        $cacheKey = 'selected_park_ids' . $token;

        $parkIds = Cache::get($cacheKey, []);

        if (!in_array($park_id, $parkIds)) {
            if ($park_id) {
                $parkIds[] = (int) $park_id;
            }
            Cache::put($cacheKey, $parkIds);
        } elseif ($type == 'remove' && in_array($park_id, $parkIds)) {
            $parkIds = array_values(array_filter($parkIds, fn($id) => $id != $park_id));
            Cache::put($cacheKey, $parkIds);
        }

        $alreadyAssignedParksId = $container?->parks_id;
        $query = Parks::whereCity($location->city)->whereState($location->state)->whereCountry($location->country);

        // if ($feature_id) {
        //     $query->whereHas('features', fn($q) => $q->where('feature_id', $feature_id));
        // }

        if ($feature_id) {
            $query->where(function ($que) use ($feature_id) {
                $que->whereHas('features', fn($q) => $q->where('feature_id', $feature_id))
                    ->orWhereHas('featuresType', fn($q) => $q->where('feature_id', $feature_id));
            });
        }

        $newArrayOfIds = !empty($alreadyAssignedParksId) ? array_merge($parkIds, $alreadyAssignedParksId) : $parkIds;

        // foreach ($parkIds as $i) {
        //     $query->where('id', '!=', $i);
        // }

        if ($user->hasRole('subadmin')) {
            $query = $query->where('created_by_id', $user->id);
        }

        $recordsTotal = $query->count();

        if (isset($search['value'])) {
            $query->Where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search['value'] . '%')
                    ->orWhere('address', 'like', '%' . $search['value'] . '%');
            });
        }

        $recordsFiltered = $query->count();

        $data = $query
            ->orderBy($order_by, $order_dir)
            ->skip($skip)
            ->take($take)
            ->get();

        foreach ($data as &$d) {
            $id = $d->id;
            $additionSubtractionButton = in_array($id, $newArrayOfIds) ? 'disable' : $id;

            $detailsRoute = route('admin.park.details', $id);
            $park_image_1 = $d->park_images()->where('set_as_banner', '1')->first();
            $park_images = ParkImage::where('park_id', $id)->where('status', '1')->get();
            $image = $park_image_1 ? $park_image_1->media->full_path : asset('images/default.jpg');

            $d->name = "<img data-id='$id' data-id='$id' src=' $image' alt='Logo' height='50px' width='50px' style='border-radius: 10px;'> <a class='text-reset' style='text-decoration:none;' href='$detailsRoute' rel='tooltip' title='Go To Details'>" . ucfirst($d->name) . "</a>";

            $d->city = $d->city ?? 'N/A';
            $d->action = View::make('components.admin.actioncomponent', compact('additionSubtractionButton'))->render();
        }

        return [
            "draw" => request('draw'),
            "recordsTotal" => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            "data" => $data,
        ];
    }

    public function selected_park_list(Request $request, Location $location, Container $container)
    {
        $feature_id = $request->feature_id ?? null;
        $_order = request('order');
        $_columns = request('columns');
        $order_by = $_columns[$_order[0]['column']]['name'];
        $order_dir = $_order[0]['dir'];
        $search = request('search');
        $skip = request('start');
        $take = 10;
        $user = $request->user();

        $park_id = $request->id;
        $type = $request->type;

        $token = session()->get('_token');
        $cacheKey = 'selected_park_ids' . $token;

        if (!$park_id && $container->id) {
            $ids = $container->parks()->pluck('park_id')->toArray() ?? [];
            Cache::put($cacheKey, $ids);
        }

        $parkIds = Cache::get($cacheKey, []);

        if ($type == 'add' && !in_array($park_id, $parkIds)) {
            if ($park_id) {
                $parkIds[] = (int) $park_id;
            }
            Cache::put($cacheKey, $parkIds);
        } elseif ($type == 'remove' && in_array($park_id, $parkIds)) {
            $parkIds = array_values(array_filter($parkIds, fn($id) => $id != $park_id));
            Cache::put($cacheKey, $parkIds);
        }

        $query = Parks::whereCity($location->city)->whereState($location->state)->whereCountry($location->country)->whereIn('id', $parkIds);

        if ($feature_id) {
            $query->where(function ($que) use ($feature_id) {
                $que->whereHas('features', fn($q) => $q->where('feature_id', $feature_id))
                    ->orWhereHas('featuresType', fn($q) => $q->where('feature_id', $feature_id));
            });
        }

        if ($user->hasRole('subadmin')) {
            $query = $query->where('created_by_id', $user->id);
        }

        $recordsTotal = $query->count();

        if (isset($search['value'])) {
            $query->Where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search['value'] . '%')
                    ->orWhere('address', 'like', '%' . $search['value'] . '%');
            });
        }

        $recordsFiltered = $query->count();
        $dataTable = $query->orderBy($order_by, $order_dir)
            ->skip($skip)
            ->take($take)
            ->get();

        foreach ($dataTable as &$d) {
            $id = $subtractionAdditionButton = $d->id;
            $detailsRoute = route('admin.park.details', $id);
            $park_image_1 = $d->park_images()->where('set_as_banner', '1')->first();
            $park_images = ParkImage::where('park_id', $id)->where('status', '1')->get();
            $image = $park_image_1 ? $park_image_1->media->full_path : asset('images/default.jpg');

            $d->name = "<img data-id='$id' src=' $image' alt='Logo' height='50px' width='50px' style='border-radius: 10px;'> <a class='text-reset' style='text-decoration:none;' href='$detailsRoute' rel='tooltip' title='Go To Details'>" . ucfirst($d->name) . "</a>";
            $d->city = $d->city ?? 'N/A';

            $d->action = View::make('components.admin.actioncomponent', compact('subtractionAdditionButton'))->render();
        }

        return [
            "draw" => request('draw'),
            "recordsTotal" => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            "data" => $dataTable,
        ];
    }

    public function reset_image(Request $request, Location $location, Container $container)
    {
        if ($request->ajax()) {
            $container->image->delete();
            $update = $container->update(['image_id' => null]);
            $this->revalidateApi->revalidateLocation($location);
            return response()->json(['status' => $update]);
        }
    }

    public function destroy(Request $request, Location $location, Container $container)
    {
        if ($request->ajax()) {
            $container->delete();
            $this->revalidateApi->revalidateLocation($location);
            return response()->json(['status' => true, 'message' =>'Container deleted successfully', 'redirect_url' => route('admin.locations.edit', [$location->id])]);
        }
        return response()->json(['status' => false, 'message' => 'Container deletion failed']);
    }
}
