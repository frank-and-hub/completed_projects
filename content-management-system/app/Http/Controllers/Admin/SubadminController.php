<?php

namespace App\Http\Controllers\admin;

use App\Helpers\YResponse;
use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\ParkImage;
use App\Models\User;
use App\Models\Parks;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class SubadminController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->can('show-sub-admins')) {
            abort(404);
        }
        $active_page = "subadmin";
        $page_title = "Admins";
        return view('admin.subadmin.index', compact('active_page', 'page_title'));
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
        $query = User::role('subadmin');


        $recordsTotal = $query->count();

        if (isset($search['value'])) {
            $query->Where(function ($q) use ($search) {
                $q->whereRaw("email LIKE '%" . $search['value'] . "%' OR name LIKE '%" . $search['value'] . "%'");
            });
        }


        $recordsFiltered = $query->count();

        $data = $query
            ->orderBy($order_by, $order_dir)->skip($skip)->take($take)->get();

        foreach ($data as $d) {
            $image = $d->image_id ? $d->image->full_path : asset('images/user.svg');
            $email_verified = $d->email_verified_at ? "<span class='bx bxs-badge-check' style='color:#48D33A; font-size:1.5rem;'></span>" : null;
            // $d->email =  $d->email ? "<div class='d-flex'>" . $d->email . ' ' . $email_verified . "</div>" : "N/A";
            $d->email = $d->email;

            $detailsRoute =  route('admin.subadmin.details', $d->id);
            $onlineAvatar = ($d->is_active) ? "avatar-online" : null;
            $detailsRouteTooltipTitle = "Click to show parks created by " . $d->name;

            $d->name  = "<div class='d-flex'>
                <div class='flex-shrink-0 me-3'>
                    <div class='avatar $onlineAvatar'>
                        <a href='$detailsRoute'><img src='$image' alt='' class='w-px-40 h-auto rounded-circle'></a>
                    </div>
                </div>
                <div class='mt-2'>
                    <a href='$detailsRoute'><span >" . $d->name . "</span></a>

                </div>
            </div>";


            $status = ($d->is_active == 1) ? 'checked' : '';
            $statusRoute = route('admin.subadmin.changestatus', $d->id);
            $changePasswordRoute = route('admin.subadmin.resetpassword', $d->id);
            $editRoute = route('admin.subadmin.edit', $d->id);
            $deleteRoute = route('admin.subadmin.delete', $d->id);
            $d->action = View::make(
                'components.admin.actioncomponent',
                compact(
                    'editRoute',
                    'statusRoute',
                    'status',
                    'deleteRoute',
                )
            )->render();
        }


        return [
            "draw" => request('draw'),
            "recordsTotal" => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            "data" => $data,
        ];
    }


    public function park_dt_list(Request $request, User $user)
    {
        $_order = request('order');
        $_columns = request('columns');
        $order_by = $_columns[$_order[0]['column']]['name'];
        $order_dir = $_order[0]['dir'];
        $search = request('search');
        $skip = request('start');
        $take = request('length');

        $query = $user->parks();
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
            $detailsRoute = route('admin.park.details', $d->id);
            $park_image_1 = $d->park_images()->where('set_as_banner', '1')->first();
            $park_images = ParkImage::where('park_id', $d->id)
                ->where('status', '1')->get();

            $image =  $park_image_1 ?  $park_image_1->media->full_path : asset('images/default.jpg');
            $d->name =  "<img src=' $image' alt='Logo' height='50px' width='50px' style='border-radius: 10px;'> <a class='text-reset' style='text-decoration:none;' href='$detailsRoute' rel='tooltip' title='Go To Details'>" . $d->name . "</a>";
            // $d->created_at = Carbon::parse($d->created_at)->setTimezone(Auth::user()->timezone)->format('d-m-y');
            $editRoute = route('admin.park.edit', $d->id);
            $deleteRoute = route('admin.delete.park', $d->id);
            $imageEditRoute = $imageUplodRoute = null;
            if (count($park_images) == 0) {
                $imageUplodRoute = route('admin.park.image.upload', $d->id);
            } else {
                $imageEditRoute = route('admin.park.image.edit', $d->id);
            }
            $d->created_at_ = Carbon::parse($d->created_at)->setTimezone(Auth::user()->timezone)->format('d M y');


            $statusRoute = route('admin.update.park.status', $d->id);
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
        if (!$user->can('show-sub-admins')) {
            abort(404);
        }
        $active_page = "subadmin";
        $page_title = "Sub-Admin";
        $subadmin = null;
        $breadcrumbs = collect([['route' => route('admin.subadmin.index'), 'name' => 'Sub-Admins']]);
        return view('admin.subadmin.create', compact('active_page', 'page_title', 'subadmin', 'breadcrumbs'));
    }
    public function edit(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->can('show-sub-admins')) {
            abort(404);
        }
        $active_page = "subadmin";
        $page_title = "Sub-Admin";
        $breadcrumbs = [
            ['route' => route('admin.subadmin.index'), 'name' => 'Sub-Admins'],

        ];
        $subadmin = User::role('subadmin')->where('id', $id)->first();
        return view('admin.subadmin.create', compact('active_page', 'page_title', 'subadmin', 'breadcrumbs'));
    }


    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'image' => ['image', 'mimes:png,jpg', 'max:1024'],
                'password' => ['required', 'string', 'min:8'],
                'confirm_password' => ['required', 'string', 'min:8', 'same:password'],
            ],
            [
                'image.max' => 'The size of the image must not be greater than 1 MB',
            ]
        );

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
        }
        $subadmin = new User();

        $subadmin->name = $request->name;
        $subadmin->email = $request->email;
        $subadmin->email_verified_at = Carbon::now();
        $subadmin->password = Hash::make(12345678);

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $image = Media::save_media(file: $request->file('image'), dir: 'profile', tags: ['profile image'], user_id: $subadmin->id, store_as: 'image');
            $subadmin->image_id = $image->id;
        }
        $subadmin->password = Hash::make($request->password);
        $subadmin->save();
        $subadmin->assignRole('subadmin');

        return redirect()->route('admin.subadmin.index')->with('success', __('admin.subadmin_create'));
    }
    public function update(Request $request, User $subadmin)
    {
        $subadminId = $subadmin->id;

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'email' => "required|email|unique:users,email," . $subadminId,
                'image' => ['image', 'mimes:png,jpg', 'max:1024'],
                'password' => ['required', 'string', 'min:8'],
                'confirm_password' => ['required', 'string', 'min:8', 'same:password'],
            ],
            [
                'image.max' => 'The size of the image must not be greater than 1 MB',
            ]
        );

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
        }

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $image = Media::save_media(file: $request->file('image'), dir: 'profile', tags: ['profile image'], user_id: $subadminId, store_as: 'image');
            $subadmin->image_id = $image->id;
        }

        $subadmin->name = $request->name;
        $subadmin->email = $request->email;

        if ((Hash::check(request('password'), $subadmin->password)) == true) {
            return redirect()->back()->with('error', __('admin.invalid_new_password'));
        }

        $subadmin->password = Hash::make($request->password);

        $subadmin->save();

        return redirect()->route('admin.subadmin.index')->with('success', __('admin.subadmin_update'));
    }

    public function changeStatus(Request $request, User $user)
    {
        if ($request->ajax()) {
            $user->update([
                'is_active' => $request->status
            ]);
            $msg = __('admin.subadmin_active');
            if ($request->status == 0) {
                $msg = __('admin.subadmin_inactive');
            }
            return response()->json([
                'msg' => $msg
            ]);
        }
    }

    public function aj_reset_password(User $user)
    {

        return view('admin.subadmin.reset_password', compact('user'));
    }
    public function password_update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8'],
            'confirm_password' => ['required', 'string', 'min:8', 'same:password'],
        ]);

        $user = User::find($request->id);

        if ((Hash::check(request('password'), $user->password)) == true) {
            return YResponse::json(message: __('admin.invalid_new_password'), status: 406);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return YResponse::json(message: __('admin.password_change_success'), status: 200);
    }

    public function view(Request $requestl, User $user)
    {
        if (!$user) {

            return redirect()->back()->with('error', __('admin.user_not_found'));
        }
        $active_page = "subadmin";
        $page_title = "Admin";
        $breadcrumbs = collect([
            ['name' => 'Admins', 'route' => route('admin.subadmin.index')]
        ]);
        return view('admin.subadmin.details', compact('user', 'breadcrumbs', 'active_page', 'page_title'));
    }

    public function delete(Request $request, User $user)
    {
        if ($request->ajax()) {
            $user->verifications()->delete();
            Parks::where('created_by_id', $user->id)->update(['created_by_id' => null]);

            if ($user->parkimages) {
                ParkImage::where('user_id', $user->id)->update(['user_id' => null, 'is_verified' => 0]);
            }
            $delete = $user->forceDelete();
            if ($delete) {
                return response()->json([
                    'status' => $delete,
                    'msg' => __('admin.subadmin_delete')
                ]);
            }
        }
    }
}
