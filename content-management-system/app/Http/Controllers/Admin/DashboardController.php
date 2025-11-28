<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Weather;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DeleteAccountRequest;
use App\Models\Feature;
use App\Models\ParkImage;
use App\Models\Parks;
use App\Models\Pendingimage;
use App\Models\Rating;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\CssSelector\XPath\Extension\FunctionExtension;

class DashboardController extends Controller
{
    public function index()
    {

        $active_page = "dashboard";
        $page_title = "Dashboard";
        $verified_ratings = Rating::where('is_verified', 1)->count();
        return view('admin.dashboard.index', compact('active_page', 'page_title', 'verified_ratings'));
    }


    public function dashboard_total_count(Request $request)
    {
        if ($request->ajax()) {
            $role = Auth::user()->hasRole('admin');
            $total_parks = Parks::count();

            if ($role != 'admin') {
                $total_parks = Parks::where('created_by_id', Auth::user()->id)->count();
            }
            $total_users = User::role('user')->count();
            $total_categories = Category::count();
            $total_pending_reviews = Rating::where('is_verified', 0)->count();
            // $total_pending_images = ParkImage::whereNotNull('user_id')->where('is_verified', 0)->where('is_archived',0)->count();
            $total_pending_images = Pendingimage::get()->sum('total_pending_image');

            $total_features = Feature::count();
            $total_delete_account = DeleteAccountRequest::count();

            return response()->json([
                'data' => [
                    'total_parks' => $total_parks,
                    'total_users' => $total_users,
                    'total_categories' => $total_categories,
                    'total_pending_reviews' => $total_pending_reviews,
                    'total_pending_images' => $total_pending_images,
                    'total_features' => $total_features,
                    'total_delete_account' => $total_delete_account
                ]
            ]);
        }
    }

    public function day_wise_users_and_parks_chart(Request $request)
    {
        if ($request->ajax()) {
            $start_date = Carbon::createFromFormat('Y-m-d', $request->start_date);
            $end_date = Carbon::createFromFormat('Y-m-d', $request->end_date);
            $days = CarbonPeriod::create($start_date, $end_date);

            foreach ($days as $date) {
                $user = User::whereDate('created_at', $date->setTimezone(Auth::user()->timezone))->role('user')->count();
                $park = Parks::where('active', 1)->whereDate('created_at', $date->setTimezone(Auth::user()->timezone))->count();

                $users[] = $user;
                $parks[] = $park;
                $dates[] = $date->format('d M');
            }
            $data = [
                "labels" => $dates,
                'parks' => $parks,
                'users' => $users,

            ];
            return response()->json($data);
        }
    }

    public function top_five_parks(Request $request)
    {
        if ($request->ajax()) {

            $parks = Parks::where('active', 1)->whereHas('ratings', function ($q) {
                $q->where('is_verified', 1)->orderBy('rating', 'DESC');
            })->limit(5)->get();

            $html = "";
            foreach ($parks as $park) {
                $park_image_1 = $park->park_images()->where('set_as_banner', '1')->first();
                $image =  $park_image_1 ?  $park_image_1->media->full_path : asset('images/default.jpg');
                $avg_ratings = number_format((float)$park->ratings()->where('is_verified', 1)->avg('rating'), 1, '.', '');
                $number_of_ratings = $park->ratings()->where('is_verified', 1)->count('rating');
                $link = route('admin.park.details', $park->id);
                $rating = $avg_ratings;
                $rating_section = View::make('components.admin.ratingcomponent', compact('rating'))->render();
                $html .= "<div class='row mb-3'>
                <div class='col-md-9'>
                    <div class='d-flex'>
                        <div class='avatar flex-shrink-0 me-3'>
                            <img src='$image' height='50px' width='50px'
                                style='border-radius: 10px;'>
                        </div>
                        <div>
                            <h6 class='mb-0' style='text-transform:none !important'>" . ucfirst($park->name) . "</h6>
                            " . $rating_section . "<small style='vertical-align:-2px;' class='text-muted'>$avg_ratings
                                (<i class='tf-icons bx bxs-user' style='font-size:1rem; vertical-align:-2px;'></i> $number_of_ratings)</small>
                        </div>
                    </div>
                </div>

                <div class='col-md-3'>
                    <a href='$link'><small class='btn btn-primary '>View</small></a>
                </div>
            </div>";
            }
            return $html;
        }
    }

    public function top_five_users(Request $request)
    {
        if ($request->ajax()) {

            $Users = User::role('user')->whereHas('ratings', function ($q) {
                $q->where('is_verified', 1)->orderBy('rating', 'DESC');
            })->limit(5)->get();

            $html = "";
            foreach ($Users as $user) {

                $image =  $user->image ? $user->image->full_path : asset('images/user.svg');

                $link = route('admin.user.view', $user->id);
                $html .= "<div class='row mb-3'>
                <div class='col-md-9'>
                    <div class='d-flex'>
                        <div class='avatar flex-shrink-0 me-3'>
                            <img src='$image' height='50px' width='50px'
                                style='border-radius: 10px;'>
                        </div>
                        <div class='mt-2'>
                            <h6 class='mb-0' style='text-transform:none !important'>" . ucfirst($user->name) . "</h6>
                        </div>
                    </div>
                </div>

                <div class='col-md-3'>
                    <a href='$link'><small class='btn btn-primary '>View</small></a>
                </div>
                </div>";
            }
            return $html;
        }
    }
}
