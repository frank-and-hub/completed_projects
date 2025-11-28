<?php

namespace App\Http\Controllers\adminsubuser;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminExternalPropertyUsers;
use App\Models\Country;
use App\Models\ExternalPropertyUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExternalPropertController extends Controller
{

    protected $title;
    public function __construct()
    {
        $this->title = 'API Access';
    }

    public function index()
    {
        if (auth()->guard('admin')->user()->getRoleNames()->first() !== 'agency') {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }

        $id = Auth::user()->id;
        $user = ExternalPropertyUser::whereHas('agencies', function ($q) use ($id) {
            $q->where('admin_external_property_users_pivot.admin_id', $id);
        })->with('agencies')->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Record not found',
            ]);
        }
        $data['title'] = $data['active_page'] = $this->title;
        $data['external_property'] = $user;
        $data['status'] = AdminExternalPropertyUsers::where('external_property_users_id', $user->id)
            ->where('admin_id', $id)
            ->value('status');
        $data['view'] = true;
        return view('adminsubuser.external_property.index', $data);
    }

    public function status(Request $request)
    {
        // Logic to handle the status of external properties
        // This could involve checking the status of properties, updating them, etc.
        // For now, we will just return a simple response.

        return response()->json(['status' => 'success', 'message' => 'External property status checked successfully.']);
    }
}
