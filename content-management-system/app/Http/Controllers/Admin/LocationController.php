<?php

namespace App\Http\Controllers\Admin;

use App\Models\Location;
use App\Http\Controllers\Controller;
use App\Services\RevalidateApiService;
use Illuminate\Http\Request;
use App\Models\Media;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    protected $active_page = "locations";

    protected $revalidateApi;

    public function __construct(RevalidateApiService $revalidateApi)
    {
        $this->revalidateApi = $revalidateApi;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $_order = request('order');
            $_columns = request('columns');
            $order_by = $_columns[$_order[0]['column']]['name'];
            $order_dir = $_order[0]['dir'];
            $search = request('search');
            $skip = request('start');
            $take = request('length');
            $greater_than_ten_parks = 0;
            $showAction = 1;

            $query = Location::select(
                'id',
                'city',
                'state',
                'country',
                'status',
                'banner_id',
                'thumbnail_id',
                'title',
                'subtitle'
            )
                ->selectSub(function ($subque) {
                    $subque->from('parks')
                        ->selectRaw('count(*)')
                        ->whereColumn('parks.city', 'locations.city')
                        ->whereColumn('parks.state', 'locations.state')
                        ->whereColumn('parks.country', 'locations.country');
                }, 'parks_count')
                // ->groupBy(
                //     'locations.id',
                //     'locations.city',
                //     'locations.state',
                //     'locations.country',
                //     'locations.status',
                //     'locations.banner_id',
                //     'locations.thumbnail_id',
                //     'locations.title',
                //     'locations.subtitle'
                // );
                // ->distinct()
            ;

            // Clone query for recordsTotal
            // $queryRecord = clone $query;
            $recordsTotal = $query->havingRaw('parks_count > 0')->count();

            // Handle search
            if (!empty($search['value'])) {
                $query->where(function ($q) use ($search) {
                    $q->where('city', 'LIKE', '%' . $search['value'] . '%')
                        ->orWhere('city_slug', 'LIKE', '%' . $search['value'] . '%')
                        ->orWhere('state', 'LIKE', '%' . $search['value'] . '%')
                        ->orWhere('state_slug', 'LIKE', '%' . $search['value'] . '%')
                        ->orWhere('country', 'LIKE', '%' . $search['value'] . '%')
                        ->orWhere('country_short_name', 'LIKE', '%' . $search['value'] . '%');
                });
            }

            // Initialize flags
            $greater_than_ten_parks = 0;
            $showAction = 1;

            // Apply filters
            if (!empty($request->filterVal)) {
                $val = $request->filterVal;

                $filters = [
                    'active_location' => fn($q) => $q->where('status', 1),
                    'inactive_location' => fn($q) => $q->where('status', 0),
                    'with_description' => fn($q) => $q->whereNotNull('subtitle'),
                    'with_out_description' => fn($q) => $q->whereNull('subtitle'),
                    'greater_than_ten_parks' => function ($q) use (&$greater_than_ten_parks, &$showAction) {
                        $greater_than_ten_parks = 1;
                        $showAction = 0;
                        return $q->having('parks_count', '>', 10);
                    },
                    'less_then_ten_parks' => function ($q) use (&$showAction) {
                        $showAction = 0;
                        return $q->havingRaw('parks_count <= 10 AND parks_count > 0');
                    },
                ];

                // Default fallback
                $defaultFilter = fn($q) => $q->havingRaw('parks_count > 0');

                // if ($val === 'active_location') {
                //     $query->where('status', 1);
                // } elseif ($val === 'inactive_location') {
                //     $query->where('status', 0);
                // }

                // if ($val === 'with_description') {
                //     $query->whereNotNull('subtitle');
                // } elseif ($val === 'with_out_description') {
                //     $query->whereNull('subtitle');
                // }

                // if ($val === 'greater_than_ten_parks') {
                //     $query->having('parks_count', '>', 10);
                //     $greater_than_ten_parks = 1;
                //     $showAction = 0;
                // } elseif ($val === 'less_then_ten_parks') {
                //     $query->havingRaw('parks_count <= 10 AND parks_count > 0');
                //     $showAction = 0;
                // } else {
                //     $query->havingRaw('parks_count > 0');
                // }

                // Apply filter
                ($filters[$val] ?? $defaultFilter)($query);
            } else {
                $query->havingRaw('parks_count > 0');
            }

            // Count filtered records
            $recordsFiltered = $query->count();

            // Get paginated results
            $dataTable = $query->orderBy($order_by, $order_dir)
                ->skip($skip)
                ->take($take)
                ->get();

            // Format results
            foreach ($dataTable as &$d) {
                $data = []; // Reset per row

                $data['id'] = $id = $d->id;
                $data['status'] = $d->status == 1 ? 'checked' : '';

                if ($greater_than_ten_parks) {
                    $data['seoDescriptionBtn'] = route('admin.locations.get.seo', $id);
                }

                $d->city = ucwords($d->city);
                $d->state = ucwords($d->state);
                $d->country = ucwords($d->country);

                if ($showAction) {
                    $data['editRoute'] = route('admin.locations.edit', $id);
                    $data['locationStatus'] = isset($d->title, $d->subtitle, $d->thumbnail_id, $d->banner_id);
                    $data['statusRouteUrl'] = route('admin.locations.status', [$id]);
                }

                $d->action = view('components.admin.actioncomponent', $data)->render();
            }

            return response()->json([
                "draw" => request('draw'),
                "recordsTotal" => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                "data" => $dataTable,
            ]);
        } else {
            $user = $data['user'] = $request->user();
            // if (!$user->can('location-show')) {
            //     abort(404);
            // }
            $data['active_page'] = $this->active_page;
            $data['page_title'] = "Locations";
            $data['custom_headings'] = "Location";
            return response()->view('admin.locations.index', $data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Location $location)
    {
        $user = $request->user();
        // if (!$user->can('locations-edit')) {
        //     abort(404);
        // }
        $string = "Edit Location";
        $data['active_page'] = $this->active_page;
        $data['page_title'] = "Edit Location";
        $data['custom_headings'] = $string;
        $data['location'] = $location;

        $token = session()->get('_token');
        $cacheKey = 'selected_park_ids' . $token;

        Cache::put($cacheKey, []);
        return response()->view('admin.locations.edit', $data);
    }

    public function seo(Request $request, Location $location)
    {
        $user = $request->user();
        // if (!$user->can('locations-seo')) {
        //     abort(404);
        // }
        $string = "SEO Location";
        $data['active_page'] = $this->active_page;
        $data['page_title'] = "SEO Location";
        $data['custom_headings'] = $string;
        $data['location'] = $location;
        $token = session()->get('_token');
        $cacheKey = 'selected_park_ids' . $token;

        Cache::put($cacheKey, []);
        return response()->view('admin.locations.seo', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Request  $request
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Location $location)
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:2000'],
        ];

        // Check if it's an edit or create (based on presence of ID or route)
        $isEdit = $location->banner_id ?? false;

        // Add conditional image rules
        $imageRules = ['image', 'mimes:png,jpg', 'max:10240'];

        $rules['banner'] = $isEdit ? array_merge(['nullable'], $imageRules) : array_merge(['required'], $imageRules);
        $rules['thumbnail'] = $isEdit ? array_merge(['nullable'], $imageRules) : array_merge(['required'], $imageRules);

        $messages = [
            'title.required' => 'Banner title field is required',
            'banner.required' => 'Banner image is required',
            'thumbnail.required' => 'Carousel image is required',
            'content.required' => 'The description field is required.',
            'content.string' => 'The description must be a valid string.',
            'content.max' => 'The description must not exceed 2000 characters.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $validator->sometimes('priority', 'required|min:1|max:65535', function () use ($request) {
            return $request->type == 'popular';
        });

        if ($validator->fails()) {
            return back()
                ->with('error', $validator->errors()->first())
                ->withInput($request->all())
                ->withErrors($validator->errors()->first());
        }

        $old_banner_media_to_delete = null;
        $old_thumbnail_media_to_delete = null;

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            $imageBanner = Media::save_media(file: $request->file('banner'), dir: 'location', tags: ['location banner image'], store_as: 'image');
            $old_banner_media_to_delete = $location->banner;
            $location->banner_id = $imageBanner->id;
        }

        if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
            $thumbnailImage = Media::save_media(file: $request->file('thumbnail'), dir: 'location', tags: ['location thumbnail image'], store_as: 'image');
            $old_thumbnail_media_to_delete = $location->thumbnail;
            $location->thumbnail_id = $thumbnailImage->id;
        }

        if ($old_banner_media_to_delete) {
            $old_banner_media_to_delete->forceDelete();
        }

        if ($old_thumbnail_media_to_delete) {
            $old_thumbnail_media_to_delete->forceDelete();
        }

        // if ($request->location_longitude) {
        //     $location->location_longitude = $request->location_longitude;
        // }

        // if ($request->location_latitude) {
        //     $location->location_latitude = $request->location_latitude;
        // }

        $location->title = $request->title;
        $location->subtitle = $request->content;
        $location->save();
        $message = __('admin.location_update');

        $this->revalidateApi->revalidateLocation($location);

        return redirect()->route('admin.locations.edit', [$location->id])->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function destroy(Location $location)
    {
        //
    }

    public function update_status(Request $request, Location $location)
    {
        $user = $request->user();
        // if (!$user->can('park-active')) {
        //     abort(404);
        // }
        if ($request->ajax()) {
            $location_status = $location->update(['status' => $request->status]);
            $msg = 'Location has been activated';
            if ($request->status == 0) {
                $msg = "Location has been inactivated.";
            }

            $this->revalidateApi->revalidateLocation($location);

            if ($location_status) {
                return response()->json([
                    'msg' => $msg
                ]);
            }
        };
    }

    public function reset_image(Request $request, Location $location, $type)
    {
        if ($request->ajax()) {
            $delete = $location?->$type?->delete();
            if ($delete) {
                $update = $location->update([$type . '_id' => null]);
            }
            $this->revalidateApi->revalidateLocation($location);
            return response()->json(['status' => $update ?? $delete]);
        }
    }

    public function update_default_container(Request $request, Location $location)
    {
        $container_id = $request->input('container_id');
        $location->update(['default_container_id' => $container_id]);
        $this->revalidateApi->revalidateLocation($location);
        return response()->json([
            'msg' => 'Location default feature successfully updated'
        ]);
    }

    protected function stateLatLng($state)
    {
        $accessToken = config('services.MAP_BOX_ACCESS_TOKEN');
        $url = $url = "https://api.mapbox.com/geocoding/v5/mapbox.places/" . urlencode($state) . ".json";

        $response = Http::get($url, [
            'access_token' => $accessToken
        ]);

        $data = $response->json();
        if (!empty($data['features'])) {
            $coordinates = $data['features'][0]['center'];
            $longitude = $coordinates[0];
            $latitude = $coordinates[1];

            return response()->json([
                'state' => $state,
                'latitude' => $latitude,
                'longitude' => $longitude
            ]);
        } else {
            return response()->json([
                'error' => 'State not found.'
            ], 404);
        }
    }

    public function uploadLatLng(Request $request)
    {
        $locations = Location::get();
        foreach ($locations as $location) {
            $data = $this->stateLatLng($location->city);
            $location->update([
                'location_latitude' => $data->original['latitude'],
                'location_longitude' => $data->original['longitude'],
            ]);
        }
    }

    public function update_seo(Request $request, Location $location)
    {
        $rules = [
            'seo_description' => ['required', 'string'],
        ];

        $messages = [
            'seo_description.required' => 'Content for SEO field is required',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $validator->sometimes('priority', 'required|min:1|max:65535', function () use ($request) {
            return $request->type == 'popular';
        });

        if ($location->seo_description !== $request->seo_description) {
            $location->update([
                'seo_description' => $request->seo_description,
            ]);
        }

        $location->refresh();
        $this->revalidateApi->revalidateLocation($location);
        return redirect()->route('admin.locations.get.seo', [
            $location->id
        ])
            ->with('success', "SEO Content has been updated.");
    }
}
