<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LocationCollection;
use App\Http\Resources\LocationResource;
use App\Http\Resources\RatingResource;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Helpers\YResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = request('search');
        $query = Location::with([
            'containers:id,name,title,description',
        ])->whereHas('containers')
            ->select('id', 'city', 'city_slug', 'state', 'state_slug', 'country', 'country_short_name', 'title', 'subtitle', 'thumbnail_id', 'banner_id', 'status', 'default_container_id', 'location_latitude', 'location_longitude', 'seo_description')
            ->whereStatus(1);

        if (isset($search)) {
            $query->Where(function ($q) use ($search) {
                $q->where('city', 'LIKE', '%' . $search . '%')
                    ->orWhere('state', 'LIKE', '%' . $search . '%')
                    ->orWhere('country', 'LIKE', '%' . $search . '%')
                ;
            });
        }

        $locationData = new LocationCollection($query->paginate($request->get('per_page', 15))->withQueryString());
        return YResponse::json(data: ["locations" => $locationData->response()->getData()]);
    }

    public function show($id)
    {
        $query = Location::with([
            'containers',
            'containers.parks',
        ])
            ->whereStatus(1)
            ->find($id);

        $location = new LocationResource($query);
        return YResponse::json(data: ['location' => $location]);
    }

    public function location_reviews(Request $request, Location $location)
    {
        $location->load('containers.parks.ratings');

        $ratings = $location->containers
            ->flatMap(function ($container) {
                return $container->parks->flatMap(function ($park) {
                    return $park->ratings->where('is_verified', 1);
                });
            });

        $perPage = $request->get('per_page', 15);
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pagedData = $ratings->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginated = new LengthAwarePaginator($pagedData, $ratings->count(), $perPage, $currentPage, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);

        return YResponse::json(data: [
            "parks" => RatingResource::collection($paginated)->response()->getData()
        ]);
    }

    public function showBySlug(Request $request)
    {
        $country = $request->country ?? null;
        $state = $request->state ?? null;
        $city = $request->city ?? null;

        $query = Location::with([
            'containers',
            'containers.parks',
            'containers.feature',
            'default_container.feature',
            'containers.parks.park_images.media',
            'containers.parks.ratings',
            'containers.parks.features',
            'containers.parks.park_availability',
            'containers.parks.bookmark',
        ])
            ->whereStatus(1)
            ->when($country, function ($q) use ($country) {
                $q->where('country', 'like', "%$country%")
                    ->orWhere('country_short_name', $country);
            })->when($state, function ($q) use ($state) {
                $q->where('state', 'like', "%$state%")
                    ->orWhere('state_slug', $state);
            })->when($city, function ($q) use ($city) {
                $q->where('city', 'like', "%$city%")
                    ->orWhere('city_slug', $city);
            })
            ->first();

        if (!$query) {
            return YResponse::json('Data not found!', [], 404);
        }

        $location = new LocationResource($query);
        return YResponse::json(data: ['location' => $location]);
    }
}
