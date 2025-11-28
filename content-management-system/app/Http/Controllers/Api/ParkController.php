<?php

namespace App\Http\Controllers\Api;

use App\Helpers\YResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserAddParkImageRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CategoryShortInfoResource;
use App\Http\Resources\FeatureResource;
use App\Http\Resources\FeatureTypeResource;
use App\Http\Resources\ImageFilterLabelResource;
use App\Http\Resources\LocationShortResource;
use App\Http\Resources\MyParkImagesCollection;
use App\Http\Resources\ParkCollection;
use App\Http\Resources\ParkDetailResource;
use App\Http\Resources\ParkImageCollection;
use App\Http\Resources\ParkImageResource;
use App\Http\Resources\ParkMapPinResource;
use App\Http\Resources\ParkShortInfoCollection;
use App\Http\Resources\RatingCollection;
use App\Http\Resources\RatingResource;
use App\Http\Resources\SearchResource;
use App\Http\Resources\SubcategoryResource;
use App\Models\Category;
use App\Models\Feature;
use App\Models\FeatureType;
use App\Models\Media;
use App\Models\ParkImage;
use App\Models\Parks;
use App\Models\Pendingimage;
use App\Models\Rating;
use App\Models\Subcategory;
use App\Traits\CommonTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParkController extends Controller
{
    use CommonTraits;

    public function add_rating(Request $request)
    {
        $request->validate([
            'rating' => ['required', 'numeric', 'max:5', 'min:1'],
            'review' => ['nullable', 'string', 'max:1000'],
            'park_id' => ['required', 'exists:parks,id']
        ]);

        $user = $request->user();
        $park = Parks::where('id', $request->park_id)->first();
        $rating = $user->ratings()->where('park_id', $request->park_id)->first();
        if (empty($rating)) {
            $rating = $user->ratings()->create([
                'rating' => $request->rating,
                'review' => $request->review,
                'park_id' => $request->park_id
            ]);
        } else {
            $rating->rating = $request->rating;
            $rating->review = $request->review;
            $rating->is_verified = 0;
            $rating->save();
        }
        $total_ratings = $park->ratings()->where('is_verified', 1)->count();
        $avg_ratings = (float) number_format($park->ratings()->where('is_verified', 1)->avg('rating'), 1);
        $ratings = new RatingResource($rating);
        $data = [
            'my_review' => $ratings,
            "rating" => [
                "total_ratings" => $total_ratings,
                "5" => $total_ratings ? (float) number_format($park->ratings()->where("rating", 5)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                "4" => $total_ratings ? (float) number_format($park->ratings()->where("rating", 4)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                "3" => $total_ratings ? (float) number_format($park->ratings()->where("rating", 3)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                "2" => $total_ratings ? (float) number_format($park->ratings()->where("rating", 2)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                "1" => $total_ratings ? (float) number_format($park->ratings()->where("rating", 1)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                "avg_ratings" => $avg_ratings,
            ]
        ];
        return YResponse::json(data: $data, message: "Your review is in under verification");
    }


    public function getParks(Request $request)
    {
        $park_query = Parks::where('active', 1);
        $information = null;
        switch ($request->type) {
            case 'no-child':
                $park_query = $park_query->whereHas('categories', function ($query) use ($request) {
                    $query->where('category_id', $request->id);
                });
                $information = new CategoryShortInfoResource(Category::find($request->id));
                break;
            case 'child':
                $park_query = $park_query->whereHas('categories', function ($query) use ($request) {
                    $query->where('subcategory_id', $request->id);
                });
                $information = new SubcategoryResource(Subcategory::find($request->id));
                break;
            case 'child-feature':
                $park_query = $park_query->whereHas('features', function ($query) use ($request) {
                    $query->where('feature_id', $request->id);
                });
                $information = new FeatureResource(Feature::find($request->id));
                break;
            case 'parent-feature':
                $park_query = $park_query->whereHas('features', function ($query) use ($request) {
                    $query->where('feature_type_id', $request->id);
                });
                $information = new FeatureTypeResource(FeatureType::find($request->id));
                break;
        }

        if ($request->header('longitude') && $request->header('latitude')) {
            $park_query = $park_query->select([
                'parks.*',
                DB::raw('ROUND((6371 * acos( cos( radians(' . $request->header('latitude') . ') ) * cos( radians(parks.latitude ) ) * cos( radians(parks.longitude ) - radians(' . $request->header('longitude') . ') ) + sin( radians(' . $request->header('latitude') . ') ) * sin( radians(parks.latitude ) ) ) ),2) as distance'),
            ])
                ->where('parks.active', 1)
                ->orderBy('distance', 'asc');
        }

        // if ($request->longitude && $request->latitude) {
        //     $park_query = $park_query->select([
        //         'parks.*',
        //         DB::raw('ROUND((6371 * acos( cos( radians(' . $request->latitude . ') ) * cos( radians(parks.latitude ) ) * cos( radians(parks.longitude ) - radians(' . $request->longitude . ') ) + sin( radians(' . $request->latitude . ') ) * sin( radians(parks.latitude ) ) ) ),2) as distance'),
        //     ])->where('parks.active', 1)->orderBy('distance', 'asc');
        // }

        return YResponse::json(data: [
            "parks" => (new ParkShortInfoCollection($park_query->paginate($request->get('per_page', 15))->withQueryString()))
                ->response()
                ->getData(),
            'extra-info' => $information ?? null
        ]);
    }


    public function getParkDetails(Parks $park)
    {
        if ($park->park_images()->whereNull('sort_index')->exists()) {
            $max_sort_index = $park->park_images()->max('sort_index');
            $imagesToUpdate = $park->park_images()->whereNull('sort_index')->get();

            // Start sorting index from the maximum existing sort index
            $sortIndex = $max_sort_index ?? 0;

            foreach ($imagesToUpdate as $image) {
                $sortIndex++;
                $image->update(['sort_index' => $sortIndex]);
            }
        }

        $park = new ParkDetailResource($park);
        return YResponse::json(data: ['park' => $park]);
    }


    public function getParkImages(Request $request, Parks $park)
    {
        $request->validate([
            'filter' => ['sometimes', 'in:all,latest,parkscape,users']
        ]);
        DB::enableQueryLog();
        $park_images = $park->park_images()->where('status', '1')->where(function ($query) {
            $query->where('is_verified', true)->orWhereNull('user_id');
        });

        switch ($request->get('filter')) {
            case 'all':
                $park_images = $park_images->orderBy('sort_index', 'asc');
                break;
            case 'latest':
                $park_images = $park_images->orderBy('created_at', 'desc');
                break;
            case 'parkscape':
                $park_images = $park_images->whereNull('user_id')->orWhere(function ($q) {
                    $q->whereNotNull('user_id')->where('is_verified', 1)->whereHas('user', function ($query) {
                        $query->role('subadmin');
                    });
                })->orderBy('created_at', 'desc');
                break;

            // case 'subadmin':
            //     // $park_images = $park_images->whereNotNull('user_id')->where('is_verified',false)->orderBy('created_at','desc');
            //     $park_images =  $park->park_images()->where('status', '1')->whereNotNull('user_id')->where('is_verified',1)
            //     ->WhereHas('user', function ($q) {
            //         $q->role('subadmin');
            //     })
            //     ->orderBy('created_at','desc');
            //     break;
            case 'users':
                $park_images = $park_images->whereNotNull('user_id')->where('is_verified', true)
                    ->WhereHas('user', function ($q) {
                        $q->role('user');
                    })
                    ->orderBy('created_at', 'desc');
                break;
        }


        return YResponse::json(data: ["parks" => (new ParkImageCollection($park_images->paginate($request->get('per_page', 15))->withQueryString()))->response()->getData()]);
    }


    public function getParkDetailsBySlug($slug)
    {
        $park = $park = $this->findPark($slug);

        if (!$park) {
            return YResponse::json(message: __('api_message.park_not_found'), status: 404);
        }

        if ($park->park_images()->whereNull('sort_index')->exists()) {
            $max_sort_index = $park->park_images()->max('sort_index');
            $imagesToUpdate = $park->park_images()->whereNull('sort_index')->get();

            // Start sorting index from the maximum existing sort index
            $sortIndex = $max_sort_index ?? 0;

            foreach ($imagesToUpdate as $image) {
                $sortIndex++;
                $image->update(['sort_index' => $sortIndex]);
            }
        }

        $park = new ParkDetailResource($park);
        return YResponse::json(data: ['park' => $park]);
    }


    public function getParkImagesBySlug(Request $request, $slug)
    {
        $park = $park = $this->findPark($slug);

        if (!$park) {
            return YResponse::json(message: __('api_message.park_not_found'), status: 404);
        }

        $request->validate([
            'filter' => ['sometimes', 'in:all,latest,parkscape,users']
        ]);

        DB::enableQueryLog();

        $park_images = $park->park_images()->where('status', '1')->where(function ($query) {
            $query->where('is_verified', true)->orWhereNull('user_id');
        });

        switch ($request->get('filter')) {
            case 'latest':
                $park_images = $park_images->orderByDesc('created_at');
                break;
            case 'parkscape':
                $park_images = $park_images->where(function ($que) {
                    $que->whereNull('user_id')
                        ->orWhere(function ($q) {
                            $q->whereNotNull('user_id')
                                ->where('is_verified', 1)
                                ->whereHas('user', function ($query) {
                                    $query->role('subadmin');
                                });
                        });
                })->orderBy('created_at', 'asc');
                break;
            case 'users':
                $park_images = $park_images->whereNotNull('user_id')->where('is_verified', true)
                    ->WhereHas('user', function ($q) {
                        $q->role('user');
                    })
                    ->orderByDesc('created_at');
                break;
            default:
                // $park_images = $park_images->orderBy('sort_index', 'asc');
                $park_images = $park_images->orderBy('img_tmp_id');
        }
        // $park_images->dd();
        return YResponse::json(data: ["parks" => (new ParkImageCollection($park_images->paginate($request->get('per_page', 15))->withQueryString()))->response()->getData()]);
    }


    public function getNumberOfTotalImages(Request $request, Parks $park)
    {
        $park_images = $park->park_images()->where('status', '1')->where(function ($query) {
            $query->where('is_verified', true)->orWhereNull('user_id');
        });

        $data = new ImageFilterLabelResource($park_images);
        return YResponse::json(data: $data);
    }


    public function getRatings(Request $request, Parks $park)
    {
        $user = $request->user();
        if (!empty($user)) {
            $ratings = $park->ratings()->where('user_id', '!=', $request->user()->id)->where('is_verified', 1)->orderBy('created_at', 'desc');
        } else {
            $ratings = $park->ratings()->where('is_verified', 1)->orderBy('created_at', 'desc');
        }
        return YResponse::json(data: ["reviews" => (new RatingCollection($ratings->paginate($request->get('per_page', 15))->withQueryString()))->response()->getData()]);
    }


    public function getRatingsBySlug(Request $request, $slug)
    {
        $park = $this->findPark($slug);

        if (!$park) {
            return YResponse::json(message: __('api_message.park_not_found'), status: 404);
        }

        $user = $request->user();

        if (!empty($user)) {
            $ratings = $park->ratings()->where('user_id', '!=', $request->user()->id)->where('is_verified', 1)->orderBy('created_at', 'desc');
        } else {
            $ratings = $park->ratings()->where('is_verified', 1)->orderBy('created_at', 'desc');
        }

        return YResponse::json(data: ["reviews" => (new RatingCollection($ratings->paginate($request->get('per_page', 15))->withQueryString()))->response()->getData()]);
    }


    public function deleteRating(Request $request, $id)
    {
        $rating = Rating::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!empty($rating)) {
            $park = Parks::where('id', $rating->park_id)->first();
            $rating->delete();

            $total_ratings = $park->ratings()->where('is_verified', 1)->count();
            $avg_ratings = (float) number_format($park->ratings()->where('is_verified', 1)->avg('rating'), 1);
            $data = [
                "rating" => [
                    "total_ratings" => $total_ratings,
                    "5" => $total_ratings ? (float) number_format($park->ratings()->where("rating", 5)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                    "4" => $total_ratings ? (float) number_format($park->ratings()->where("rating", 4)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                    "3" => $total_ratings ? (float) number_format($park->ratings()->where("rating", 3)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                    "2" => $total_ratings ? (float) number_format($park->ratings()->where("rating", 2)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                    "1" => $total_ratings ? (float) number_format($park->ratings()->where("rating", 1)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                    "avg_ratings" => $avg_ratings,
                ]
            ];
            return YResponse::json(data: $data);
        } else {
            return YResponse::json();
        }
    }


    public function parks_filter(Request $request)
    {
        $request->validate([
            'feature_ids' => ['sometimes', 'array'],
            'feature_ids.*' => ['exists:features,id'],
            'latitude' => ['sometimes'],
            'longitude' => ['sometimes'],
            'feature_type_id' => ['sometimes', 'exists:feature_types,id'],
            'search' => ['sometimes'],
        ]);

        $parks = Parks::query()->where('active', 1);
        $distance = 50;


        if ($request->has('feature_ids')) {
            $parks = $parks->whereHas('features', function ($query) use ($request) {
                $query->whereIn('feature_id', $request->feature_ids);
            });
        }


        if ($request->has('feature_type_id')) {
            $parks = $parks->whereHas('features', function ($query) use ($request) {
                $query->where('feature_type_id', $request->feature_type_id);
            });
        }

        if ($request->has('search')) {
            $parks = $parks->whereRaw('name LIKE "%' . $request->search . '%" ')->orWhereRaw('country LIKE "%' . $request->search . '%" ')->orWhereRaw('city LIKE "%' . $request->search . '%" ')->orWhereHas('features', function ($query) use ($request) {
                $query->whereHas('feature', function ($query) use ($request) {
                    $query->whereRaw('name LIKE "%' . $request->search . '%" ');
                })->orWhereHas('feature_type', function ($query) use ($request) {
                    $query->whereRaw('name LIKE "%' . $request->search . '%" ');
                });
            });
        }
        if ($request->has('latitude') && $request->has('longitude')) {
            $parks = $parks->select([
                'parks.*',
                DB::raw('ROUND((6371 * acos( cos( radians(' . $request->latitude . ') ) * cos( radians(parks.latitude ) ) * cos( radians(parks.longitude ) - radians(' . $request->longitude . ') ) + sin( radians(' . $request->latitude . ') ) * sin( radians(parks.latitude ) ) ) ),2) as distance'),
            ])->distinct('id')->having('distance', '<=', $distance ?? config('constants.default_radius'))->orderBy('distance');
        }

        // if ($request->has('area_latitude') && $request->has('area_longitude')) {
        //     $parks = $parks->select([
        //         'parks.*',
        //         DB::raw('ROUND((6371 * acos( cos( radians(' . $request->area_latitude . ') ) * cos( radians(parks.latitude ) ) * cos( radians(parks.longitude ) - radians(' . $request->area_longitude . ') ) + sin( radians(' . $request->area_latitude . ') ) * sin( radians(parks.latitude ) ) ) ),2) as distance'),
        //     ])->distinct('id')->having('distance', '<=', $distance ?? config('constants.default_radius'))->orderBy('distance');
        // }



        return YResponse::json(data: ["parks" => (new ParkShortInfoCollection($parks->paginate($request->get('per_page', 50))->withQueryString()))->response()->getData()]);
    }


    public static function randomTmpId()
    {
        return bin2hex(random_bytes(8));
    }


    public function add_image(UserAddParkImageRequest $request)
    {
        $user = $request->user();
        $park_id = $request->park_id;
        $park = Parks::find($park_id);
        ini_set('max_file_uploads', '25');


        if (empty($park)) {
            return YResponse::json(message: __('api_message.park_not_found'), status: 404);
        }
        if (!$park->active) {
            return YResponse::json(message: __('api_message.park_not_available'), status: 404);
        }

        if (count($request->images) > 25) {
            return YResponse::json(message: __('api_message.Max_image_size'), status: 400);
        }

        $data = $request->only('park_id');
        $data['user_id'] = $user->id;
        $data['status'] = "1";

        $total_pending_image = ParkImage::where('park_id', $request->park_id)->where('user_id', $user->id)->where('is_verified', 0)->count();
        Pendingimage::updateOrCreate(
            ['park_id' => $request->park_id, 'user_id' => $user->id],
            ['park_id' => $request->park_id, 'user_id' => $user->id, 'total_pending_image' => $total_pending_image]
        );


        foreach ($request->images as $image) {
            $media = Media::save_media(file: $image, dir: 'parks', tags: ['user park image'], user_id: $user->id, store_as: 'image');
            $data['media_id'] = $media->id;

            // Pendingimage::where('user_id',$user->id)->where('park_id',$request->park_id)
            $pendingImage = Pendingimage::updateOrCreate(
                ['park_id' => $request->park_id, 'user_id' => $user->id],
                ['park_id' => $request->park_id, 'user_id' => $user->id]
            );

            $pendingImage->increment('total_pending_image');
            $data['img_tmp_id'] = $this->randomTmpId();
            ParkImage::create($data);
        }

        //unarchive image uploading image time
        ParkImage::where('park_id', $request->park_id)->where('user_id', $user->id)->where('is_archived', 1)->update([
            'is_archived' => 0
        ]);

        $park_image = ParkImage::where('user_id', $user->id)->where('park_id', $request->park_id)->latest()->limit(count($request->images))->get();


        return YResponse::json(data: ['image' => ParkImageResource::collection($park_image)]);
    }


    public function search_filter(Request $request)
    {
        $request->validate([
            'feature_ids' => ['sometimes', 'array'],
            'feature_ids.*' => ['exists:features,id'],
            'latitude' => ['sometimes'],
            'longitude' => ['sometimes'],
            'feature_type_id' => ['sometimes', 'exists:feature_types,id'],
            'search' => ['sometimes', 'string'],
        ]);

        $parks = Parks::where('active', 1);

        if ($request->has('latitude') && $request->has('longitude')) {
            $parks = $parks->select([
                'parks.*',
                DB::raw('ROUND((6371 * acos( cos( radians(' . $request->latitude . ') ) * cos( radians(parks.latitude ) ) * cos( radians(parks.longitude ) - radians(' . $request->longitude . ') ) + sin( radians(' . $request->latitude . ') ) * sin( radians(parks.latitude ) ) ) ),2) as user_distance'),
            ]);
        }

        if ($request->has('area_latitude') && $request->has('area_longitude')) {
            $parks = $parks->select([
                'parks.*',
                DB::raw('ROUND((6371 * acos( cos( radians(' . $request->area_latitude . ') ) * cos( radians(parks.latitude ) ) * cos( radians(parks.longitude ) - radians(' . $request->area_longitude . ') ) + sin( radians(' . $request->area_latitude . ') ) * sin( radians(parks.latitude ) ) ) ),2) as distance'),
            ])->having('distance', '<=', $distance ?? config('constants.default_radius'));
        }

        if ($request->has('feature_ids')) {
            $parks = $parks->whereHas('features', function ($query) use ($request) {
                $query->whereIn('feature_id', $request->feature_ids);
            });
        }


        if ($request->has('feature_type_id')) {
            $parks = $parks->whereHas('features', function ($query) use ($request) {
                $query->where('feature_type_id', $request->feature_type_id);
            });
        }

        // if ($request->has('search')) {
        //     $parks = $parks->whereRaw('name LIKE "%' . $request->search . '%" ')
        //         ->orWhereHas('features', function ($query) use ($request) {
        //             $query->whereHas('feature', function ($query) use ($request) {
        //                 $query->whereRaw('name LIKE "%' . $request->search . '%" ');
        //             })->orWhereHas('feature_type', function ($query) use ($request) {
        //                 $query->whereRaw('name LIKE "%' . $request->search . '%" ');
        //             });
        //         });
        // }

        $parks = $parks->search($request->search);

        $parks = $parks->limit(30)->orderBy('name', 'asc')->get();

        foreach ($parks as $park) {
            $park->custom_type = 'park';
        }

        $feature_types = FeatureType::search($request->search)->where('active', 1)->limit(30)->orderBy('name', 'asc')->groupBy('name')->get();

        foreach ($feature_types as $feature_type) {
            $feature_type->custom_type = 'feature';
        }

        $features = Feature::search($request->search)->where('active', 1)->limit(30)->orderBy('name', 'asc')->groupBy('name')->get();

        foreach ($features as $feature) {
            $feature->custom_type = 'sub-feature';
        }


        $merge = collect($feature_types)->merge(collect($features));
        $merge->all();
        $merge = $merge->merge($parks)->unique();


        return YResponse::json(data: ["data" => SearchResource::collection($merge)]);
    }


    public function my_ratings(Request $request)
    {
        $ratings = Rating::where('user_id', $request->user()->id);

        return YResponse::json(data: ["reviews" => (new RatingCollection($ratings->paginate($request->get('per_page', 15))->withQueryString()))->response()->getData()]);
    }


    public function my_park_images_list(Request $request)
    {
        $user = $request->user();

        $park_ids = ParkImage::where('user_id', $user->id)->distinct('park_id')->pluck('park_id')->toArray();

        $parks = Parks::whereIn('id', $park_ids);

        return YResponse::json(data: ["parks" => (new MyParkImagesCollection($parks->paginate($request->get('per_page', 15))->withQueryString()))->response()->getData()]);
    }

    public function my_park_images(Request $request, $park_id)
    {

        $user = $request->user();
        $park_images = ParkImage::where('user_id', $user->id)->where('park_id', $park_id)->get();


        $data = [
            'images' => ParkImageResource::collection($park_images)
        ];

        return YResponse::json(data: ["data" => $data]);
    }

    public function park_map_pins(Request $request)
    {
        $request->validate(
            [
                'latitude' => ['sometimes'],
                'longitude' => ['sometimes'],
                'longitudeDelta' => ['sometimes'],
            ]
        );
        $parks = Parks::query()->where('active', 1);
        if ($request->has('latitude') && $request->has('longitude')) {
            $distance = $request->longitudeDelta ? $request->longitudeDelta * cos(0) * 111 : 50;
            $parks = $parks->select([
                'parks.*',
                DB::raw('ROUND((6371 * acos( cos( radians(' . $request->latitude . ') ) * cos( radians(parks.latitude ) ) * cos( radians(parks.longitude ) - radians(' . $request->longitude . ') ) + sin( radians(' . $request->latitude . ') ) * sin( radians(parks.latitude ) ) ) ),2) as distance'),
            ])->having('distance', '<=', $distance ?? config('constants.default_radius'))->orderbY('distance');
        }
        $parks = $parks->get();

        $data = [
            'parks' => ParkMapPinResource::collection($parks)
        ];

        return YResponse::json(data: ["data" => $data]);
    }

    public function likePark(Request $request)
    {
        $request->validate([
            'park_image_id' => ['required', 'exists:park_images,id']
        ]);

        $user = $request->user();
        $like = $user->likes()->where('park_image_id', $request->park_image_id)->first();
        if (empty($like)) {
            $like = $user->likes()->create([
                'park_image_id' => $request->park_image_id
            ]);
        } else {
            $like->delete();
        }

        return YResponse::json(message: 'success');
    }

    public function getParksBySlug(Request $request)
    {
        $park_query = Parks::where('active', 1);
        $information = null;
        switch ($request->type) {
            case 'no-child':
                $category = Category::where('slug', $request->slug)->firstOrFail();
                $park_query = $park_query->whereHas('categories', function ($query) use ($category) {
                    $query->where('category_id', $category->id);
                });
                $information = new CategoryShortInfoResource($category);
                break;
            case 'child':
                $subcategory = Subcategory::where('slug', $request->slug)->firstOrFail();
                $park_query = $park_query->whereHas('categories', function ($query) use ($subcategory) {
                    $query->where('subcategory_id', $subcategory->id);
                });
                $information = new SubcategoryResource($subcategory);
                break;
            case 'child-feature':
                $feature = Feature::where('slug', $request->slug)->firstOrFail();
                $park_query = $park_query->whereHas('features', function ($query) use ($feature) {
                    $query->where('feature_id', $feature->id);
                });
                $information = new FeatureResource($feature);
                break;
            case 'parent-feature':
                $featureType = FeatureType::where('slug', $request->slug)->firstOrFail();
                $park_query = $park_query->whereHas('features', function ($query) use ($featureType) {
                    $query->where('feature_type_id', $featureType->id);
                });
                $information = new FeatureTypeResource($featureType);
                break;
        }

        if ($request->header('longitude') && $request->header('latitude')) {
            $park_query = $park_query->select([
                'parks.*',
                DB::raw('ROUND((6371 * acos( cos( radians(' . $request->header('latitude') . ') ) * cos( radians(parks.latitude ) ) * cos( radians(parks.longitude ) - radians(' . $request->header('longitude') . ') ) + sin( radians(' . $request->header('latitude') . ') ) * sin( radians(parks.latitude ) ) ) ),2) as distance'),
            ])
                ->where('parks.active', 1)
                ->orderBy('distance', 'asc');
        }

        return YResponse::json(data: [
            "parks" => (new ParkShortInfoCollection($park_query->paginate($request->get('per_page', 15))->withQueryString()))
                ->response()
                ->getData(),
            'extra-info' => $information ?? null
        ]);
    }

    public function TopSlug(Request $request, $slug, $type = null)
    {
        $perPage = $request->get('per_page', 15);
        $data = [];

        $mapping = [
            'feature' => [
                Feature::class,
                FeatureResource::class,
                'parks',
                'features'
            ],
            'feature-type' => [
                FeatureType::class,
                FeatureTypeResource::class,
                'parks',
                'featuresType'
            ],
            'category' => [
                Category::class,
                CategoryResource::class,
                'parks',
                'categories'
            ],
            'subcategory' => [
                Subcategory::class,
                SubcategoryResource::class,
                'parks',
                'subcategory'
            ],
        ];

        if (!$type) {
            if (isset($mapping[$slug])) {
                [$model, $resource, $relation, $rel] = $mapping[$slug];
                $query = $model::withCount([$relation]);
                if ($slug === 'feature') {
                    $query->whereIn('name', $this->topFeatureList(false));
                } else if ($slug === 'feature-type') {
                    $query->whereIn('name', $this->topFeatureList(true));
                }
                $items = $query->having("{$relation}_count", '>=', 10)
                    ->orderByDesc("{$relation}_count")
                    ->paginate($perPage)
                    ->withQueryString();
                $data = $resource::collection($items)
                    ->response()
                    ->getData();
            } else if (
                in_array($slug, [
                    'city',
                    'state',
                    'country',
                    'country_short_name'
                ])
            ) {
                $parks = Parks::query()
                    ->selectRaw("city, city_slug, state, state_slug, country, country_short_name, COUNT(*) as parks_count")
                    ->groupBy($slug)
                    ->havingRaw('COUNT(*) >= 10')
                    // ->orderByDesc('parks_count')
                    ->orderBy('city')
                    ->paginate($perPage)
                    ->withQueryString();
                $data = LocationShortResource::collection($parks)->response()->getData();
            } else {
                return YResponse::json(message: 'Invalid slug', status: 400);
            }
        } else if (
            in_array($slug, [
                'feature',
                'category'
            ])
        ) {
            if (isset($mapping[$slug])) {
                [$model, $resource, $relation, $rel] = $mapping[$slug];
                $parksIds = Parks::whereHas($rel, function ($query) use ($type, $slug) {
                    $query->whereHas($slug, function ($query) use ($type) {
                        $query->where('slug', 'like', "%$type%");
                    });
                })->pluck('id');
                $parks = Parks::whereIn('id', $parksIds)
                    ->selectRaw("city, city_slug, state, state_slug, country, country_short_name, COUNT(*) as parks_count")
                    ->groupBy('city')
                    // ->havingRaw('COUNT(*) >= 10')
                    // ->orderByDesc('parks_count')
                    ->orderBy('city')
                    ->paginate($perPage)
                    ->withQueryString();
                $data = LocationShortResource::collection($parks)->response()->getData();
            } else {
                return YResponse::json(message: 'Invalid type', status: 400);
            }
        }

        return YResponse::json(data: $data);
    }

    public function getParksList(Request $request)
    {
        $search = $request->search;
        $query = Parks::with('park_images')->where('active', 1)->select('id', 'name', 'slug', 'city', 'city_slug', 'state', 'state_slug', 'country', 'country_short_name');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search . '%');
            });
        }

        $parks = $query->orderBy('name')->paginate($request->get('per_page', 15))->withQueryString();
        $data = new ParkCollection($parks);

        return YResponse::json(data: $data->response()->getData());
    }
}
