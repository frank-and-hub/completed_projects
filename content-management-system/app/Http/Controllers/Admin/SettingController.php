<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\YResponse;
use App\Http\Controllers\Controller;
use App\Models\CustomPage;
use App\Models\Media;
use App\Models\Seasons;
use App\Models\User;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function index()
    {
        $admin = Auth::user();
        $active_page = "settings";
        $page_title = "Settings";

        $admin_timezone = $admin->timezone;
        // $setting = Setting::find(1);


        $carbon = \Carbon\Carbon::now();

        foreach (timezone_identifiers_list(DateTimeZone::ALL) as $key => $t) {
            $tz = new DateTimeZone($t);

            $timezonelist[$key]['zone'] = $t;
            $timezonelist[$key]['GMT_difference'] = $carbon->setTimezone($tz)->format('P');

            if (
                $admin_timezone == $timezonelist[$key]['zone']

            ) {
                $timezonelist[$key]['selected'] = 'selected';
            } else {
                $timezonelist[$key]['selected'] = '';
            }
        }
        return view('admin.settings.index', compact('active_page', 'admin', 'timezonelist', 'page_title'));
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

        $query = CustomPage::query();


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
            $d->name = ucwords($d->name);
            $editRoute =  route('admin.settings.edit.custom.page', $d->id);
            $d->action = View::make('components.admin.actioncomponent', compact('editRoute'))->render();
        }

        return [
            "draw" => request('draw'),
            "recordsTotal" => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            "data" => $data,
        ];
    }

    public function update_profile(Request $request)
    {
        $admin = auth()->user();
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'image' => ['image', 'mimes:png,jpg', 'max:1024']
        ], [
            'image.max' => 'The size of the image must not be greater than 1 MB',
        ]);


        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
        }

        $admin = User::find($admin->id);
        $admin->name = $request->input('name');
        $admin->timezone = $request->get('timezone');
        $old_media_to_delete =  null;

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $image = Media::save_media(file: $request->file('image'), dir: 'profile', tags: ['profile image'], user_id: $admin->id, store_as: 'image');
            $old_media_to_delete = $admin->image;
            $admin->image_id = $image->id;
        }

        $admin->save();
        if ($old_media_to_delete) {
            $old_media_to_delete->forceDelete();
        }

        return redirect()->back()->with('success', __('admin.settings_update'));
    }
    public function aj_reset_password(User $user)
    {
        return view('admin.settings.reset_password', compact('user'));
    }
    public function password_update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string', 'min:8'],
            'password' => ['required', 'string', 'min:8'],
            'confirm_password' => ['required', 'string', 'min:8', 'same:password'],
        ]);

        $user = Auth::user();

        if ((Hash::check(request('current_password'), $user->password)) == false) {
            return YResponse::json(message: __('admin.invalid_current_password'), status: 406);
        }

        if ((Hash::check(request('password'), $user->password)) == true) {
            return YResponse::json(message: __('admin.invalid_new_password'), status: 406);
        }

        $user = User::find($user->id);
        $user->password = Hash::make($request->password);
        $user->save();

        return YResponse::json(message: __('admin.password_change_success'), status: 200);
    }

    public function create_custom_page(Request $request)
    {
        $user = $request->user();
        if (!$user->can('custom-page-show')) {
            abort(404);
        }
        $active_page = "settings";
        $page_title = "Settings";
        $breadcrumbs = collect([['route' => route('admin.settings'), 'name' => 'Settings']]);
        return view('admin.settings.create-custom-page', compact('active_page', 'breadcrumbs', 'page_title'));
    }

    public function save_custom_page(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'min:3', 'unique:custom_pages,name'],
            'text' => ['required', 'min:20'],
        ]);
        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
        }
        $data = $request->only(['text', 'name']);
        $data['slug'] = Str::slug($request->name);

        CustomPage::create($data);
        return redirect()->route('admin.settings')->with('success', __('admin.save_custom_page'));
    }

    public function edit_custom_page(Request $request, CustomPage $customPage)
    {
        $user = $request->user();
        if (!$user->can('custom-page-show')) {
            abort(404);
        }
        if (!$customPage) {
            return back()->with('error', 'Invalid data supply !');
        }
        $breadcrumbs = collect([['route' => route('admin.settings'), 'name' => 'Settings']]);
        $active_page = "settings";
        $page_title = "Settings";
        return view('admin.settings.create-custom-page', compact('active_page', 'breadcrumbs', 'page_title', 'customPage'));
    }

    public function update_custom_page(Request $request, CustomPage $customPage)
    {
        $customPageid = $customPage->id;
        $validator = Validator::make($request->all(), [
            'name' => [Rule::unique('custom_pages', 'name')->where(fn($query) =>
            $query->where('id', '!=', $customPageid)), 'required'],
            'text' => ['required', 'min:20'],
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
        }
        $data = $request->only(['text', 'name']);
        $data['slug'] = Str::slug($request->name);
        $customPage->update($data);
        return redirect()->route('admin.settings')->with('success', __('admin.update_custom_page'));
    }

    function reset_img(Request $request, User $user)
    {
        if ($request->ajax()) {
            $user->image->delete();
            $update = $user->update(['image_id' => null]);
            return response()->json(['status' => $update]);
        }
    }
}
