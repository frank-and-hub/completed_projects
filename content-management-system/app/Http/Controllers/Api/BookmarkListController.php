<?php

namespace App\Http\Controllers\Api;

use App\Helpers\YResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bookmark\BookmarkListCreateRequest;
use App\Http\Requests\Bookmark\BookmarkListUpdateRequest;
use App\Http\Resources\api\UserBookmarkTypeResource;
use App\Models\BookmarkType;
use Illuminate\Http\Request;

class BookmarkListController extends Controller
{

    public function list(Request $request)
    {
        $user = $request->user();
        $bookmark_types = BookmarkType::where('user_id', $user->id)->orWhereNull('user_id')->orderBy('user_id', 'asc')->get();
        return YResponse::json(data: ['bookmark_type' => UserBookmarkTypeResource::collection($bookmark_types)]);
    }

    public function create(BookmarkListCreateRequest $request)
    {
        $user = $request->user();
        $bookmark = BookmarkType::create(['type' => $request->type, 'user_id' => $user->id]);
        return YResponse::json(data: ['bookmark_type' => new UserBookmarkTypeResource($bookmark)]);
    }

    public function update(BookmarkListUpdateRequest $request)
    {
        $user = $request->user();
        $bookmark_type = BookmarkType::where('id', $request->id)->where('user_id', $request->user()->id)->first();
        if (empty($bookmark_type)) {
            return YResponse::json(message:__('api_message.bookmark_type_not_found'), status: 404);
        }
        BookmarkType::where('id', $request->id)->update(['type' => $request->type]);
        $bookmark_types = BookmarkType::find($request->id);
        return YResponse::json(data: ['bookmark_type' => new UserBookmarkTypeResource($bookmark_types)]);
    }

    public function delete(Request $request)
    {
        $bookmark_type = BookmarkType::where('id', $request->id)->where('user_id', $request->user()->id)->first();
        if (empty($bookmark_type)) {
            return YResponse::json(message: __('api_message.bookmark_type_not_found'), status: 404);
        }
        if(count($bookmark_type->parks) > 0){
            // return YResponse::json(message: __('You cannot delete this bookmark because parks already exist within it'), status: 404);
            return YResponse::json(message: __("You can't delete this bookmark because it still includes parks. Please remove or reassign them first."), status: 404);
        }
        BookmarkType::where('id', $request->id)->whereNotNull('user_id')->delete();
        return YResponse::json(message: "Successfully Deleted list ");
    }

    // public function delete_all(Request $request)
    // {
    //     $user = $request->user();
    //     BookmarkType::where('user_id', $user->id)->delete();
    //     return YResponse::json(message: "Successfully Deleted saved list");
    // }
}
