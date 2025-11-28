<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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

        $query = User::where('role', 2);

        $total = $query->count();
        $maxPage = ceil($total / $limit);

        // Redirect to named route if current page is invalid
        if ($page > 1 && $page > $maxPage && $total > 0) {
            return redirect()->route('admin.userIndex', ['limit' => $limit]);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereAny(['fullname', 'email'],  'like', '%' . $request->search . '%');
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
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $allInfo = $query->orderBy('id', 'desc')->paginate($limit);

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


    public function userUpdate(Request $request)
    {

        $userId = User::find($request->id);
        $request->validate([
            'status' => 'required|in:0,1',
        ]);

        $user = User::findOrFail($userId);
        $user->status = $request->status;

        if ($request->status == 0) {
            $user->currentAccessToken()->delete();
            $user->tokens()->delete();
        }

        if ($user->save()) {
            return response()->json(['message' => 'User updated successfully']);
        }

        return response()->json(['message' => 'Failed to update user'], 500);
    }

    public function userDelete(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->resetData();
            $user?->currentAccessToken()?->delete();
            $user?->tokens()?->delete();
            $user->delete();

            $currentPage = (int) $request->get('page', 1);
            $limit       = 10;
            $total       = User::where('role', 2)->count();
            $maxPage     = (int) ceil($total / $limit);
            $redirectPage = $currentPage > $maxPage ? $maxPage : $currentPage;

            if ($redirectPage < 1) {
                $redirectPage = 1;
            }

            return response()->json([
                'success'      => true,
                'message'      => 'User deleted successfully',
                'redirect_url' => $redirectPage > 1
                    ? url('admin/users?page=' . $redirectPage)
                    : url('admin/users'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
