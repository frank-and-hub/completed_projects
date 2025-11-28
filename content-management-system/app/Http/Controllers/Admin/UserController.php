<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\DeleteAccountRequest;
use App\Models\ParkImage;
use App\Models\Parks;
use App\Models\Pendingimage;
use App\Models\Rating;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;


class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->can('users-show')) {
            abort(404);
        }
        $active_page = "user";
        $page_title = "Users";
        return view('admin.user.index', compact('active_page', 'page_title'));
    }

    public function dt_list()
    {
        $_order = request('order');
        $_columns = request('columns');
        $order_by = $_columns[$_order[0]['column']]['name'];
        $order_dir = $_order[0]['dir'];
        $search = request('search');
        $skip = request('start');
        $take = request('length');
        $query = User::role('user');

        $recordsTotal = $query->count();

        if (isset($search['value'])) {
            $query->Where(function ($q) use ($search) {
                $q->whereRaw("email LIKE '%" . $search['value'] . "%' OR name LIKE '%" . $search['value'] . "%'")
                    ->orWhereRaw("username LIKE '%" . $search['value'] . "%'");
            });
        }

        $recordsFiltered = $query->count();

        $data = $query
            ->orderBy($order_by, $order_dir)->skip($skip)->take($take)->get();

        foreach ($data as $d) {
            $detailsRoute = route('admin.user.view', $d->id);
            $image = $d->image_id ? $d->image->full_path : asset('images/user.svg');

            $email_verified = $d->email_verified_at ? "<span class='bx bxs-badge-check' style='color:#48D33A; font-size:1.5rem;'></span>" : null;
            $d->email =  $d->email ? "<div class='d-flex'>" . Str::limit($d->email, 50) . ' ' . $email_verified . "</div>" : "N/A";
            $d->name =  "<div class='d-flex'>
            <a href='$detailsRoute'>
            <img src='" . $image  .
                "'alt='Logo' height='40px' width='40px' style='border-radius: 10px'><span class='ml-2'>" . ucfirst($d->name) . "</span></a></div>";
            $d->username = $d->username ?? 'N/A';
            $d->created = Carbon::parse($d->created_at)->setTimezone(Auth::user()->timezone)->format('d M y h:i A');
            $statusRoute = route('admin.user.active.inactive', $d->id);
            $status = ($d->is_active) ? 'checked' : '';
            $id = $d->id;
            $d->action = View::make('components.admin.actioncomponent', compact('detailsRoute', 'statusRoute', 'status', 'id'))->render();
        }

        return [
            "draw" => request('draw'),
            "recordsTotal" => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            "data" => $data,
        ];
    }

    public function bookmark_dt_list(Request $request, $id)
    {

        $_order = request('order');
        $_columns = request('columns');
        $order_by = $_columns[$_order[0]['column']]['name'];
        $order_dir = $_order[0]['dir'];
        $search = request('search');
        $skip = request('start');
        $take = request('length');
        $user = $request->user();

        $query = Bookmark::where('user_id', $id);

        $recordsTotal = $query->count();

        if (isset($search['value'])) {
            $query->WhereHas('park', function ($q) use ($search) {
                $q->whereRaw('name LIKE "%' . $search['value'] . '%" ');
            });
        }

        $recordsFiltered = $query->count();
        $data = $query
            ->orderBy($order_by, $order_dir)->skip($skip)->take($take)->get();
        foreach ($data as &$d) {
            $park_image_1 = $d->park->park_images()->where('set_as_banner', '1')->first();

            $image =  $park_image_1 ?  $park_image_1->media->full_path : asset('images/default.jpg');

            // $image = $d->park->park_images()->where('set_as_banner', '1')->first() ?? asset('images/default.jpg');

            $d->bookmark_type_ = $d->bookmarkType->type;
            $detailsRoute = route('admin.park.details', $d->park->id);

            $d->name =  "<a href='$detailsRoute'><img src='$image' alt='Logo' height='50px' width='50px' style='border-radius: 10px;'> " . ucfirst($d->park->name) . "</a>";
            $d->created = Carbon::parse($d->created_at)->setTimezone(Auth::user()->timezone)->format('d M y h:i A');
            // $d->action = View::make('components.admin.actioncomponent', compact('detailsRoute'))->render();
        }

        return [
            "draw" => request('draw'),
            "recordsTotal" => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            "data" => $data,
        ];
    }


    public function view(User $user)
    {
        $active_page = "user";
        $page_title = "User Details";
        if (empty($user)) {
            return redirect()->back()->with('error',  __('admin.user_not_found'))
                ->withInput();
        }
        $userId = $user->id;
        $parkimages = ParkImage::where('user_id', $userId)->orderBy('id', 'DESC')->get();
        $userpark = Parkimage::where('user_id', $userId)->get()->groupBy('park_id');

        return view('admin.user.view', compact('active_page', 'user', 'page_title', 'parkimages', 'userpark'));
    }


    public function verifyImges(Request $request)
    {
        if ($request->ajax()) {
            Parkimage::where('user_id', $request->user_id)->where('park_id', $request->park_id)->update(['is_verified' => 0]);
            if (!empty($request->id)) {
                ParkImage::WhereIn('id', $request->id)->update(['is_verified' => 1]);
            }
            return response()->json(['msg' => 'Image is verified successfully', 'status' => '1']);
        }
    }

    public function users_park_images(Request $request)
    {
        if ($request->ajax()) {
            $parkImages = ParkImage::where('park_id', $request->park_id)
                ->where('user_id', $request->user_id)->orderBy('id', 'DESC')->get();
            $html = '';
            foreach ($parkImages as $parkimage) {
                if ($parkimage->is_verified == 1) {
                    $verify_check_mark_html = "<div class='check-mark'>
                    <span class='bx bxs-badge-check'
                        style='color:#48D33A; font-size:2rem;'></span>
                    <input type='hidden' value='" . $parkimage->id . "' class='parkimage_id'
                        checked-mark='true'>
                </div>";
                } else {
                    $verify_check_mark_html = "<div class='check-mark d-none'>
                    <span class='bx bxs-badge-check'
                        style='color:#48D33A; font-size:2rem;'></span>
                    <input type='hidden' value='" . $parkimage->id . "' class='parkimage_id'>
                </div>";
                }


                $html .= "
                <div class='image-preview-box ml-2 mb-3 mt-3 image_box' style='position: relative'>
                    <img draggable='false' class='fill-img' src='" . $parkimage->media->full_path . "'>" . $verify_check_mark_html . "
                </div>
            ";
            }
            return response()->Json([
                'data' => $html
            ]);
        }
    }

    public function unverified_park_images_dt_list(Request $request, User $user)
    {
        $parkimages = Parkimage::where('user_id', $user->id)->where('status', '1')->get();
        $park_id = collect($parkimages)->pluck('park_id')->unique()->toArray();
        if ($request->ajax()) { {
                $_order = request('order');
                $_columns = request('columns');
                $order_by = $_columns[$_order[0]['column']]['name'];
                $order_dir = $_order[0]['dir'];
                $search = request('search');
                $skip = request('start');
                $take = request('length');

                $query = Parks::whereIn('id', $park_id);
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

                    $park_image = $d->park_images()->where('set_as_banner', '1')->first();
                    $image =  $park_image ?  $park_image->media->full_path : asset('images/default.jpg');
                    $d->name = "<img src=' $image' alt='Logo' height='50px' width='50px' style='border-radius: 10px;'> <a class='text-reset' style='text-decoration:none;' href='" . route('admin.park.details', $d->id) . "'>" . ucfirst($d->name) . "</a>";

                    $d->unverified_images = $d->park_images()->where('is_verified', 0)->where('user_id', $user->id)->count();
                    $d->verified_images = $d->park_images()->where('is_verified', 1)->where('user_id', $user->id)->count();
                    $d->total_images = $d->park_images()->where('user_id', $user->id)->count();

                    $is_archieved =  ($d->park_images()->where('user_id', $user->id)->where('is_archived', 1)->count() > 0) ? true : false;
                    $tooltipTitle = "Verify Image";
                    $infoBtn = true;
                    $detailsRoute = route('admin.park.pendingimage.view', [$d->id, $user->id]);
                    $details = $other = '';
                    $detailsRouteTooltipTitle = "Verify Pending Image";

                    if ($is_archieved) {
                        $other = "<button class='btn btn-icon ml-1 btn-primary' rel='tooltip' title='Unarchive'
                        onmouseenter ='EnableTooltip(this)' onmouseover='EnableTooltip(this)'ommouseout='EnableTooltip(this)'
                         onclick='unarchive($d->id,$user->id,this)'><i class='bx bxs-archive-out'></i></button>";
                    }
                    // else {
                    //     $details =  "<a href='$detailsRoute' rel='tooltip' class='btn btn-icon ml-1 btn-primary'
                    //     title='Verify Image'>
                    //     <span class='tf-icons bx bx-info-circle'></span>
                    // </a>";
                    // }
                    $details =  "<a href='$detailsRoute' rel='tooltip' class='btn btn-icon ml-1 btn-primary'
                        title='Verify Image'>
                        <span class='tf-icons bx bx-info-circle'></span>
                    </a>";

                    $other =  $other . $details;
                    $d->action = View::make('components.admin.actioncomponent', compact('other'))->render();
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

    public function unachivedImage(Request $request)
    {
        if ($request->ajax()) {

            $parkImage = Parkimage::where('user_id', $request->user_id)->where('park_id', $request->park_id);
            $total_pending_image = $parkImage->clone()->where('is_verified', 0)->count();
            $total_verified_image = $parkImage->clone()->where('is_verified', 1)->count();
            Pendingimage::create([
                'park_id' => $request->park_id,
                'user_id' => $request->user_id,
                'total_pending_image' => $total_pending_image,
                'total_verify_image' => $total_verified_image
            ]);
            $update = ParkImage::where('user_id', $request->user_id)->where('park_id', $request->park_id)->update(['is_archived' => 0]);
            return response()->json(['msg' => 'Updated successfully', 'status' => $update]);
        }
    }

    public function reviews_dt_list(Request $request, User $user)
    {
        if ($request->ajax()) { {
                $_order = request('order');
                $_columns = request('columns');
                $order_by = $_columns[$_order[0]['column']]['name'];
                $order_dir = $_order[0]['dir'];
                $search = request('search');
                $skip = request('start');
                $take = request('length');

                $query = Rating::where('user_id', $user->id);
                $recordsTotal = $query->count();

                if (isset($search['value'])) {
                    $query->WhereHas('park', function ($q) use ($search) {
                        $q->whereRaw('name LIKE "%' . $search['value'] . '%" ');
                    });
                }

                $recordsFiltered = $query->count();

                $data = $query
                    // ->orderBy($order_by, $order_dir)->skip($skip)->take($take)->get();
                    ->orderBy('id', 'DESC')->skip($skip)->take($take)->get();


                foreach ($data as &$d) {
                    $parkImg = $d->park->park_images()->where('set_as_banner', '1')->first();
                    $image =  $parkImg ?  $parkImg->media->full_path : asset('images/default.jpg');
                    // $image =  asset('images/default.jpg');

                    // if (!empty(Park::where)) {
                    //     $parkImg = $d->parks->park_images()->where('set_as_banner', '1')->first();
                    //     $image =  $parkImg ?  $parkImg->media->full_path : asset('images/default.jpg');
                    // }

                    $d->name =  "<img src=' $image' alt='Logo' height='50px' width='50px' style='border-radius: 10px;'> <a class='text-reset' style='text-decoration:none;' href='" . route('admin.park.details', $d->park->id) . "' rel='tooltip' title='Go To Details'>" . $d->park->name . "</a>";
                    $rating = $d->rating;
                    $ratings_star = View::make('components.admin.ratingcomponent', compact('rating'))->render();
                    $d->review = Str::limit($d->review, 25, '...');
                    $d->ratings = $ratings_star;
                    $tooltipTitle = "Verify Review";
                    $infoBtn = true;
                    $detailsRoute = route('admin.park.pending.reviews', $d->id);
                    $detailsRouteTooltipTitle = "Verify Review";


                    $other = "<a href='$detailsRoute' rel='tooltip' class='btn btn-icon ml-1 btn-primary'
                        title='Verify Review'><span class='tf-icons bx bx-info-circle'></span>
                    </a>";
                    if ($d->is_verified) {
                        $other = '';
                    }
                    $deleteRoute = route('admin.park.delete.review', $d->id);
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
        $active_page = "park";
        $page_title = "Parks";
        $custom_headings = "Parks";
        $breadcrumbs = collect([['route' => route('admin.park.user.pending.images'), 'name' => 'Pending Image(s)'],]);

        return view('admin.park.users_images', compact('active_page', 'page_title', 'custom_headings', 'breadcrumbs', 'park'));
    }


    public function active_inactive(Request $request, User $user)
    {
        if ($request->ajax()) {
            if ($request->status == 0) {
                $user->tokens()->delete();
                $user->update(['is_active' => false]);
                $msg = __('admin.user_inactivated');
            } else if ($request->status == 1) {
                $user->update(['is_active' => true]);
                $msg = __('admin.user_activated');
            }

            return response()->json([
                'msg' => $msg
            ]);
        }
    }

    public function delete_account_index(Request $request)
    {
        $user = $request->user();
        if (!$user->can('users-show')) {
            abort(404);
        }
        $active_page = "delete_account";
        $page_title = "Delete User Requests";
        return view('admin.delete_account.index', compact('active_page', 'page_title'));
    }

    public function delete_account_dt_list()
    {
        $_order = request('order');
        $_columns = request('columns');
        $order_by = $_columns[$_order[0]['column']]['name'];
        $order_dir = $_order[0]['dir'];
        $search = request('search');
        $skip = request('start');
        $take = request('length');
        $query = DeleteAccountRequest::query();

        $recordsTotal = $query->count();

        if (isset($search['value'])) {
            $query->WhereHas('user', function ($q) use ($search) {
                $q->whereRaw("email LIKE '%" . $search['value'] . "%' OR name LIKE '%" . $search['value'] . "%'")
                    ->orWhereRaw("username LIKE '%" . $search['value'] . "%'");
            });
        }

        $recordsFiltered = $query->count();

        $data = $query
            ->orderBy($order_by, $order_dir)->skip($skip)->take($take)->get();

        foreach ($data as $d) {
            $detailsRoute = route('admin.user.view', $d->user_id);
            $image = $d->user->image_id ? $d->user->image->full_path : asset('images/user.svg');

            $email_verified = $d->user->email_verified_at ? "<span class='bx bxs-badge-check' style='color:#48D33A; font-size:1.5rem;'></span>" : null;
            $d->email =  $d->user->email ? "<div class='d-flex'>" . Str::limit($d->user->email, 50) . ' ' . $email_verified . "</div>" : "N/A";
            $d->name =  "<div class='d-flex'>
            <a href='$detailsRoute'>
            <img src='" . $image  .
                "'alt='Logo' height='40px' width='40px' style='border-radius: 10px'><span class='ml-2'>" . ucfirst($d->user->name) . "</span></a></div>";

            $d->created = Carbon::parse($d->created_at)->setTimezone(Auth::user()->timezone)->format('d M y h:i A');
            $statusRoute = route('admin.user.active.inactive', $d->user_id);
            $status = ($d->user->is_active) ? 'checked' : '';
            $id = $d->user->id;
            $d->action = View::make('components.admin.actioncomponent', compact('detailsRoute', 'statusRoute', 'status', 'id'))->render();
        }

        return [
            "draw" => request('draw'),
            "recordsTotal" => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            "data" => $data,
        ];
    }
}
