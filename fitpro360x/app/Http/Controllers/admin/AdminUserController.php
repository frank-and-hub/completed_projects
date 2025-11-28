<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSubscription;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    use Common_trait;

    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $query = User::with(['subscription.package'])->where('role', 2); 
    
        $total = $query->count();
        $maxPage = ceil($total / $limit);

        // Redirect to named route if current page is invalid
        if ($page > 1 && $page > $maxPage && $total > 0) {
            return redirect()->route('admin.userIndex', ['limit' => $limit]);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('fullname', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan_name')) {
            $query->whereHas('subscription.package', function ($q) use ($request) {
                $q->where('plan_name', $request->plan_name);
            });
        }

        $allInfo = $query->paginate($limit);

        return view('admin.manage-users.index', compact('allInfo'));
    }


    public function userAdd()
    {
        return view('admin.manage-users.add');
    }

    public function userSave(Request $req)
    {
        $req->validate([
            'fullname' => 'required|string|min:3|max:255|regex:/^[a-zA-Z]+$/',
            'email'      => 'required|email|unique:' . config('tables.users'),
            'password'   => 'required|min:6|max:15',
            'profile_photo'     => 'required|file|mimes:jpg,jpeg,png',
            'status'     => 'required|in:0,1',
        ]);

        $img = $this->file_upload($req->file('profile_photo'), config('constants.uploads') . '/' . config('constants.user_profile_photo'));

        $user = new User();
        $user->fullname = $req->fullname;
        $user->email      = $req->email;
        $user->profile_photo      = $img;
        $user->role      = 2;
        $user->status      = $req->status;
        $user->password   = Hash::make($req->password);

        if ($user->save()) {
            return redirect()->back()->with('flash-success', 'User created successfully');
        } else {
            return redirect()->back()->with('Oops! Something went wrong!');
        }
    }

    public function userEdit($id)
    {
        $singleUser = User::findOrFail($id);
        return view('admin.manage-users.edit', compact('singleUser'));
    }

    // public function userUpdate(Request $req, $userId)
    // {
    //     $req->validate([
    //         'status'     => 'required|in:0,1',
    //     ]);

    //     $user = User::findOrFail($userId);
    //     $user->status      = $req->status;

    //     if ($user->save()) {
    //         return redirect()->back()->with('flash-success', 'Status updated');
    //     } else {
    //         return redirect()->back()->with('Oops! Something went wrong!');
    //     }
    // }

    public function userUpdate(Request $request)
    {

        $userId = User::find($request->id);
        $request->validate([
            'status' => 'required|in:0,1',
        ]);

        $user = User::findOrFail($userId);
        $user->status = $request->status;

        if ($user->save()) {
            return response()->json(['message' => 'User updated successfully']);
        }

        return response()->json(['message' => 'Failed to update user'], 500);
    }
}
