<?php

namespace App\Http\Controllers\Api;

use App\Helpers\YResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bookmark\BookmarkcreateRequest;
use App\Http\Resources\BookmarkCollection;
use App\Http\Resources\BookmarkResource;
use App\Models\Bookmark;
use App\Models\Parks;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function list(Request $request)
    {
        $user = $request->user();
        $bookmarks = Bookmark::where('bookmark_type_id', $request->bookmark_type_id)->where('user_id', $user->id)->orderBy('id', 'DESC');
        return YResponse::json(data: ["bookmarks" => (new BookmarkCollection($bookmarks->paginate($request->get('per_page', 15))->withQueryString()))->response()->getData()]);
    }
    public function create(BookmarkcreateRequest $request)
    {
        $user = $request->user();
        // $data = $request->only(['bookmark_type_id', 'park_id']);
        $data['user_id'] = $user->id;
        Bookmark::where('park_id', $request->park_id)->where('user_id', $user->id)->delete();
        if (!is_null($request->bookmark_type_id)) {
            foreach ($request->bookmark_type_id as $bookmark_type_id) {
                $bookmarks = Bookmark::updateOrCreate([
                    'user_id' => $user->id,
                    'park_id' => $request->park_id,
                    'bookmark_type_id' => $bookmark_type_id
                ]);
            }
            return YResponse::json(data: ['bookmarks' => (new BookmarkResource($bookmarks))]);
        }
        return YResponse::json(message: __('api_message.update_msg'));
    }

    // public function update(BookmarkUpdateRequest $request)
    // {
    //     $request->validate([
    //         'park_id' => 'required|exists:parks,id',
    //         ''
    //     ]);
    //     $user = $request->user();
    //     $data = $request->only(['bookmark_type_id', 'park_id']);
    //     $data['user_id'] = $user->id;
    //     Bookmark::where('id', $request->id)->update($data);
    //     $bookmarks = Bookmark::find($request->id);
    //     return YResponse::json(data: ['bookmarks' => (new BookmarkResource($bookmarks))], message: __('api_message.update_msg'));
    // }

    public function delete(Request $request)
    {
        $request->validate([
            'park_id' => 'required|exists:parks,id'
        ]);
        $bookmark = Bookmark::where('park_id', $request->park_id)->where('user_id', $request->user()->id);

        if (empty($bookmark->clone()->first())) {
            return YResponse::json(message: __('api_message.bookmark_not_found'), status: 404);
        }
        $bookmark->delete();
        return YResponse::json(message: __("api_message.delete_msg", ['name' => 'Bookmark']));
    }

    public function is_bookmarked(Request $request)
    {
        $request->validate([
            'park_ids' => 'required|array',
            'park_ids.*' => 'required|exists:' . Parks::class . ',id',
        ]);

        $parks = Parks::whereIn('id', $request->park_ids)->get();
        // (new BookmarkResource($bookmarks)
        $user = $request->user();

        if (!$user) {
            return YResponse::json(message: 'User Not found!', status: 404);
        }

        foreach ($parks as $k => $park) {
            $bookmark = Bookmark::where('park_id', $park->id)->where('user_id', $user->id)->get();
            $data[$park->id] = new BookmarkCollection($bookmark);
        }
        
        return YResponse::json(data: $data);
    }
}
