<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ParkImage;
use App\Models\Parks;
use App\Models\Rating;
use App\Models\User;
use App\Services\RevalidateApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class ParkReviewController extends Controller
{

    protected $revalidateApi;

    public function __construct(RevalidateApiService $revalidateApi)
    {
        $this->revalidateApi = $revalidateApi;
    }

    public function view()
    {
        $active_page = "park";
        $page_title = "Parks";
        return view('admin.park.pending_reviews.index', compact('active_page', 'page_title'));
    }

    public function pending_reviews(Rating $rating)
    {
        if (!$rating) {
            return redirect()->back()->with('error', __('admin.review_not_found'));
        }

        $active_page = "park";
        $page_title = "Parks";
        $breadcrumbs = [['route' => route('admin.park.review'), 'name' => 'Pending Reviews']];
        return view('admin.park.pending_reviews.view', compact('active_page', 'page_title', 'breadcrumbs', 'rating'));
    }

    public function dt_list(Request $request)
    {

        if ($request->ajax()) {
            $_order = request('order');
            $_columns = request('columns');
            // $order_by = "user_id";
            $order_by = $_columns[$_order[0]['column']]['name'];
            $order_dir = $_order[0]['dir'];
            $search = request('search');
            $skip = request('start');
            $take = request('length');
            $user = $request->user();

            $query = Rating::with(['park:id,name', 'user:id,name'])
                ->select(
                    'park_id',
                    'user_id',
                    'is_verified',
                    'id',
                    'review',
                    'rating',
                    DB::raw('(select name from parks where parks.id=ratings.park_id) as name'),
                    DB::raw('(select name from users where users.id=ratings.user_id) as username')
                )
                ->with('user', 'user.image')
                ->where('is_verified', 0);

            $recordsTotal = $query->count();


            if (isset($search['value'])) {
                $query->WhereHas('park', function ($q) use ($search) {
                    $q->whereRaw('name LIKE "%' . $search['value'] . '%" ');
                })->orWhereHas('user', function ($q) use ($search) {
                    $q->whereRaw('name LIKE "%' . $search['value'] . '%" ');
                })->where('is_verified', 0);
            }
            $recordsFiltered = $query->count();
            // $data = $query->orderBy('id','desc')->skip($skip)->take($take)->get();
            $data = $query
                // ->orderBy($order_by, $order_dir)
                ->skip($skip)->take($take)
                ->orderByRaw("$order_by $order_dir")->get();


            foreach ($data as &$d) {
                $parkImg = $d->park->park_images()->where('set_as_banner', '1')->first();
                $image = $parkImg ? $parkImg->media->full_path : asset('images/default.jpg');
                $d->name = "<img src=' $image' alt='Logo' height='50px' width='50px' style='border-radius: 10px;'> <a class='text-reset' style='text-decoration:none;' href='" . route('admin.park.details', $d->park->id) . "' rel='tooltip' title='Go To Details'>" . $d->park->name . "</a>";

                $image = (!empty($d->user->image_id)) ? $d->user->image->full_path : asset('images/user.svg');
                $d->username = "<div class='d-flex'>
                <a href='" . route('admin.user.view', $d->user->id) . "'>
                <img src='" . $image .
                    "'alt='Logo' height='40px' width='40px' style='border-radius: 10px'><span class='ml-2'>" . $d->user->name . "</span></a></div>";

                $rating = $d->rating;
                $ratings_star = View::make('components.admin.ratingcomponent', compact('rating'))->render();

                $d->review = Str::limit($d->review, 25, '...');
                $d->rating = $ratings_star;

                $tooltipTitle = "Verify Review";
                $infoBtn = true;
                $detailsRoute = route('admin.park.pending.reviews', $d->id);
                $detailsRouteTooltipTitle = "Verify Review";
                $deleteRoute = route('admin.park.delete.review', $d->id);
                $d->action = View::make('components.admin.actioncomponent', compact('detailsRouteTooltipTitle', 'detailsRoute', 'deleteRoute'))->render();
            }
            return [
                "draw" => request('draw'),
                "recordsTotal" => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                "data" => $data,
            ];
        }
    }

    public function verify_review(Request $request)
    {
        if ($request->ajax()) {
            $park = Parks::findOrFail($request->park_id);
            if ($request->status == 'verify') {
                $update = Rating::where('user_id', $request->user_id)->where('park_id', $request->park_id)->update(['is_verified' => 1]);
            } else {
                $update = Rating::where('user_id', $request->user_id)->where('park_id', $request->park_id)->update(['is_verified' => 0]);
            }
            $this->revalidateApi->revalidatePark($park);
            return response()->json(['msg' => '', 'status' => $update]);
        }
    }

    public function delete_review(Request $request, Rating $rating)
    {
        if ($request->ajax()) {
            $park = Parks::findOrFail($request->park_id);
            $this->revalidateApi->revalidatePark($park);
            $delete = $rating->delete();
            return response()->json(['msg' => __('admin.review_delete_success'), 'status' => $delete]);
        }
    }
}
