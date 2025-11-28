<?php

namespace App\Http\Controllers\Api;

use App\Helpers\YResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CategoryShortInfoCollection;
use App\Http\Resources\CategoryShortInfoResource;
use App\Http\Resources\FeatureResource;
use App\Http\Resources\FeatureTypeDetailCollection;
use App\Http\Resources\RatingResource;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\FeatureTypeResource;
use App\Http\Resources\LocationShortInfoResource;
use App\Http\Resources\MetaData\ParkShortDataResource;
use App\Http\Resources\ParksByCategoriesResource;
use App\Http\Resources\ParkShortInfoCollection;
use App\Http\Resources\ParkShortInfoResource;
use App\Http\Resources\SubcategoryCollection;
use App\Http\Resources\SubcategoryResource;
use App\Models\Category;
use App\Models\CustomPage;
use App\Models\Feature;
use App\Models\FeatureType;
use App\Models\Location;
use App\Models\ParkFeature;
use App\Models\Parks;
use App\Models\Subcategory;
use App\Traits\CommonTraits;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DataController extends Controller
{
    use CommonTraits;
    public function home(Request $request)
    {
        $userLat = $request->input('latitude');
        $userLng = $request->input('longitude');

        $features = Feature::where('type', 'popular')->where('active', 1)->get();
        foreach ($features as $feature) {
            $feature->type = 'child-feature';
        }
        $features_types = FeatureType::where('type', 'popular')->where('active', 1)->get();
        foreach ($features_types as $feature) {
            $feature->type = 'parent-feature';
        }

        $features = $features->merge($features_types)->shuffle();
        $season = $this->get_season($request);
        $park_query = Parks::where('active', 1);
        $nearby_parks_query = null;
        if (!empty($request->header('latitude')) && !empty($request->header('longitude'))) {
            $nearby_parks_query = $park_query->select([
                'parks.*',
                DB::raw('ROUND((6371 * acos( cos( radians(' . $request->header('latitude') . ') ) * cos( radians(parks.latitude ) ) * cos( radians(parks.longitude ) - radians(' . $request->header('longitude') . ') ) + sin( radians(' . $request->header('latitude') . ') ) * sin( radians(parks.latitude ) ) ) ),2) as distance'),
                'city',
                'state',
            ])->where('parks.active', 1)
                ->having('distance', '<=', 20)
                ->orderBy('distance')
                ->limit(5)
                ->get();
        }
        $categories = Category::where('active', 1)->where('is_display_by_itself', false)->where('type', 'parent')->where('is_set_as_home', 1)->where(function ($query) use ($request) {
            $query->where('special_category', 0);
        })->limit(5)->get();

        $categories__ = Category::where('active', 1)->where('is_display_by_itself', false)->where('type', 'parent')->where('is_set_as_home', 1)->where(function ($query) use ($season) {
            $query->where('special_category', 1)->where('season', $season);
        })->orderBy('priority')->limit(2)->get();

        $park_by_categories = Category::where('active', 1)->where('is_display_by_itself', false)->where('type', 'no-child')->where('is_set_as_carousel', 1)->where(function ($query) use ($season) {
            $query->where('special_category', 0)->orWhere('season', $season);
        })->orderBy('priority')->limit(5)->get();

        $categories_p_1 = Category::where('active', 1)->where('is_display_by_itself', false)->where('is_set_as_home', 1)->where('type', 'no-child')->where(function ($query) use ($request, $season) {
            $query->where('special_category', 0)->orWhere('season', $season);
        })->has('parks')->orderBy('priority', 'asc')->first();

        if ($categories_p_1) {
            $categories_p = Category::where('active', 1)->where('is_display_by_itself', false)->where('is_set_as_home', 1)->where('type', 'no-child')->where(function ($query) use ($season) {
                $query->where('special_category', 0)->orWhere('season', $season);
            })->where('id', '!=', $categories_p_1->id)->has('parks')->limit(5)->orderBy('priority', 'asc')->get();
        }

        $data = [];

        $popular_features = [
            "title" => "Popular Features",
            "slug" => "popular",
            "type" => "features",
            'data' => FeatureResource::collection($features)
        ];

        $data[] = $popular_features;

        // if (!empty($request->header('latitude')) && !empty($request->header('longitude'))) {

        $nearby_parks = [
            "title" => "Nearby Parks",
            'slug' => 'nearby',
            "type" => "near_by_parks",
            'data' => $nearby_parks_query ? ParkShortInfoResource::collection($nearby_parks_query) : null,
        ];

        $data[] = $nearby_parks;

        $parent_display_itself_categories = Category::where('active', 1)->where('is_display_by_itself', true)->where('type', 'parent')->where(function ($query) use ($request) {
            $query->where('special_category', 0);
        })->orderBy('priority')->get();

        $parent_display_itself_categories__ = Category::where('active', 1)->where('is_display_by_itself', true)->where('type', 'parent')->where(function ($query) use ($season) {
            $query->where('special_category', 1)->where('season', $season);
        })->orderBy('priority')->get();

        if (count($parent_display_itself_categories) > 0 || count($parent_display_itself_categories__) > 0) {
            $categories_array = [
                "title" => "",
                "slug" => "parent",
                "type" => "parent-display-by-itself",
                'data' => CategoryShortInfoResource::collection($parent_display_itself_categories->merge($parent_display_itself_categories__))
            ];
            $data[] = $categories_array;
        }

        // }

        if ($categories_p_1) {
            $data[] = new ParksByCategoriesResource($categories_p_1);
        }

        $categories_array = [
            "title" => "",
            "type" => "parent",
            'data' => CategoryShortInfoResource::collection($categories->merge($categories__))
        ];

        $data[] = $categories_array;

        if ($categories_p_1) {
            foreach ($categories_p as $category) {
                $data[] = new ParksByCategoriesResource($category);
            }
        }

        $_categories_query = Category::where('active', 1)->where('is_set_as_carousel', 1)->where('type', 'parent')->has('subcategories')->where(function ($query) use ($request) {
            $query->where('special_category', 0);
        })->orderBy('priority', 'asc')->get();

        if (count($_categories_query) > 0) {
            foreach ($_categories_query as $cq) {
                $_categories = [
                    "id" => $cq->id,
                    "title" => $cq->name,
                    "slug" => $cq->slug,
                    "type" => "special",
                    "description" => $cq->description,
                    'data' => SubcategoryResource::collection($cq->subcategories()->where('active', 1)->limit(5)->get()),
                ];
                $data[] = $_categories;
            }
        }

        $_categories_query = Category::where('active', 1)->where('is_set_as_carousel', 1)->where('type', 'parent')->has('subcategories')->where(function ($query) use ($request) {
            $query->where('special_category', 1)->orWhere('season', $this->get_season($request));
        })->orderBy('priority', 'asc')->get();

        if (count($_categories_query) > 0) {
            foreach ($_categories_query as $cq) {
                $_categories = [
                    "id" => $cq->id,
                    "title" => $cq->name,
                    "slug" => $cq->slug,
                    "type" => "special",
                    "description" => $cq->description ?? null,
                    'data' => SubcategoryResource::collection($cq->subcategories()->where('active', 1)->limit(5)->get()),
                ];
                $data[] = $_categories;
            }
        }

        $standalone_display_itself_categories = Category::where('active', 1)->where('is_display_by_itself', true)->where('type', 'no-child')->where(function ($query) use ($request) {
            $query->where('special_category', 0);
        })->orderBy('priority')->get();

        $standalone_display_itself_categories__ = Category::where('active', 1)->where('is_display_by_itself', true)->where('type', 'no-child')->where(function ($query) use ($season) {
            $query->where('special_category', 1)->where('season', $season);
        })->orderBy('priority')->get();

        if (count($standalone_display_itself_categories) > 0 || count($standalone_display_itself_categories__) > 0) {
            $categories_array = [
                "title" => "",
                "slug" => 'standalone',
                "type" => "standalone-display-by-itself",
                'data' => CategoryShortInfoResource::collection($standalone_display_itself_categories->merge($standalone_display_itself_categories__))
            ];

            $data[] = $categories_array;
        }

        if (count($park_by_categories) > 0) {
            $park_by_categories = [
                "title" => "More Categories",
                "slug" => "more-categories",
                "type" => "no-child",
                'data' => CategoryShortInfoResource::collection($park_by_categories),
            ];

            $data[] = $park_by_categories;
        }

        return YResponse::json(data: ['home' => $data]);
    }

    public function getSubcategories(Request $request, Category $category)
    {
        $subcategory = Subcategory::where('active', 1)->where('category_id', $category->id)->orderBy('name', 'asc');
        return YResponse::json(data: ["subcategory" => (new SubcategoryCollection($subcategory->paginate($request->get('per_page', 15))->withQueryString()))->response()->getData()]);
    }

    public function getSubcategoriesBySlug(Request $request, $slug)
    {
        $category = Category::where('active', 1)->whereSlug($slug)->first();
        $subcategory = Subcategory::where('active', 1)->where('category_id', $category->id)->orderBy('name', 'asc');
        return YResponse::json(data: ["subcategory" => (new SubcategoryCollection($subcategory->paginate($request->get('per_page', 15))->withQueryString()))->response()->getData()]);
    }

    public function getCategories(Request $request)
    {
        $request->validate([
            'type' => ['sometimes', 'in:parent,no-child,special']
        ]);
        $categories = Category::where('active', 1);
        if ($request->type) {
            $categories = $categories->where('type', $request->type);
        }
        if ($request->type == 'no-child') {
            $categories = $categories->whereNot('is_set_as_home')->where('is_set_as_carousel', true)->where(function ($query) use ($request) {
                $query->where('special_category', 0)->orWhere('season', $this->get_season($request));
            });
        }
        return YResponse::json(data: ["categories" => (new CategoryShortInfoCollection($categories->paginate($request->get('per_page', 15))->withQueryString()))->response()->getData()]);
    }

    public function getFeatures(Request $request)
    {
        $search = $this->normalizeSearchTerm($request->input('search'));
        $feature = FeatureType::where('active', 1)->with('features');

        if ($search) {
            $feature->where(function ($q) use ($search) {
                $q->whereHas('features', function ($q) use ($search) {
                    $q->where('slug', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })->orWhere('name', 'like', "%$search%");
            });
        }

        return YResponse::json(data: ["features" => (new FeatureTypeDetailCollection($feature->paginate($request->get('per_page', 15))->withQueryString()))->response()->getData()]);
    }

    public function getFeaturesMap(Request $request)
    {
        $features = Feature::where('type', 'popular')->where('active', 1)->get();
        foreach ($features as $feature) {
            $feature->type = 'sub-feature';
        }
        $features_types = FeatureType::where('type', 'popular')->where('active', 1)->get();
        foreach ($features_types as $feature) {
            $feature->type = 'feature';
        }

        $features = $features->merge($features_types)->shuffle();

        return YResponse::json(data: ["features" => FeatureResource::collection($features)]);
    }

    public function pages($slug)
    {
        try {
            $page = CustomPage::where('slug', $slug)->first();

            if (!$page) {
                return YResponse::json(__('api_message.page_not_found'), status: 404);
            }

            return YResponse::json(data: ['content' => $page->text]);
        } catch (\Exception $e) {
            // Log::error($e);
            return YResponse::json(__('api_message.default_error_message'), status: 500);
        }
    }

    public function get_season($request)
    {
        $current_date = Carbon::now()->setTimezone($request->header('timezone'))->format('Y-m-d H:i:s');

        $season = DB::select(
            "
                    SELECT *
                    FROM `seasons`
                    WHERE `hemisphere` = :hemisphere
                    AND (
                        (DATE_FORMAT(season_start_date, '%m-%d') <= DATE_FORMAT(:current_date1, '%m-%d')
                        AND DATE_FORMAT(season_end_date, '%m-%d') >= DATE_FORMAT(:current_date2, '%m-%d'))
                        OR
                        (DATE_FORMAT(season_start_date, '%m-%d') > DATE_FORMAT(season_end_date, '%m-%d')
                        AND (
                            DATE_FORMAT(:current_date3, '%m-%d') >= DATE_FORMAT(season_start_date, '%m-%d')
                            OR DATE_FORMAT(:current_date4, '%m-%d') <= DATE_FORMAT(season_end_date, '%m-%d')
                        ))
                    )
                    LIMIT 1",
            [
                'hemisphere' => $request->header('latitude') >= 0 ? 'north' : 'south',
                'current_date1' => $current_date,
                'current_date2' => $current_date,
                'current_date3' => $current_date,
                'current_date4' => $current_date,
            ]
        );

        if (empty($season)) {
            return '';
        } else {
            return $season[0]->season; // Assuming the `season` column exists
        }
    }

    public function location_search(Request $request)
    {
        $search = $request->input('search'); // or $request->search
        $types = $request->input('types');   // optional, example: 'geocode' or 'establishment'

        $url = 'https://maps.googleapis.com/maps/api/place/autocomplete/json';

        $query = [
            'input' => $search,
            'language' => 'en',
            'key' => config('services.place_api_key'),
        ];

        if ($types) {
            $query['types'] = $types;
        }

        $response = Http::get($url, $query);

        return $response->json();
    }

    public function place_search(Request $request)
    {
        $place = $request->input('place_id'); // or $request->search
        $types = $request->input('types');   // optional, example: 'geocode' or 'establishment'

        $url = 'https://maps.googleapis.com/maps/api/place/details/json';

        $query = [
            'place_id' => $place,
            'language' => 'en',
            'key' => config('services.place_api_key'),
        ];

        $response = Http::get($url, $query);

        return $response->json();
    }

    public function getCategoryDetails(Request $request, Category $category)
    {
        if (!$category->id) {
            return YResponse::json('Data not found!', status: 404);
        }

        return YResponse::json(data: new CategoryResource($category));
    }

    public function getDetailsBySlug(Request $request)
    {
        $slug = $request->slug ?? null;
        $type = $request->type ?? null;

        if (!$slug || !$type) {
            return YResponse::json('Slug and type are required.', status: 400);
        }

        $getActiveModel = function ($model, $slug) {
            return $model::where('active', 1)
                ->whereSlug($slug)
                ->first();
        };

        switch ($type) {
            case 'parent':

                if ($slug == 'nearby') {
                    return YResponse::json('Invalid Slug!', status: 404);
                }

                $category = $getActiveModel(Category::class, $slug);
                if ($category) {
                    $data = new CategoryShortInfoResource($category);
                    break;
                }

                $subcategory = $getActiveModel(Subcategory::class, $slug);
                if ($subcategory) {
                    $data = new SubcategoryResource($subcategory);
                    break;
                }

                $feature = $getActiveModel(Feature::class, $slug);
                if ($feature) {
                    $data = new FeatureResource($feature);
                    break;
                }

                $featureType = $getActiveModel(FeatureType::class, $slug);
                if ($featureType) {
                    $data = new FeatureTypeResource($featureType);
                    break;
                }

                return YResponse::json('Data not found!', status: 404);

            case 'child':

                if ($slug == 'nearby') {
                    return YResponse::json('Invalid Slug!', status: 404);
                }

                $subcategory = $getActiveModel(Subcategory::class, $slug);
                if ($subcategory) {
                    $data = new SubcategoryResource($subcategory);
                    break;
                }

                $category = $getActiveModel(Category::class, $slug);
                if ($category) {
                    $data = new CategoryShortInfoResource($category);
                    break;
                }

                $featureType = $getActiveModel(FeatureType::class, $slug);
                if ($featureType) {
                    $data = new FeatureTypeResource($featureType);
                    break;
                }

                $feature = $getActiveModel(Feature::class, $slug);
                if ($feature) {
                    $data = new FeatureResource($feature);
                    break;
                }

                return YResponse::json('Data not found!', status: 404);

            default:
                return YResponse::json('Invalid type. Must be "parent" or "child".', status: 400);
        }
        return YResponse::json(data: $data);
    }

    public function global_search(Request $request)
    {
        // Validate the search input
        $request->validate([
            'search' => ['sometimes', 'string', 'nullable'],
        ]);

        $longitude = $request->header('longitude') ?? 0;
        $latitude = $request->header('latitude') ?? 0;
        $radius = (int) $request->get('radius', 100);
        $featureLimit = (int) $request->get('feature_limit', 5);
        $search = $request->input('search');
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 15);
        $searchTerm = Str::slug($this->normalizeSearchTerm($search));
        $offset = ($page - 1) * $perPage;

        if (!$searchTerm) {
            return YResponse::json(data: [], message: 'search... not found!');
        }

        $searchWildcard = "%$searchTerm%";

        $cacheKey = "global_search:" . md5($searchTerm . $latitude . $longitude . $page . $perPage);
        $data = Cache::remember($cacheKey, 300, function () use ($searchWildcard, $latitude, $longitude, $radius, $featureLimit, $offset, $perPage) {
            // $results = collect();
            // without description match
            
            $query = "( SELECT DISTINCT 'features' AS db_name, id, name, slug, Null AS description, Null AS city, Null AS city_slug, Null AS state, Null AS state_slug, Null AS country, Null AS country_slug, Null AS title, Null AS subtitle, Null AS longitude, Null AS latitude, 0 AS distance from features WHERE slug LIKE ? OR name LIKE ? LIMIT ? ) UNION ALL ( SELECT DISTINCT 'locations' AS db_name, id, city AS name, city_slug AS slug, Null AS description, city, city_slug, state, state_slug, country, country_short_name AS country_slug, title, subtitle, location_longitude AS longitude, location_latitude AS latitude, ROUND((6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(location_latitude)) * COS(RADIANS(location_longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(location_latitude)))), 2 ) AS distance from locations WHERE ( city LIKE ? OR city_slug LIKE ? OR state LIKE ? OR state_slug LIKE ? OR country LIKE ? OR country_short_name LIKE ? OR title LIKE ? OR subtitle LIKE ? ) LIMIT ? ) UNION ALL ( SELECT DISTINCT 'feature_types' AS db_name, id, name, slug, Null AS description, Null AS city, Null AS city_slug, Null AS state, Null AS state_slug, Null AS country, Null AS country_slug, Null AS title, Null AS subtitle, Null AS longitude, Null latitude, 0 AS distance from feature_types WHERE slug LIKE ? OR name LIKE ? LIMIT ? ) UNION ALL ( SELECT DISTINCT 'parks' AS db_name, p.id AS id, p.name AS name, p.slug AS slug, p.description AS description, p.city AS city, p.city_slug AS city_slug, p.state AS state, p.state_slug AS state_slug, p.country AS country, p.country_short_name AS country_slug, NULL AS title, NULL AS subtitle, p.longitude AS longitude, p.latitude AS latitude, ROUND((6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(p.latitude)) * COS(RADIANS(p.longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(p.latitude)))), 2 ) AS distance FROM parks AS p JOIN park_features AS pf ON pf.park_id = p.id JOIN features AS f ON f.id = pf.feature_id JOIN feature_types AS ft ON ft.id = pf.feature_type_id WHERE ( p.slug LIKE ? OR p.name LIKE ? OR p.city LIKE ? OR p.city_slug LIKE ? OR p.state LIKE ? OR p.state_slug LIKE ? OR p.country LIKE ? OR p.country_short_name LIKE ? OR f.slug LIKE ? OR ft.slug LIKE ? ) Limit ? OFFSET ? ) ORDER BY db_name ASC, distance ASC, city ASC, state ASC, country ASC";

            $params = [
                $searchWildcard,
                $searchWildcard,
                $featureLimit, // features
                $latitude,
                $longitude,
                $latitude,
                $searchWildcard,
                $searchWildcard,
                $searchWildcard,
                $searchWildcard,
                $searchWildcard,
                $searchWildcard,
                $searchWildcard,
                $searchWildcard,
                $featureLimit,
                $searchWildcard,
                $searchWildcard,
                $featureLimit, // feature_types
                $latitude,
                $longitude,
                $latitude, // parks (lat/lng for distance)
                $searchWildcard,
                $searchWildcard,
                $searchWildcard,
                $searchWildcard, // parks search
                $searchWildcard,
                $searchWildcard,
                $searchWildcard,
                $searchWildcard,
                $searchWildcard,
                $searchWildcard,
                $perPage,
                $offset
            ];

            return DB::select($query, $params);
        });

        $total = count($data);
        $pagedData = array_slice($data, $offset, $perPage);

        $paginated = new LengthAwarePaginator(
            items: $pagedData,
            total: $total,
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );
        return YResponse::json(data: $paginated);
    }

    public function global_search_old(Request $request)
    {
        // Validate the search input
        $request->validate([
            'search' => ['sometimes', 'string', 'nullable'],
        ]);

        $longitude = $request->header('longitude');
        $latitude = $request->header('latitude');
        $radius = (int) $request->get('radius', 100);
        $featureLimit = (int) $request->get('feature_limit', 5);
        $search = $request->input('search');
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 15);
        $searchTerm = Str::slug($this->normalizeSearchTerm($search));
        $offset = ($page - 1) * $perPage;

        if (!$searchTerm) {
            return YResponse::json(data: [], message: 'search... not found!');
        }

        $searchWildcard = "%$searchTerm%";
        $results = collect();

        // without description match
        $query = "( SELECT DISTINCT 'features' AS db_name, id, name, slug, Null AS description, Null AS city, Null AS city_slug, Null AS state, Null AS state_slug, Null AS country, Null AS country_slug, Null AS title, Null AS subtitle, Null AS longitude, Null AS latitude, 0 AS distance from features WHERE slug LIKE ? OR name LIKE ? LIMIT ? ) UNION ALL ( SELECT DISTINCT 'locations' AS db_name, id, city AS name, city_slug AS slug, Null AS description, city, city_slug, state, state_slug, country, country_short_name AS country_slug, title, subtitle, location_longitude AS longitude, location_latitude AS latitude, ROUND((6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(location_latitude)) * COS(RADIANS(location_longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(location_latitude)))), 2 ) AS distance from locations WHERE ( city LIKE ? OR city_slug LIKE ? OR state LIKE ? OR state_slug LIKE ? OR country LIKE ? OR country_short_name LIKE ? OR title LIKE ? OR subtitle LIKE ? ) LIMIT ? ) UNION ALL ( SELECT DISTINCT 'feature_types' AS db_name, id, name, slug, Null AS description, Null AS city, Null AS city_slug, Null AS state, Null AS state_slug, Null AS country, Null AS country_slug, Null AS title, Null AS subtitle, Null AS longitude, Null latitude, 0 AS distance from feature_types WHERE slug LIKE ? OR name LIKE ? LIMIT ? ) UNION ALL ( SELECT DISTINCT 'parks' AS db_name, p.id AS id, p.name AS name, p.slug AS slug, p.description AS description, p.city AS city, p.city_slug AS city_slug, p.state AS state, p.state_slug AS state_slug, p.country AS country, p.country_short_name AS country_slug, NULL AS title, NULL AS subtitle, p.longitude AS longitude, p.latitude AS latitude, ROUND((6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(p.latitude)) * COS(RADIANS(p.longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(p.latitude)))), 2 ) AS distance FROM parks AS p JOIN park_features AS pf ON pf.park_id = p.id JOIN features AS f ON f.id = pf.feature_id JOIN feature_types AS ft ON ft.id = pf.feature_type_id WHERE ( p.slug LIKE ? OR p.name LIKE ? OR p.city LIKE ? OR p.city_slug LIKE ? OR p.state LIKE ? OR p.state_slug LIKE ? OR p.country LIKE ? OR p.country_short_name LIKE ? OR f.slug LIKE ? OR ft.slug LIKE ? ) Limit ? OFFSET ? ) ORDER BY db_name ASC, distance ASC, city ASC, state ASC, country ASC";

        // $substrCount = substr_count($query, "?");
        // $params = array_fill(0, $substrCount, "%$searchTerm%");
        // $data = DB::select($query, $params);


        // $params = [
        //     $latitude,
        //     $longitude,
        //     $latitude,
        // ];
        // $searchParamCount = substr_count($query, "?") - 3;
        // $params = array_merge($params, array_fill(0, $searchParamCount, $searchWildcard));

        $params = [
            $searchWildcard,
            $searchWildcard,
            $featureLimit, // features
            $latitude,
            $longitude,
            $latitude,
            $searchWildcard,
            $searchWildcard,
            $searchWildcard,
            $searchWildcard,
            $searchWildcard,
            $searchWildcard,
            $searchWildcard,
            $searchWildcard,
            $featureLimit,
            // $radius, // locations
            $searchWildcard,
            $searchWildcard,
            $featureLimit, // feature_types
            $latitude,
            $longitude,
            $latitude, // parks (lat/lng for distance)
            $searchWildcard,
            $searchWildcard,
            $searchWildcard,
            $searchWildcard, // parks search
            $searchWildcard,
            $searchWildcard,
            $searchWildcard,
            $searchWildcard,
            // $searchWildcard, // with decription
            $searchWildcard,
            $searchWildcard,
            // $radius // HAVING distance < ?
            $perPage,
            $offset
        ];

        $data = DB::select($query, $params);

        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);
        $offset = ($page - 1) * $perPage;

        $pagedData = array_slice($data, $offset, $perPage);

        $paginated = new LengthAwarePaginator(
            items: $pagedData,
            total: count($data),
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path' => $request->url(),
                'query' => $request->query()
            ]
        );
        return YResponse::json(data: $paginated);
    }

    public function global_search_try(Request $request)
    {
        // Validate input
        $request->validate([
            'search' => ['sometimes', 'string', 'nullable'],
        ]);

        $latitude = $request->header('latitude', 0);
        $longitude = $request->header('longitude', 0);
        $radius = $request->get('radius', 100);
        $featureLimit = $request->get('feature_limit', 5);
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $search = $request->input('search');
        $searchTerm = Str::slug($this->normalizeSearchTerm($search));

        if (!$searchTerm) {
            return YResponse::json(data: [], message: 'Search term not found!');
        }

        $searchWildcard = "%{$searchTerm}%";

        // Optional: Cache results
        $cacheKey = "global_search:" . md5($searchTerm . $latitude . $longitude . $page . $perPage);
        $data = Cache::remember($cacheKey, 300, function () use ($searchWildcard, $latitude, $longitude, $radius, $featureLimit, $offset, $perPage) {

            $results = collect();

            // Features
            $features = DB::table('features')
                ->selectRaw("'features' AS db_name, id, name, slug, NULL AS description, NULL AS city, NULL AS city_slug, NULL AS state, NULL AS state_slug, NULL AS country, NULL AS country_slug, NULL AS title, NULL AS subtitle, NULL AS longitude, NULL AS latitude, 0 AS distance")
                ->where('slug', 'LIKE', $searchWildcard)
                ->orWhere('name', 'LIKE', $searchWildcard)
                ->limit($featureLimit)
                ->get();

            // Locations
            $locations = DB::table('locations')
                ->selectRaw("'locations' AS db_name, id, city AS name, city_slug AS slug, NULL AS description, city, city_slug, state, state_slug, country, country_short_name AS country_slug, title, subtitle, location_longitude AS longitude, location_latitude AS latitude, ROUND(6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(location_latitude)) * COS(RADIANS(location_longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(location_latitude))), 2) AS distance", [$latitude, $longitude, $latitude])
                ->where(function ($query) use ($searchWildcard) {
                    $query->where('city', 'LIKE', $searchWildcard)
                        ->orWhere('city_slug', 'LIKE', $searchWildcard)
                        ->orWhere('state', 'LIKE', $searchWildcard)
                        ->orWhere('state_slug', 'LIKE', $searchWildcard)
                        ->orWhere('country', 'LIKE', $searchWildcard)
                        ->orWhere('country_short_name', 'LIKE', $searchWildcard)
                        ->orWhere('title', 'LIKE', $searchWildcard)
                        ->orWhere('subtitle', 'LIKE', $searchWildcard);
                })
                ->limit($featureLimit)
                ->get();

            // Feature Types
            $featureTypes = DB::table('feature_types')
                ->selectRaw("'feature_types' AS db_name, id, name, slug, NULL AS description, NULL AS city, NULL AS city_slug, NULL AS state, NULL AS state_slug, NULL AS country, NULL AS country_slug, NULL AS title, NULL AS subtitle, NULL AS longitude, NULL AS latitude, 0 AS distance")
                ->where('slug', 'LIKE', $searchWildcard)
                ->orWhere('name', 'LIKE', $searchWildcard)
                ->limit($featureLimit)
                ->get();

            // Parks
            $parks = DB::table('parks AS p')
                ->join('park_features AS pf', 'pf.park_id', '=', 'p.id')
                ->join('features AS f', 'f.id', '=', 'pf.feature_id')
                ->join('feature_types AS ft', 'ft.id', '=', 'pf.feature_type_id')
                ->selectRaw("'parks' AS db_name, p.id, p.name, p.slug, p.description, p.city, p.city_slug, p.state, p.state_slug, p.country, p.country_short_name AS country_slug, NULL AS title, NULL AS subtitle, p.longitude, p.latitude, ROUND(6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(p.latitude)) * COS(RADIANS(p.longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(p.latitude))), 2) AS distance", [$latitude, $longitude, $latitude])
                ->where(function ($query) use ($searchWildcard) {
                    $query->where('p.slug', 'LIKE', $searchWildcard)
                        ->orWhere('p.name', 'LIKE', $searchWildcard)
                        ->orWhere('p.city', 'LIKE', $searchWildcard)
                        ->orWhere('p.city_slug', 'LIKE', $searchWildcard)
                        ->orWhere('p.state', 'LIKE', $searchWildcard)
                        ->orWhere('p.state_slug', 'LIKE', $searchWildcard)
                        ->orWhere('p.country', 'LIKE', $searchWildcard)
                        ->orWhere('p.country_short_name', 'LIKE', $searchWildcard)
                        ->orWhere('f.slug', 'LIKE', $searchWildcard)
                        ->orWhere('ft.slug', 'LIKE', $searchWildcard);
                })
                ->limit($perPage)
                ->get();

            $results = $features
                ->merge($locations)
                ->merge($featureTypes)
                ->merge($parks)
                ->sortBy([
                    ['db_name', 'asc'],
                    ['distance', 'asc'],
                    ['city', 'asc'],
                    ['state', 'asc'],
                    ['country', 'asc'],
                ])
                ->values();

            return $results;
        });

        $total = $data->count();
        $pagedData = $data->slice($offset, $perPage)->values();

        $paginated = new LengthAwarePaginator(
            items: $pagedData,
            total: $total,
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );

        return YResponse::json(data: $paginated);
    }

    public function filterParksByRank($parks, &$filterRank)
    {
        $query = clone $parks;
        $parks = $query->with(['park_images', 'ratings'])->get();
        if ($query->park_images->count() > 0) {
            $filterRank = $filterRank + 5;
            return $query;
        }
        return $query;
    }

    public function metaDataValidation(Request $request)
    {
        $slug = $request->slug ?? null;
        $country = $request->country ?? null;
        $state = $request->state ?? null;
        $city = $request->city ?? null;
        $respponceData = [];
        if ($slug) {
            if (!in_array($slug, $this->metaFeatures(true))) {
                return YResponse::json('Data not found!', status: 404);
            }
            $featureType = FeatureType::where('active', 1)->whereSlug($slug)->first();

            if (!$featureType) {
                $feature = Feature::where('active', 1)->whereSlug($slug)->first();

                if (!$feature) {
                    return YResponse::json('Data not found!', status: 404);
                }
                $parentFeature = new FeatureResource($feature->feature_type);
                $respponceData['feature'] = $parentFeature;
            } else {
                $parentFeature = new FeatureResource($featureType);
                $respponceData['feature'] = $parentFeature;
            }
        }

        // Validate presence of metadata
        $query = Parks::query();

        if ($country) {
            $query->where(function ($q) use ($country) {
                $q->where('country', 'like', "%$country%")
                    ->orWhere('country_short_name', $country);
            });
        }

        if ($state) {
            $query->where(function ($subQuery) use ($state) {
                $subQuery->where('state', 'like', "%$state%")
                    ->orWhere('state_slug', $state);
            });
        }

        if ($city) {
            $query->where(function ($subQuery) use ($city) {
                $subQuery->where('city', 'like', "%$city%")
                    ->orWhere('city_slug', $city);
            });
        }

        $parkQuery = clone $query;
        $parkData = $parkQuery->first();

        $dataExists = $query->exists();

        if (!$dataExists) {
            return YResponse::json('Data not found!', status: 404);
        }

        if ($country) {
            $respponceData['country'] = [
                'name' => $parkData->country,
                'slug' => $parkData->country_short_name,
            ];
        }

        if ($state) {
            $respponceData['state'] = [
                'name' => $parkData->state,
            ];
        }

        if ($city) {
            $respponceData['city'] = [
                'name' => $parkData->city,
                'slug' => Str::slug($parkData->city),
            ];
        }

        return YResponse::json(data: $respponceData);
    }

    public function parkCategories(Request $request, $slug)
    {
        $type = $request->type;
        $perPage = $request->get('per_page', 15);

        // Common query to check parks with categories/subcategories by slug
        $parkQuery = Parks::query();
        if ($slug != 'nearby') {
            $parkQuery->whereHas('categories', function ($query) use ($slug) {
                $query->whereHas('category', fn($q) => $q->where('slug', $slug))
                    ->orWhereHas('subcategory', fn($q) => $q->where('slug', $slug));
            });
        }

        if ($request->header('longitude') && $request->header('latitude')) {
            $parkQuery->select([
                'parks.*',
                DB::raw('ROUND((6371 * acos( cos( radians(' . $request->header('latitude') . ') ) * cos( radians(parks.latitude ) ) * cos( radians(parks.longitude ) - radians(' . $request->header('longitude') . ') ) + sin( radians(' . $request->header('latitude') . ') ) * sin( radians(parks.latitude ) ) ) ),2) AS distance'),
            ])
                ->where('parks.active', 1)
                ->orderBy('distance', 'asc');
        }

        if ($type === 'parent') {
            // Try fetching category with subcategories
            $category = Category::with('subcategories')->whereSlug($slug)->first();

            if (!$category) {
                return YResponse::json('No data found.', [], 404);
            }

            if ($category->subcategories->isEmpty()) {
                $data = new ParkShortInfoCollection($parkQuery->paginate($perPage)->withQueryString());
                if ($data->isEmpty()) {
                    return YResponse::json('No parks found for this category.', [], 404);
                }
                return YResponse::json(data: $data->response()->getData());
            }

            // If subcategories exist, return them (paginated)
            $data = new SubcategoryCollection($category->subcategories()->paginate($perPage)->withQueryString());
            return YResponse::json(data: $data->response()->getData());
        } elseif ($type === 'child') {
            // If type is 'child', return parks with categories/subcategories
            if ($parkQuery->exists()) {
                $data = new ParkShortInfoCollection($parkQuery->paginate($perPage)->withQueryString());
                return YResponse::json(data: $data->response()->getData());
            }
            return YResponse::json('No parks found for this subcategory.', [], 404);
        } else {
            return YResponse::json('Invalid type provided. Must be "parent" or "child".', [], 400);
        }
    }

    public function parkFeatures(Request $request, $slug)
    {
        $perPage = $request->get('per_page', 15);
        $parkQuery = $this->getFilteredParks($slug);
        if ($parkQuery->exists()) {
            $data = new ParkShortInfoCollection($parkQuery->paginate($perPage)->withQueryString());
            return YResponse::json(data: $data->response()->getData());
        }
        return YResponse::json('No parks found for this feature.', [], 404);
    }

    public function nearbyParks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
        }

        $city = $request->input('city') ?? null;
        $userLat = $request->input('latitude');
        $userLng = $request->input('longitude');
        $radius = $request->input('radius', 100);
        $perPage = $request->get('per_page', 15);

        $parks = Parks::with('park_images')
            ->select('*', DB::raw("(
                6371 * acos(
                    cos(radians(?)) *
                    cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(latitude))
                )
            ) AS distance"))
            ->addBinding([$userLat, $userLng, $userLat], 'select')
            ->having('distance', '<=', $radius)
            ->orderBy('distance')
            ->paginate($perPage)
            ->withQueryString();

        $parksCount = $parks->count();

        if ($parksCount < 10 && $city) {
            $parks = Parks::whereCity($city)
                ->paginate($perPage)
                ->withQueryString();
        }

        $data = ParkShortInfoResource::collection($parks);
        return YResponse::json(data: $data->response()->getData());
    }

    public function topFeature(Request $request)
    {
        $country = $request->country ? $this->fetchParksBasicData('country_short_name', $request->country)->country_short_name : null;
        $state = $request->state ? $this->fetchParksBasicData('state_slug', $request->state)->state_slug : null;
        $city = $request->city ? $this->fetchParksBasicData('city_slug', $request->city)->city_slug : null;
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);

        $features = Feature::whereHas('image')
            ->whereIn('slug', $this->metaFeatures(false))
            ->withCount([
                'parks' => function ($query) use ($country, $state, $city) {
                    if ($country) {
                        $query->where(function ($q) use ($country) {
                            $q->where('country', 'like', "%$country%")
                                ->orWhere('country_short_name', $country);
                        });
                    }

                    if ($state) {
                        $query->where(function ($subQuery) use ($state) {
                            $subQuery->where('state', 'like', "%$state%")
                                ->orWhere('state_slug', $state);
                        });
                    }

                    if ($city) {
                        $query->where(function ($subQuery) use ($city) {
                            $subQuery->where('city', 'like', "%$city%")
                                ->orWhere('city_slug', $city);
                        });
                    }
                }
            ])
            ->having('parks_count', '>', 1)
            ->orderByDesc('parks_count')
            ->get();

        $features_types = FeatureType::whereHas('image')
            ->whereIn('slug', $this->metaFeatures($type = true))
            ->withCount([
                'parks' => function ($query) use ($country, $state, $city) {
                    if ($country) {
                        $query->where(function ($q) use ($country) {
                            $q->where('country', 'like', "%$country%")
                                ->orWhere('country_short_name', $country);
                        });
                    }

                    if ($state) {
                        $query->where(function ($subQuery) use ($state) {
                            $subQuery->where('state', 'like', "%$state%")
                                ->orWhere('state_slug', $state);
                        });
                    }

                    if ($city) {
                        $query->where(function ($subQuery) use ($city) {
                            $subQuery->where('city', 'like', "%$city%")
                                ->orWhere('city_slug', $city);
                        });
                    }
                }
            ])
            ->having('parks_count', '>', 1)
            ->orderByDesc('parks_count')
            ->get();

        $currentPageItems = $features
            ->merge($features_types)
            ->sortByDesc('parks_count')
            ->take($perPage)
            ->values();

        $total = $currentPageItems->count();

        $paginated = new LengthAwarePaginator(
            $currentPageItems,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $dataCollection = FeatureResource::collection($paginated);

        return YResponse::json(data: $dataCollection->response()->getData());
    }

    public function f_a_q(Request $request)
    {
        $country = $countrySlug = $request->country ?? null;
        $state = $stateSlug = $request->state ?? null;
        $city = $citySlug = $request->city ?? null;
        $slug = $request->slug ?? null;
        $return = array();
        $featureSlug = null;
        if ($countrySlug) {
            $country = $this->fetchParksBasicData('country_short_name', $countrySlug)->country;
        }

        if ($stateSlug) {
            $state = $this->fetchParksBasicData('state_slug', $stateSlug)->state;
        }

        if ($citySlug) {
            $city = $this->fetchParksBasicData('city_slug', $citySlug)->city;
        }

        if ($slug) {

            $featureSlug = Feature::where('slug', $slug)
                ->first();

            if (!$featureSlug) {
                $featureSlug = FeatureType::where('slug', $slug)
                    ->first();
                if (!$featureSlug) {
                    return YResponse::json('No data found.', [], 404);
                }
            }

            $newSlug = $featureSlug->name;
            $filteredParks = $this->getFilteredParks($featureSlug->slug, $citySlug, $stateSlug, $countrySlug, false);

            $parksCount = $filteredParks->count();

            $return = [
                [
                    'question' => "How many parks with $newSlug in $city are available to explore on Parkscape?",
                    'answer' => "Browse $parksCount parks in $city with $newSlug in $city on Parkscape.",
                ],
                [
                    'question' => "How do I locate parks with $newSlug in $city on Parkscape?",
                    'answer' => 'Quickly find ' . $newSlug . ' in ' . $city . ' from both the home page or map page by either selecting the feature out of the "All Features" menu or by simply searching for ' . $newSlug . ' using the search bar.',
                ],
                [
                    'question' => "Are $newSlug in $city easily accessible and free to use for everyone?",
                    'answer' => 'While most ' . $newSlug . ' in city are readily accessible and free of charge, some ' . $newSlug . ' may have an admission fee or require reservations. Please check out the "Plan Your Visit" section for more information.',
                ]
            ];
        } else if (!$slug && $city) {
            $parksCount = Parks::whereCity($city)->count();
            $return = [
                [
                    'question' => "How many parks does Parkscape cover in $city?",
                    'answer' => "Parkscape has details of $parksCount parks in $city. As the best resource for finding parks, our team works daily to add new locations and update existing ones. Keep an eye out for new amazing outdoor places to explore.",
                ],
                [
                    'question' => "What kind of details can I find about parks in $city on Parkscape?",
                    'answer' => "Parkscape is your trusted park guide where you can easily find things like features, reviews, and photos about parks in $city. We strive to deliver the most accuruate and up-to-date details so you can spend the best time outside.",
                ],
                [
                    'question' => "What kinds of amenities can I expect to find at parks in $city?",
                    'answer' => "From small neighborhood parks to large outdoor spaces in the heart of the city, parks in $city offer a wide-range of features for all to enjoy. Fun-filled playgrounds, great tennis courts, super athletic fields, and off-leash dog parks are just some of the fantastic amenities $city parks offer.",
                ]
            ];
        } else if (!$slug && !$city && $state) {
        } else if (!$slug && !$city && !$state && $country) {
        } else {
        }

        $return = array_values($return);
        return response()->json(['data' => $this->convertToStructuredData($return)]);
    }

    public function fetchParksBasicData($column, $data)
    {
        return Parks::where($column, $data)->first();
    }

    public function convertToStructuredData(array $faqArray): array
    {
        $structured = [];

        foreach ($faqArray as $item) {
            $structured[] = [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['answer'],
                ],
            ];
        }

        return $structured;
    }

    public function side_map_slug(Request $request)
    {
        $type = $request->type ?? null;
        $respponceData = [];

        if (!$type) {
            return YResponse::json('Missing type parameter', status: 400);
        }

        switch ($type) {
            case 'city':
                $respponceData['cities'] = Parks::where('active', 1)
                    ->select('city', 'state', 'country', 'country_short_name')
                    ->whereNotNull('city')
                    ->groupBy('city')
                    ->havingRaw('COUNT(*) > 10')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'name' => $item->city,
                            'slug' => Str::slug($item->city),
                            'state' => $item->state,
                            'state_slug' => Str::slug($item->state),
                            'countrie' => $item->country,
                            'country_short_name' => $item->country_short_name
                        ];
                    })
                    ->values();
                break;

            case 'state':
                $respponceData['states'] = Parks::where('active', 1)
                    ->select('state')
                    ->whereNotNull('state')
                    ->groupBy('state')
                    ->havingRaw('COUNT(*) > 10')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'name' => $item->state,
                            'slug' => Str::slug($item->state),
                        ];
                    })
                    ->values();
                break;

            case 'country':
                $respponceData['countries'] = Parks::where('active', 1)->select('country', 'country_short_name')
                    ->whereNotNull('country')
                    ->groupBy('country', 'country_short_name')
                    ->havingRaw('COUNT(*) > 10')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'name' => $item->country,
                            'slug' => $item->country_short_name,
                        ];
                    });
                break;

            case 'feature':
                $respponceData['features'] = Feature::where('active', 1)
                    ->whereIn('slug', $this->metaFeatures(false))
                    ->select('id', 'name', 'slug')
                    // ->withCount('parks')
                    // ->having('parks_count', '>', 10)
                    ->get()
                    ->map(fn($f) => [
                        'name' => $f->name,
                        'slug' => $f->slug,
                    ]);
                break;

            case 'feature_type':
                $respponceData['feature_types'] = FeatureType::where('active', 1)
                    ->whereIn('slug', $this->metaFeatures($type = true))
                    ->select('id', 'name', 'slug')
                    // ->withCount('parks')
                    // ->having('parks_count', '>', 10)
                    ->get()
                    ->map(fn($f) => [
                        'name' => $f->name,
                        'slug' => $f->slug,
                    ]);
                break;

            case 'park':
                $respponceData['parks'] = Parks::whereHas('park_images')
                    ->where('active', 1)
                    ->select('city', 'state', 'country', 'country_short_name', 'name', 'slug')
                    ->get()
                    ->map(fn($item) => [
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'city' => $item->city,
                        'city_slug' => Str::slug($item->city),
                        'state' => $item->state,
                        'state_slug' => Str::slug($item->state),
                        'countrie' => $item->country,
                        'country_short_name' => $item->country_short_name
                    ]);
                break;

            default:
                return YResponse::json('Invalid type value', status: 400);
        }

        return YResponse::json(data: $respponceData);
    }

    public function parkContainer(Request $request)
    {
        $country = $request->country ? $this->fetchParksBasicData('country_short_name', $request->country)->country_short_name : null;
        $state = $request->state ? $this->fetchParksBasicData('state_slug', $request->state)->state_slug : null;
        $city = $request->city ? $this->fetchParksBasicData('city_slug', $request->city)->city_slug : null;
        $slug = $request->slug ?? null;

        if (!$country || !$state) {
            return YResponse::json('Data not found!', status: 404);
        }

        $query = $this->getFilteredParks(
            $slug,
            $city,
            $state,
            $country,
            true
        );

        $parks = $query->withCount('park_images')
            ->with([
                'park_images' => fn($q) => $q->limit(5),
                'featuresType',
                'ratings'
            ]);

        $locations = Location::with('containers.feature');
        $matchLevel = 0;
        $isContainer = false;
        $featureData = null;

        if ($slug) {
            $featureSlug = Feature::where('slug', $slug)->first();
            if (!$featureSlug) {
                $featureSlug = FeatureType::where('slug', $slug)->first();

                if (!$featureSlug) {
                    return YResponse::json('No data found.', [], 404);
                }

                $featureData = new FeatureTypeResource($featureSlug);
            } else {
                $featureData = new FeatureResource($featureSlug);
            }

            // $parks = $this->filterParksBySlug($parks, $slug, $matchLevel);
            $locations->where(function ($subQuery) use ($request) {
                $subQuery->with('containers.feature', function ($q) use ($request) {
                    $q->where('slug', $request->slug)->orWhere('slug', 'like', "%$request->slug%");
                });
            });
        }

        if ($country) {
            // $parks->where(function ($query) use ($country) {
            //     $query->where('country', 'like', "%$country%")
            //         ->orWhere('country_short_name', $country);
            // });

            $locations->where(function ($subQuery) use ($country) {
                $subQuery->where('country', 'like', "%$country%")
                    ->orWhere('country_short_name', $country);
            });
            // $locations->nestedWhere('country', 'like', "%$country%", 'or', 'country_short_name', '=', $country);
        }

        if ($state) {
            // $parks->where(function ($subQuery) use ($state) {
            //     $subQuery->where('state', 'like', "%$state%")
            //         ->orWhere('state_slug', $state);
            // });
            $locations->where(function ($subQuery) use ($state) {
                $subQuery->where('state', 'like', "%$state%")
                    ->orWhere('state_slug', $state);
            });
        }

        if ($city) {
            // $parks->where(function ($subQuery) use ($city) {
            //     $subQuery->where('city', 'like', "%$city%")
            //         ->orWhere('city_slug', $city);
            // });
            $locations->where(function ($subQuery) use ($city) {
                $subQuery->where('city', 'like', "%$city%")
                    ->orWhere('city_slug', $city);
            });
        }

        $location = $locations->first();

        $isContainers = $location
            ->containers()
            ->whereHas('feature', function ($q) use ($request) {
                $q->where('slug', $request->slug)
                    ->orWhere('slug', 'like', "%$request->slug%");
            })->first();

        $isContainer = $location && $isContainers && $slug;
        $locationData = new LocationShortInfoResource($location);

        $parks
            // ->select('*')
            // ->addSelect(DB::raw("{$matchLevel} as match_level"))
            ->withAvg('ratings', 'rating')
            ->orderByDesc('park_images_count')
            // ->orderBy('match_level')
            ->orderByDesc('ratings_avg_rating');

        if ($parks->count() == 0) {
            return YResponse::json(data: [
                'parks' => [],
                'park_reviews' => [],
                'isContainer' => $isContainer,
                'locations' => []
            ], status: 404);
        }

        $data['parks'] = (ParkShortDataResource::collection($parks->paginate($request->get('per_page', 15))->withQueryString()))->response()->getData();
        $data['locations'] = $locationData;
        $data['isContainer'] = $isContainer;
        $data['feature'] = $featureData;

        return YResponse::json(data: $data);
    }

    public function filterParksBySlug($parks, string $slug, &$matchLevel)
    {
        // 0. Match exact feature slug
        if (in_array($slug, ['playgrounds', 'outdoor-gyms'])) {
            $query = clone $parks;
            $matchLevel = 5;
            return $query;
        }

        // 1. Match exact feature slug
        $query = clone $parks;
        $query->whereHas('features.feature', fn($q) => $q->where('slug', $slug));
        if ($query->count()) {
            $matchLevel = 1;
            return $query;
        }

        // 2. Match feature type
        $query = clone $parks;
        $query->whereHas('features.feature_type', fn($q) => $q->where('slug', $slug));
        if ($query->count()) {
            $matchLevel = 2;
            return $query;
        }

        // 3. Match description
        $query = clone $parks;
        $query->where('description', 'like', "%{$slug}%");
        if ($query->count()) {
            $matchLevel = 3;
            return $query;
        }

        // 4. Fallback to fulltext or search()
        $parks->where('name', 'like', "%{$slug}%");
        $matchLevel = 4;
        return $parks;
    }

    public function isFeatureInCity(Request $request)
    {
        $citySlug = $request->city ?? null;
        $featureSlug = $request->slug ?? null;
        $parkQuery = $this->getFilteredParks($slug = $featureSlug, $city = $citySlug);
        $count = $parkQuery->count();
        if ($count > 0) {
            return YResponse::json(data: $count);
        }
        return YResponse::json('Data not found!', status: 404);
    }

    public function parksReview(Request $request)
    {
        $park = Parks::whereSlug($request->slug)->first();
        if (auth()->id()) {
            $data = RatingResource::collection($park->ratings()
                ->where('user_id', auth()->id())
                ->where('is_verified', 1)
                ->limit(4)
                ->orderBy('created_at', 'desc')
                ->get());
            return YResponse::json(data: $data);
        } else {
            return YResponse::json(data: []);
        }
    }

    public function lat_lng_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
        }

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $token_ = config('services.MAP_BOX_ACCESS_TOKEN');
        $url = "https://api.mapbox.com/geocoding/v5/mapbox.places/$longitude,$latitude.json?access_token=$token_";
        $country = $state = $city = $locality = $neighborhood = null;

        $guzzle = new \GuzzleHttp\Client();
        $response =  $guzzle->get($url); // Url of your choosing
        $res = json_decode($response->getBody(), true);

        if (!isset($res['features']) || empty($res['features'])) {
            return response()->json(['error' => 'No features found'], 404);
        }

        $features = $res['features'];

        foreach ($features as $element) {
            $type = explode('.', $element['id'])[0];

            if ($type == 'country' && !$country) {
                $country = $element['text'];
                // $countryShort = $element['properties']['short_code'] ?? '';
            }

            // if (in_array($type, ['place', 'district', 'locality']) && !$city) {
            //     $city = $element['text'];
            // }

            if (in_array($type, ['place', 'district']) && !$city) {
                switch ($type) {
                    case 'place':
                        $city = $element['text'];
                        break;
                    case 'district':
                        $city = $element['text'];
                        break;
                    default:
                        if (isset($element['properties']['city'])) {
                            $city = $element['properties']['city'];
                        }
                        break;
                }
            }

            if (in_array($type, ['region']) && !$state) {
                $state = $element['text'];
            }

            if (in_array($type, ['locality']) && !$locality) {
                $locality = $element['text'];
            }

            if (in_array($type, ['neighborhood']) && !$neighborhood) {
                $neighborhood = $element['text'];
            }
        }

        return YResponse::json(data: [
            'address' => $features[0]['place_name'] ?? null,
            'country' => $country,
            'state' => $state,
            'city' => $city ?? ($locality ?? $neighborhood),
        ]);
    }
}
