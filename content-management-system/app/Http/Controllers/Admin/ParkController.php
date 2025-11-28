<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\YResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\admin\ParkAvailabilityResource;
use App\Models\Category;
use App\Models\Feature;
use App\Models\FeatureType;
use App\Models\Location;
use App\Models\Media;
use App\Models\ParkAvailability;
use App\Models\ParkCategories;
use App\Models\Parks;
use App\Models\Subcategory;
use App\Models\ParkImage;
use App\Models\Rating;
use App\Models\User;
use App\Services\RevalidateApiService;
use App\Traits\CommonTraits;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class ParkController extends Controller
{
    use CommonTraits;

    protected $revalidateApi;

    public function __construct(RevalidateApiService $revalidateApi)
    {
        $this->revalidateApi = $revalidateApi;
    }

    public function index(Request $request)
    {
        $data['user'] = $user = $request->user();
        if (!$user->can('park-show')) {
            abort(404);
        }
        $data['active_page'] = "park";
        $data['page_title'] = "Parks";
        $data['custom_headings'] = "Parks";
        $data['cities'] = Parks::select('city', 'state', 'country')->distinct()->get();
        $data['seo_features'] = $this->topFeatureList(true);
        $accessUsersEmail = [
            "sonali.temani@gmail.com",
            "priyanka@pairroxz.com"
        ];
        $authEmail = auth()->user()->email;
        $data['is_show_filter'] = in_array($authEmail, $accessUsersEmail);
        return view('admin.park.index', $data);
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
        $cityData = request('city');
        $seo_Feature = request('seo_feature');
        $user = $request->user();

        $query = Parks::query();

        if ($user->hasRole('subadmin')) {
            $query = $query->where('created_by_id', $user->id);
        }

        if ($request->filterVal) {
            $filter_val = $request->filterVal;
            foreach ($filter_val as $val) {
                if ($val == 'with_images') {
                    $query->whereHas('park_images');
                } else if ($val == 'without_images') {
                    $query->doesntHave('park_images');
                }

                if ($val == 'active_parks') {
                    $query->where('active', 1);
                } else if ($val == 'inactive_parks') {
                    $query->where('active', 0);
                }
            }
        }

        $recordsTotal = $query->count();

        if (isset($search['value'])) {
            $query->Where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search['value'] . '%')
                    ->orWhere('address', 'like', '%' . $search['value'] . '%');
            });
        }

        if ($cityData) {

            list($city, $state, $country) = explode(',', $cityData);

            $query->Where(function ($q) use ($city) {
                $q->whereCity($city);
            });

            $query->Where(function ($q) use ($state) {
                $q->whereState($state);
            });

            $query->Where(function ($q) use ($country) {
                $q->whereCountry($country);
            });
        }

        if ($seo_Feature) {
            $query->Where(function ($q) use ($seo_Feature) {
                $q->whereHas('features.feature', fn($q) => $q->where('name', $seo_Feature));
                $q->orWhereHas('features.feature_type', fn($q) => $q->where('name', $seo_Feature));
            });
        }

        $recordsFiltered = $query->count();

        $data = $query
            ->orderBy($order_by, $order_dir)
            ->skip($skip)
            ->take($take)
            ->get();

        foreach ($data as &$d) {
            $detailsRoute = route('admin.park.details', $d->id);

            $park_image_1 = $d->park_images()->where('set_as_banner', '1')->first();
            $park_images = ParkImage::where('park_id', $d->id)
                ->where('status', '1')->get();

            $image = $park_image_1 ? $park_image_1->media->full_path : asset('images/default.jpg');
            $d->name = "<img src=' $image' alt='Logo' height='50px' width='50px' style='border-radius: 10px;'> <a class='text-reset' style='text-decoration:none;' href='$detailsRoute' rel='tooltip' title='Go To Details'>" . ucfirst($d->name) . "</a>";
            $d->city = $d->city ?? 'N/A';

            $editRoute = route('admin.park.edit', $d->id);
            $deleteRoute = $user->hasRole('admin') ? route('admin.delete.park', $d->id) : null;

            $imageEditRoute = $imageUplodRoute = null;
            if (count($park_images) == 0) {
                $imageUplodRoute = route('admin.park.image.upload', $d->id);
            } else {
                $imageEditRoute = route('admin.park.image.edit', $d->id);
            }

            $statusRoute = $user->hasRole('admin') ? route('admin.update.park.status', $d->id) : null;
            $status = ($d->active == 1) ? 'checked' : '';
            $d->action = View::make('components.admin.actioncomponent', compact('editRoute', 'deleteRoute', 'imageEditRoute', 'imageUplodRoute', 'statusRoute', 'status'))->render();
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
        if (!$user->can('park-create')) {
            abort(404);
        }
        $active_page = "park";
        $page_title = "Create Park";
        $park = null;
        $categories = Category::where('active', 1)->get();
        $selected_categories = [];
        $selected_subcategories = [];
        $subcategories = [];
        $feature_types = FeatureType::where('active', 1)->get();
        $selected_feature_types = [];
        $selected_features = [];
        $all_features = FeatureType::where('active', 1)->with('features')->get();

        $features = [];
        $media = null;

        return view('admin.park.create', compact('active_page', 'page_title', 'park', 'categories', 'selected_categories', 'media', 'selected_subcategories', 'subcategories', 'feature_types', 'selected_feature_types', 'selected_features', 'features', 'all_features'));
    }

    public function edit(Request $request, Parks $park)
    {
        $user = $request->user();
        // if (!$user->can('park-edit')) {
        //     abort(404);
        // }
        $active_page = "park";
        $page_title = "Edit Park";
        if ($user->hasRole('subadmin')) {
            if ($park->created_by_id != $user->id) {
                return back()->with('error', __('admin.park_not_found'));
            }
        }
        $selected_categories = $park->categories()->pluck('category_id')->toArray();
        $subcategories = Subcategory::whereIn('category_id', $selected_categories)->with('category')->get();
        $selected_subcategories = $park->categories()->pluck('subcategory_id')->toArray();
        // $categories = Category::where('active', 1)->get();
        $categories = Category::get();

        $feature_types = FeatureType::where('active', 1)->get();
        $selected_feature_types = $park->features()->pluck('feature_type_id')->toArray();
        $selected_features = $park->features()->pluck('feature_id')->toArray();
        $features = Feature::whereIn('feature_type_id', $selected_feature_types)->with('feature_type')->get();
        $all_features = FeatureType::where('active', 1)->with('features')->get();

        if ($park->image_ids) {
            $media = Media::whereIn('id', $park->image_ids)->get();
        } else {
            $media = null;
        }

        if ($request->ajax()) {
            $park_availability = ParkAvailability::where('park_id', $park->id)->get();
            $data = ParkAvailabilityResource::collection($park_availability);
            return response()->json(['data' => $data]);
        }

        return view('admin.park.create', compact(
            'active_page',
            'page_title',
            'park',
            'categories',
            'selected_categories',
            'media',
            'selected_subcategories',
            'subcategories',
            'feature_types',
            'selected_feature_types',
            'selected_features',
            'features',
            'all_features'
        ));
    }
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'images' => ['nullable', 'array', 'between:0,10'],
            'images.*' => ['image', 'mimes:png,jpg', 'max: 1024'],
            'url' => ['nullable', 'url'],
            'description' => ['nullable', 'max:1000'],
            'category_ids' => ['nullable', 'exists:categories,id'],
            'subcategory_ids' => ['nullable', 'exists:subcategories,id'],
            'feature_type_ids' => ['nullable', 'exists:feature_types,id'],
            'feature_ids' => ['nullable', 'exists:features,id'],
            'is_paid' => ['required', 'boolean'],
            'ticket_amount' => ['required_if:is_paid,1'],
            // 'instruction_url'=>['required_if:is_paid,1','url'],
            // 'instruction_url'=>['url'],

            // 'instructions'=>['required_if:is_paid,1','string','max:300'],
            'instructions' => ['nullable', 'string', 'max:300'],


        ], [
            'images.*.max' => 'The size of the image must not be greater than 1 MB',
            'ticket_amount.required_if' => 'The ticket amount is required',
            'instruction_url.required_if' => 'The instruction URL is required',
            'instructions.required_if' => 'The instructions is required',
            'instruction_url.url' => 'Invalid Instruction URL',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
        }

        $backRoute = $request->backRoute;
        $user = $request->user();
        $slug = $this->generateUniqueSlug($request->name, $request->id, Parks::class, 'slug');

        if ($request->id) {
            $park = Parks::find($request->id);
            $message = __('admin.park_update');
            $__park = Parks::where('id', '!=', $request->id)->where('slug', $slug)->first();
            // if ($__park) {
            //     return back()->with('error', __('admin.name_already_exists'));
            // }
            $image_ids = $park->image_ids ?? [];
        } else {
            $park = new Parks;
            $message = __('admin.park_create');
            $__park = Parks::where('slug', $slug)->first();
            // if ($__park) {
            //     return back()->with('error', __('admin.name_already_exists'));
            // }
            $image_ids = [];
        }
        $park->name = $request->name;
        $park->slug = $slug;

        if (empty($request->description)) {
            $park->description = null;
        } else {
            $park->description = $request->description;
        }

        $park->url = $request->url;
        $park->longitude = $request->longitude;
        $park->latitude = $request->latitude;
        $park->address = $request->address;
        $park->country = $request->country;
        $park->country_short_name = $request->country_short_name ?? '';
        $park->state = $request->state;
        $park->state_slug = Str::slug($request->state);
        $park->city = $request->city;
        $park->city_slug = Str::slug($request->city);
        $park->timezone = $request->timezone;
        $park->search_slug = $this->makeSlug($request->country, $request->state, $request->city, $slug);

        $park->is_paid = $request->is_paid;

        if ($park->is_paid) {
            $park->ticket_amount = $request->ticket_amount;
            $park->instruction_url = $request->instruction_url;
            $park->instructions = $request->instructions;
        } else {
            $park->ticket_amount = null;
            $park->instruction_url = null;
            $park->instructions = null;
        }

        if (!$park->created_by_id) {
            $park->created_by_id = $user->id;
        }

        $park->save();

        $this->revalidateApi->revalidatePark($park);

        $location = Location::where('city', 'like', "%$request->city%")
            ->where('city_slug', $park->city_slug)
            ->where('state', 'like', "%$request->state%")
            ->where('state_slug', $park->state_slug)
            ->where('country', 'like', "%$request->country%")
            ->where('country_short_name', $park->country_short_name)
            ->first();

        if (!$location) {
            $locationData = [
                'city' => $request->city,
                'city_slug' => $this->generateUniqueSlug($request->city, null, Location::class, 'city_slug'),
                'state' => $request->state,
                'state_slug' => Str::slug($request->state),
                'country' => $request->country,
                'country_short_name' => $request->country_short_name ?? null,
                'location_longitude' => $request->location_longitude,
                'location_latitude' => $request->location_latitude,
            ];
            Location::insert($locationData);
            $this->revalidateApi->revalidate("/$park->country_short_name/$park->state_slug/$park->city_slug");
        } else {
            // $location->update([
            //     'location_longitude' => $request->location_longitude,
            //     'location_latitude' => $request->location_latitude,
            // ]);
        }

        $park->categories()->delete();
        if ($request->get('subcategory_ids')) {
            foreach ($request->subcategory_ids as $subcategory_id) {
                $subcategory = Subcategory::find($subcategory_id);
                $category = Category::find($subcategory->category_id);
                $park->categories()->create([
                    'category_id' => $subcategory->category_id,
                    'subcategory_id' => $subcategory->id
                ]);
                // $this->revalidateApi->revalidate("/category/$category->slug/$subcategory->slug");
                $this->revalidateApi->revalidateCategory($category);
            }
        }

        if ($request->get('category_ids')) {
            foreach ($request->category_ids as $category_id) {
                $category = Category::find($category_id);
                $park->categories()->firstOrCreate([
                    'category_id' => $category->id
                ]);
                // $this->revalidateApi->revalidate("/category/$category->slug");
                $this->revalidateApi->revalidateCategory($category);
            }
        }

        $park->features()->delete();

        if ($request->get('feature_ids')) {
            foreach ($request->feature_ids as $feature_id) {
                $feature = Feature::find($feature_id);
                $featureType = FeatureType::findOrFail($feature->feature_type_id);
                $park->features()->create([
                    'feature_type_id' => $feature->feature_type_id,
                    'feature_id' => $feature->id
                ]);
                // $this->revalidateApi->revalidate("/feature/$featureType->slug/$feature->slug");
                $this->revalidateApi->revalidateFeature($featureType);
            }
        }

        if ($request->get('feature_type_ids')) {
            foreach ($request->feature_type_ids as $feature_type_id) {
                $featureType = FeatureType::find($feature_type_id);
                $park->features()->firstOrCreate([
                    'feature_type_id' => $featureType->id
                ]);
                // $this->revalidateApi->revalidate("/feature/$featureType->slug");
                $this->revalidateApi->revalidateFeature($featureType);
            }
        }

        if ($request->get('day')) {
            $park->park_availability()->delete();
            $i = 0;

            foreach ($request->day as $day) {
                if ($request->type[$i] == 'custom') {
                    $park->park_availability()->create([
                        'day' => $day,
                        'type' => $request->type[$i],
                        'opening_time' => $request->opening_time[$i],
                        'closing_time' => $request->closing_time[$i],
                    ]);
                } else {
                    $park->park_availability()->create([
                        'day' => $day,
                        'type' => $request->type[$i],
                    ]);
                }
                $i++;
            }
        } else {
            $park->park_availability()->delete();
        }

        $previousRoute = app('router')->getRoutes()->match(app('request')->create(url()->previous()))->getName();

        if ($backRoute == 'admin.subadmin.details') {
            return redirect()->to($request->previousUrl)->with('success', $message);
        }

        if (($previousRoute == 'admin.park.edit')) {
            return redirect()->route('admin.park.index')->with('success', $message);
        }

        $park_id = $park->id;
        $park_image = ParkImage::where('park_id', $park->id)->where('status', '1')->get();

        if (!(Auth::user()->hasRole(['admin']))) {
            return redirect()->route('admin.park.index')->with('success', $message);
        }

        if (!empty($park_image)) {
            return redirect()->route('admin.park.edit', $park->id)->with('success', $message)->with('image_edit', $park_id);
        }

        return redirect()->route('admin.park.edit', $park->id)->with('success', $message)->with('upload_image', $park_id);
    }

    public function delete_park(Request $request, Parks $park)
    {
        $user = $request->user();
        if (!$user->can('park-delete')) {
            abort(404);
        }
        if ($request->ajax()) {

            if ($user->hasRole('subadmin')) {
                if ($park->first()->created_by_id != $user->id) {
                    return YResponse::json(message: __('admin.park_not_found'), status: 404);
                }
            }

            $park->first()->categories()->delete();
            $park->first()->features()->delete();
            $park->first()->park_images()->delete();
            $park->first()->park_availability()->delete();
            $park->first()->bookmark()->delete();
            $park->first()->ratings()->delete();

            if ($park->delete()) {
                $this->revalidateApi->revalidatePark($park);
                return response()->json([
                    'msg' => 'Park deleted successfully',
                    'status' => 1
                ]);
            }
        }
    }

    public function update_status(Request $request, Parks $park)
    {
        $user = $request->user();
        if (!$user->can('park-active')) {
            abort(404);
        }
        if ($request->ajax()) {
            $park_status = $park->update(['active' => (int) $request->status]);
            $msg = 'Park has been activated';
            if ($request->status == 0) {
                $msg = "Park has been inactivated.";
            }

            $this->revalidateApi->revalidatePark($park);

            if ($park_status) {
                return response()->json([
                    'msg' => $msg
                ]);
            }
        };
    }


    public function details(Request $request, Parks $park)
    {
        $user = $request->user();
        if (!$user->can('park-show')) {
            abort(404);
        }

        if (!$park) {
            return redirect()->back()->with('error', __('admin.park_not_found'));
        }

        function custom_time($opening_time, $closing_time)
        {
            return Carbon::parse($opening_time)->format('g:i A') . " To " . Carbon::parse($closing_time)->format('g:i A');
        }

        $active_page = "park";
        $page_title = "Park";
        $breadcrumbs = collect([['route' => route('admin.park.index'), 'name' => 'Parks']]);


        $standalone = ParkCategories::Where('park_id', $park->id)->whereHas('category', function ($q) {
            $q->where('type', 'no-child');
        })->get();

        $parentCategory = ParkCategories::Where('park_id', $park->id)->whereNotNull('subcategory_id')->count();
        $selected_categories = $park->categories()->pluck('category_id')->toArray();
        $subcategories = Subcategory::whereIn('category_id', $selected_categories)->with('category')->get();
        $selected_subcategories = $park->categories()->pluck('subcategory_id')->toArray();
        $categories = Category::where('active', 1)->get();
        $feature_types = FeatureType::where('active', 1)->get();
        $selected_feature_types = $park->features()->pluck('feature_type_id')->toArray();
        $selected_features = $park->features()->pluck('feature_id')->toArray();
        $features = Feature::whereIn('feature_type_id', $selected_feature_types)->with('feature_type')->get();
        $availabilities = collect($park->park_availability()->get());
        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        $availability_days = [];

        foreach ($days as $day) {
            $availability_days[$day] = ($availabilities->where('day', $day)->value('type') != 'custom') ? ucwords(Str::replace('_', ' ', $availabilities->where('day', $day)->value('type'))) :
                custom_time($availabilities->where('day', $day)->value('opening_time'), $availabilities->where('day', $day)->value('closing_time'));
        }

        $avg_ratings = number_format((float) $park->ratings()->where('is_verified', 1)->avg('rating'), 1, '.', '');
        $ratings = Rating::where('park_id', $park->id)->where('is_verified', 1)->orderBy('rating', 'desc')->get();
        $availability_days = collect($availability_days);
        return View('admin.park.view', compact(
            'active_page',
            'page_title',
            'breadcrumbs',
            'park',
            'standalone',
            'categories',
            'subcategories',
            'selected_categories',
            'selected_subcategories',
            'feature_types',
            'selected_feature_types',
            'selected_features',
            'features',
            'parentCategory',
            'availability_days',
            'days',
            'avg_ratings',
            'ratings'
        ));
    }

    public function pending_user_images(Request $request, Parks $park)
    {
        if (!$park) {
            return back()->with('error', __('admin.park_not_found'));
        }
        $parkimages = Parkimage::where('park_id', $park->id)->whereNotNull('user_id')->get();
        $user_id = collect($parkimages)->pluck('user_id')->unique()->toArray();
        if ($request->ajax()) { {
                $_order = request('order');
                $_columns = request('columns');
                $order_by = $_columns[$_order[0]['column']]['name'];
                $order_dir = $_order[0]['dir'];
                $search = request('search');
                $skip = request('start');
                $take = request('length');

                $query = User::select(
                    '*',
                    DB::Raw('(select count(*) from park_images where park_images.user_id=users.id and park_images.park_id="' . $park->id . '") as total_images'),
                    DB::Raw('(select count(*) from park_images where park_images.user_id=users.id and park_images.park_id="' . $park->id . '" and is_verified=0) as unverified_images'),
                    DB::Raw('(select count(*) from park_images where park_images.user_id=users.id and park_images.park_id="' . $park->id . '" and is_verified=1) as verified_images')
                )->whereIn('id', $user_id);
                $recordsTotal = $query->count();
                $park_id = $park->id;
                if (isset($search['value'])) {
                    $total_images = DB::table('users')->select(
                        '*',
                        DB::Raw('(select count(*) from park_images where park_images.user_id=users.id and park_images.park_id="' . $park->id . '") as total_images')
                    )
                        ->whereRaw("total_images LIKE '%" . $search['value'] . "%' ");
                    $query->Where(function ($q) use ($search, $park_id) {
                        $q->whereRaw('name LIKE "%' . $search['value'] . '%" ');
                    })->union($total_images);

                    // $query->having('total_images',function($query){
                    //                           $q->whereRaw('name LIKE "%' . $search['value'] . '%" ');
                    // })
                }

                $recordsFiltered = $query->count();
                $data = $query
                    ->orderBy($order_by, $order_dir)->skip($skip)->take($take)->get();

                foreach ($data as &$d) {
                    $image = $d->image->full_path ?? asset('images/user.svg');
                    $d->name = "<img src=' $image' alt='Logo' height='50px' width='50px' style='border-radius: 10px;'> <a class='text-reset' style='text-decoration:none;' href='" . route('admin.user.view', $d->id) . "'>" . ucfirst($d->name) . "</a>";
                    $total_unverified_images = $d->unverified_images;
                    $total_verified_images = $d->verified_images;
                    $pending_icon = ($total_unverified_images > 0) ? "<span class='text-danger'><i class='bx bxs-time' style='font-size:1.2rem;'></i></span>" : null;
                    $verified_icon = ($total_verified_images > 0) ? "<span class='text-primary'><i class='bx bx-check' style='font-size:1.5rem; font-weight: bolder;
                        vertical-align: -3px;'></i></span>" : null;

                    $d->unverified_images = $total_unverified_images . " " . $pending_icon;
                    $d->verified_images = $total_verified_images . "" . $verified_icon;
                    $d->total_images = $d->total_images;
                    $editRoute = route("admin.park.unverified_images", [$park->id, $d->id]);
                    $d->action = View::make('components.admin.actioncomponent', compact('editRoute'))->render();
                }

                return [
                    "draw" => request('draw'),
                    "recordsTotal" => $recordsTotal,
                    'recordsFiltered' => $recordsFiltered,
                    "data" => $data,
                ];
            }
        }
        $active_page = "park";
        $page_title = "Parks";
        $custom_headings = "Parks";
        $breadcrumbs = collect([['route' => route('admin.park.user.pending.images'), 'name' => 'Pending Image(s)'],]);

        return view('admin.park.users_images', compact('active_page', 'page_title', 'custom_headings', 'breadcrumbs', 'park'));
    }

    public function show_pending_datatable(Request $requeset) {}

    public function unverify_images(Request $request)
    {
        if ($request->ajax()) {
            Parkimage::whereIn('id', $request->id)->update(['is_verified' => 0]);
            return response()->json(['msg' => 'Image(s) is unverifed successfully', 'status' => 1]);
        }
    }

    public function delteUserReview(Request $request)
    {
        if ($request->ajax()) {
            if (!empty($request->user_id)) {
                Rating::where('user_id', $request->user_id)->where('park_id', $request->park_id)->delete();
            }
            $rating = Rating::where('park_id', $request->park_id)->where('is_verified', 1);
            $ratings = $rating->clone()->limit(10)->orderBy('rating', 'desc')->latest('created_at')->get();

            $avg_rating = number_format((float) $rating->clone()->avg('rating'), 1, '.', '');
            $total_rating = $rating->count();
            $average_rating_html = View::make('components.admin.averagerating', compact('avg_rating', 'total_rating'))->render();
            $html = View::make('components.admin.reviewcomponent', compact('ratings'))->render();

            return response()->json([
                'msg' => 'Review has been delete successfully',
                'status' => 1,
                'html' => $html,
                'average_rating_html' => $average_rating_html,
                'total_rating' => $total_rating,
            ]);
        }
    }

    public function loadMoreReview(Request $request)
    {
        if ($request->ajax()) {
            // $ratings = Rating::where('park_id', $request->park_id)->limit(50)->orderBy('rating', 'desc')->get();
            $rating = Rating::where('park_id', $request->park_id)->where('is_verified', 1);
            $ratings = (clone $rating)->offset($request->offset)->limit(10)->orderBy('rating', 'desc')->get();
            $more_data = (clone $rating)->offset($request->offset + 10)->limit(10)->orderBy('rating', 'desc')->get();
            $html = View::make('components.admin.reviewcomponent', compact('ratings'))->render();



            return response()->json([
                'html' => $html,
                'more_data' => count($more_data),
            ]);
        }
    }

    public function reviews_dt_list(Request $request, Parks $park)
    {
        if (!$park) {
            return back()->with('error', __('admin.park_not_found'));
        }

        if ($request->ajax()) { {
                $_order = request('order');
                $_columns = request('columns');
                $order_by = $_columns[$_order[0]['column']]['name'];
                $order_dir = $_order[0]['dir'];
                $search = request('search');
                $skip = request('start');
                $take = request('length');

                $query = Rating::where('park_id', $park->id);
                $recordsTotal = $query->count();

                if (isset($search['value'])) {
                    $query->WhereHas('park', function ($q) use ($search) {
                        $q->whereRaw('name LIKE "%' . $search['value'] . '%" ');
                    });
                }

                $recordsFiltered = $query->count();

                $data = $query
                    ->orderBy('id', 'DESC')->skip($skip)->take($take)->get();


                foreach ($data as &$d) {
                    $user = $d->user;
                    // $parkImg = $d->user->image()->where('set_as_banner', '1')->first();
                    $image = $user->image ? $user->image->full_path : asset('images/user.svg');
                    $d->name = "<img src=' $image' alt='Logo' height='50px' width='50px' style='border-radius: 10px;'> <a class='text-reset' style='text-decoration:none;' href='" . route('admin.user.view', $user->id) . "' rel='tooltip' title='Go To Details'>" . $user->name . "</a>";
                    $rating = $d->rating;
                    $ratings_star = View::make('components.admin.ratingcomponent', compact('rating'))->render();
                    $d->review = Str::limit($d->review, 25, '...');
                    $d->ratings = $ratings_star;
                    $tooltipTitle = "Verify Review";
                    $infoBtn = true;
                    $detailsRoute = route('admin.park.pending.reviews', $d->id);
                    $detailsRouteTooltipTitle = "Verify Review";
                    $other = !$d->is_verified ? "<a href='$detailsRoute' rel='tooltip' class='btn btn-icon ml-1 btn-primary' title='Verify Review'><span class='tf-icons bx bx-info-circle'></span></a>" : '';
                    $deleteRoute = $d->is_verified ? route('admin.park.delete.review', $d->id) : '';
                    $d->action = View::make('components.admin.actioncomponent', compact('detailsRouteTooltipTitle', 'other', 'deleteRoute'))->render();
                }

                return [
                    "draw" => request('draw'),
                    "recordsTotal" => $recordsTotal,
                    'recordsFiltered' => $recordsFiltered,
                    "data" => $data,
                ];
            }
        }
    }
}
