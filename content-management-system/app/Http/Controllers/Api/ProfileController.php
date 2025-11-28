<?php

namespace App\Http\Controllers\Api;

use App\Helpers\YResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $_user = $request->user();
        return YResponse::json(data: new ProfileResource($_user));
    }
    public function update(ProfileRequest $request)
    {
        $_user = $request->user();

        $old_media_to_delete = null;
        try {
            DB::beginTransaction();

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                // return Media::save_media(file: $request->file('image'), dir: 'profile', tags: ['profile image'], user_id: $user->id);
                $image = Media::save_media(file: $request->file('image'), dir: 'profile', tags: ['profile image'], user_id: $_user->id, store_as: 'image');
                $old_media_to_delete = $_user->image;
                // $old_media_to_delete->forceDelete();
                $_user->image_id = $image->id;
            }
            if($request->name)
            {
                $_user->name = $request->name ??  $_user->name;
            }
            if($request->username){
                $_user->username = $request->username ?? $_user->username;
            }

            $_user->save();
            if ($old_media_to_delete) {
                $old_media_to_delete->forceDelete();
            }
        } catch (Throwable $e) {
            // return get_class($e);
            DB::rollback();
            throw $e;
        }
        DB::commit();
        return YResponse::json(data: new ProfileResource($_user->fresh()));
    }
}
