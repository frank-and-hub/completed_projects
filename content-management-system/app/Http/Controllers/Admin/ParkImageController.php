<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\admin\DeleteParkImageJob;
use App\Models\Media;
use App\Models\ParkImage;
use App\Models\Parks;
use App\Models\Pendingimage;
use App\Models\User;
use App\Services\RevalidateApiService;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class ParkImageController extends Controller
{
    protected $revalidateApi;

    public function __construct(RevalidateApiService $revalidateApi)
    {
        $this->revalidateApi = $revalidateApi;
    }

    public function upload(Parks $park)
    {
        if (!$park) {
            return back()->with('error', __('admin.park_not_found'));
        }

        $parkId = $park->id;

        if (count(Parkimage::where('status', '1')->where('park_id', $parkId)->with('media')->get()) > 0) {
            return redirect()->back();
        }
        $banner_image = null;
        dispatch(new DeleteParkImageJob($parkId));
        $page_title = "Parks";
        $active_page = "park";
        $breadcrumbs = collect([['route' => route('admin.park.index'), 'name' => 'Parks']]);

        return view('admin.park.upload_image', compact('park', 'banner_image', 'page_title', 'active_page', 'breadcrumbs'));
    }

    public function edit(Request $request, Parks $park)
    {
        if (!$park) {
            return back()->with('error', __('admin.park_not_found'));
        }
        $parkId = $park->id;
        dispatch(new DeleteParkImageJob($parkId));
        if ($park->park_images()->whereNull('sort_index')->exists()) {
            $park_image_ids = $park->park_images->pluck('id');
            foreach ($park_image_ids as $index => $val) {
                $park->park_images()->where('id', $val)->update(['sort_index' => $index]);
            }
        }

        $parkimage = Parkimage::where('status', '1')->where('park_id', $parkId)->WhereNull('user_id')->orderBy('sort_index', 'ASC')->with('media');
        $subadminImg = Parkimage::where('status', '1')->where('park_id', $parkId)->whereNotNull('user_id')->where('is_verified', 1)->orderBy('sort_index', 'ASC')
            ->with('media')->WhereHas('user', function ($q) {
                $q->role('subadmin');
            });

        $userImage = Parkimage::where('park_id', $parkId)->where('is_verified', 1)->whereNotNull('user_id')
            ->WhereHas('user', function ($q) {
                $q->role('user');
            })->orderBy('sort_index', 'ASC');

        $parkimages = (clone $parkimage)->offset(0)->limit(200)->get();
        $userimges = (clone $userImage)->offset(0)->limit(200)->get();
        $subadminimges = (clone $subadminImg)->offset(0)->limit(200)->get();
        $more_data = (clone $parkimage)->offset(200)->limit(200)->get();
        $parkimages = $parkimages->concat($userimges)->concat($subadminimges);

        if ($request->user()->hasRole('subadmin')) {
            $parkimages = $subadminimges;
        }

        $UserUploadedImage = $userImage->clone()->offset(0)->limit(10)->groupBy('user_id')->get();
        $SubadminUplodedImage = $subadminImg->clone()->offset(0)->limit(10)->groupBy('user_id')->get();
        $ParkscapeUploadedImage = (clone $parkimage)->offset(0)->limit(200)->clone()->groupBy('park_id')->get();

        $banner_image = Parkimage::where('status', '1')->where('park_id', $parkId)->where('set_as_banner', '1')->with('media')->get();
        $more_data = count($more_data);
        $page_title = "Parks";
        $active_page = "park";
        $breadcrumbs = collect([['route' => route('admin.park.index'), 'name' => 'Parks']]);
        $parkimages = $parkimages->sortBy('sort_index');

        return view('admin.park.upload_image', compact(
            'parkimages',
            'park',
            'more_data',
            'banner_image',
            'page_title',
            'active_page',
            'breadcrumbs',
            'UserUploadedImage',
            'SubadminUplodedImage',
            'ParkscapeUploadedImage'
        ));
    }

    public function searchSelectPickerOptions(Request $request, Parks $park)
    {
        if ($request->ajax()) {
            $park_id = $park->id;
            $search_text = $request->search;
            $event = $request->event;
            $parkimage = Parkimage::where('status', '1')->where('park_id', $park_id)->WhereNull('user_id')->orderBy('sort_index', 'ASC')->with('media');
            $userQuery = User::Wherehas('parkimages', function ($q) use ($park_id) {
                $q->where('park_id', $park_id)->where('is_verified', 1)->where('is_archived', 0)->whereNotNull('user_id');
            });

            if ($event == 'searchdata') {
                $userQry = $userQuery->clone()->where('name', 'LIKE', '%' . $search_text . '%');
                $users = $userQry->clone()->role('user')->orderBy('name', 'ASC')->get();
                $subadmin = $userQry->clone()->role('subadmin')->orderBy('name', 'ASC')->get();
                $parkimages = $parkimage->get();

                $options = View::make('components.admin.park.selectoptioncomponent', [
                    'parkscapeuploadedimage' => $parkimages,
                    'subadminuplodedimage' => $subadmin,
                    'useruploadedimage' => $users,
                ])->render();
                return response()->json(['options' => $options]);
            }

            $users = $userQuery->clone()->role('user')->orderBy('name', 'ASC')->limit(10)->get();
            $subadmin = $userQuery->clone()->role('subadmin')->orderBy('name', 'ASC')->limit(10)->get();
            $parkimages = $parkimage->get();

            $options = View::make('components.admin.park.selectoptioncomponent', [
                'parkscapeuploadedimage' => $parkimages,
                'subadminuplodedimage' => $subadmin,
                'useruploadedimage' => $users,
            ])->render();

            return response()->json(['options' => $options]);
        }
    }

    public function filter_images(Request $request, Parks $park)
    {
        if ($request->ajax()) {
            $html = '';
            $oldIndexVal = [];
            switch ($request->type) {

                case 'parkscape':
                    $parkscape_images = Parkimage::where('status', '1')
                        ->where('park_id', $park->id)
                        ->WhereNull('user_id')
                        ->orderBy('sort_index', 'ASC')
                        ->with('media')
                        ->offset(0)
                        ->limit(200)
                        ->get();
                    $parkimages = $parkscape_images;
                    break;
                case 'all_users':
                    $all_user_images = Parkimage::where('park_id', $park->id)->where('is_verified', 1)
                        ->whereNotNull('user_id')
                        ->WhereHas('user', function ($q) {
                            $q->role('user');
                        })
                        ->orderBy('sort_index', 'ASC')
                        ->offset(0)->limit(200)->get();
                    $parkimages = $all_user_images;
                    break;

                case 'all_subadmins':
                    $all_subadmin_images = Parkimage::where('status', '1')->where('park_id', $park->id)->whereNotNull('user_id')->where('is_verified', 1)
                        ->with('media')->WhereHas('user', function ($q) {
                            $q->role('subadmin');
                        })->orderBy('sort_index', 'ASC')
                        ->offset(0)->limit(200)->get();
                    $parkimages = $all_subadmin_images;
                    break;

                case 'subadmin':
                    $subadmin_image = Parkimage::where('park_id', $park->id)
                        ->where('status', '1')
                        ->where('is_verified', 1)

                        ->where('user_id', $request->id)->with('media')
                        ->WhereHas('user', function ($q) {
                            $q->role('subadmin');
                        })
                        ->orderBy('sort_index', 'ASC')
                        ->offset(0)
                        ->limit(200)
                        ->get();
                    $parkimages = $subadmin_image;
                    break;
                case 'user':
                    $user_image = Parkimage::where('park_id', $park->id)
                        ->where('is_verified', 1)

                        ->where('user_id', $request->id)
                        ->WhereHas('user', function ($q) {
                            $q->role('user');
                        })
                        ->orderBy('sort_index', 'ASC')
                        ->offset(0)
                        ->limit(200)
                        ->get();
                    $parkimages = $user_image;
                    break;
                default:
                    $park_image = Parkimage::where('status', '1')->where('park_id', $park->id)->WhereNull('user_id')->orderBy('sort_index', 'ASC')->with('media');
                    $subadminImg = Parkimage::where('status', '1')->where('park_id', $park->id)->whereNotNull('user_id')->where('is_verified', 0)->orderBy('sort_index', 'ASC')
                        ->with('media')->WhereHas('user', function ($q) {
                            $q->role('subadmin');
                        });
                    $userImage = Parkimage::where('park_id', $park->id)->where('is_verified', 1)->whereNotNull('user_id')->orderBy('sort_index', 'ASC');
                    $park_images = (clone $park_image)->offset(0)->limit(200)->get();
                    $userimges = (clone $userImage)->offset(0)->limit(200)->get();
                    $subadminimges = (clone $subadminImg)->offset(0)->limit(200)->get();

                    $parkimages = $park_images->concat($userimges)->concat($subadminimges);
            }

            $oldIndexVal[] = $parkimages->pluck('sort_index');

            $html = View::make('components.admin.gallerycomponent', compact('parkimages'))->render();

            return response()->json([
                'msg' => '',
                'total_image' => $parkimages->count(),
                'html' => $html,
                'oldIndexVal' => $oldIndexVal,
                'more_data' => '',
            ]);
        }
    }

    public function loadMoreImage(Request $request)
    {
        if ($request->ajax()) {
            $userImage = Parkimage::where('park_id', $request->park_id)->where('is_verified', 1)->where('is_archived', 0)->whereNotNull('user_id');
            $images = Parkimage::where('status', '1')->where('park_id', $request->park_id)->whereNull('user_id')->orderBy('sort_index', 'ASC')->with('media');

            $parkimages = $images->offset($request->offset)->limit(200)->get();
            $userimages = $userImage->offset($request->offset)->limit(200)->get();
            $html = '';
            $more_data = $images->offset($request->offset + 200)->limit(200)->get();
            $parkimages = collect($parkimages)->merge(collect($userimages));
            $html = View::make('components.admin.gallerycomponent', compact('parkimages'))->render();
            return response()->json([
                'html' => $html,
                'more_data' => count($more_data)
            ]);
        }
    }
    public function setUnsetBanner(Request $request)
    {
        if ($request->ajax()) {
            $parkimage = (!empty($request->id)) ? ParkImage::where('id', $request->id)->where('park_id', $request->park_id) : ParkImage::where('img_tmp_id', $request->img_tmp_id)->where('park_id', $request->park_id);

            if ($request->type == 'set_banner') {
                ParkImage::where('set_as_banner', '1')->where('park_id', $request->park_id)->update(['set_as_banner' => '0']);
                $parkimage->update(['set_as_banner' => '1']);
                $bannerImg = (clone $parkimage)->where('set_as_banner', '1')->with('media')->first();
                return response()->json(['banner_img' => Storage::url($bannerImg->media->path)]);
            } else {
                $parkimage->update(['set_as_banner' => '0']);
            }
        }
    }

    public function save(Request $request)
    {
        if ($request->ajax()) {
            if ($request->hasFile('park')) {
                $image = Media::save_media(file: $request->file('park'), dir: 'parks', tags: ['park image'], store_as: 'image');
                $park = Parks::find($request->park_id);
                if (!$park) {
                    return response()->json(['error' => 'Park not found'], 404);
                }
                $data = ['park_id' => $park->id, 'media_id' => $image->id, 'set_as_banner' => '0', 'status' => '0', 'img_tmp_id' => $request->img_tmp_id];

                if ($request->user()->hasRole('subadmin')) {
                    $data['user_id'] = $request->user()->id;
                    $data['is_verified'] = 1;
                }
                $park_image = ParkImage::create($data);
                $this->revalidateApi->revalidatePark($park);
                return response()->json(['id' => $park_image->id]);
            }
        }
    }

    public function store(Parks $park)
    {
        if (!$park) {
            return back()->with('error', __('admin.park_not_found'));
        }
        $parkId = $park->id;
        Parkimage::where('park_id', $parkId)->where('status', '0')->update(['status' => '1']);
        $parkimage = Parkimage::where('park_id', $parkId)->where('status', '1')->count();

        $this->revalidateApi->revalidatePark($park);

        if ($parkimage > 0) {
            return redirect()->route('admin.park.index')->with('success', 'Saved Images Successfully !');
        } else {
            return back()->with('error', __('Please Add Minimum One Image'));
        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax()) {
            if (!empty($request->user_id)) {
                $pending_img = Pendingimage::where('user_id', $request->user_id)
                    ->where('park_id', $request->park_id)->first();
                $pending_img->decrement('total_verify_image');
                if ($pending_img->total_pending_image == 0) {
                    $pending_img->delete();
                }
            }

            $park = Parks::find($request->park_id);
            $this->revalidateApi->revalidatePark($park);

            if ($request->park_img_id) {
                $parkimage = ParkImage::where('id', $request->park_img_id)->get();
                ParkImage::where('id', $request->park_img_id)->delete();
                Media::where('id', $parkimage->first()->media_id)->delete();
            } else {
                $parkimage = ParkImage::where('img_tmp_id', $request->id)->get();
                ParkImage::where('img_tmp_id', $request->id)->delete();
                Media::where('id', $parkimage->first()->media_id)->delete();
            }
            // Artisan::call("update:pendingimgtbl");

            return response()->json(['msg' => 'Image is deleted successfully', 'status' => '1']);
        }
    }

    public function deletMultipleImages(Request $request)
    {
        try {
            if ($request->ajax()) {
                if (is_null($request->id)) {
                    return response()->json(['status' => '0', 'msg' => "Please select image"]);
                }

                $parkimage = ParkImage::whereIn('id', $request->id);
                $media_id = collect($parkimage->pluck('media_id'));
                Media::wherein('id', $media_id)->delete();
                $delete = $parkimage->delete();
                if (!is_null($request->user_id)) {
                    foreach ($request->user_id as $user_id) {
                        $pending_img = Pendingimage::where('user_id', $user_id)
                            ->where('park_id', $request->park_id)->first();
                        $pending_img->decrement('total_verify_image');
                        if ($pending_img->total_pending_image == 0) {
                            $pending_img->delete();
                        }
                    }
                }

                if ($delete) {
                    return response()->json(['status' => '1', 'msg' => 'Image(s) is successfully deleted']);
                }
            }
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['msg' => 'Something is wrong', 'error' => $e]);
        }
    }

    public function view(Request $request, Parks $park)
    {
        if (!$park) {
            return back()->with('error', __('admin.park_not_found'));
        }
        $parkId = $park->id;
        $parkimage = Parkimage::where('status', '1')->where('park_id', $parkId)->orderBy('sort_index', 'ASC')->with('media');
        if (!count($parkimage->get()) > 0) {
            return redirect()->back();
        }

        $parkimages = (clone $parkimage)->offset(0)->limit(9)->get();
        $UserParkImages = ParkImage::where('park_id', $parkId)->where('is_verified', 1)->whereNotNull('user_id')
            ->orderBy('sort_index', 'ASC')->with('media');
        if ($request->ajax()) {
            $html = '';
            if (!empty($request->type) && $request->type == 'user') {

                $ParkImages = (clone $UserParkImages)->offset($request->offset)->limit(9)->get();
                $more_data = (clone $UserParkImages)->offset($request->offset + 9)->limit(9)->get();
                foreach ($ParkImages as $parkimage) {
                    $html .= " <a class='elem' href='" . Storage::url($parkimage->media->path) . "'
                    title='" . ucfirst($parkimage->media->name) . "'
                    data-lcl-thumb='" . Storage::url($parkimage->media->path) . "'>
                    <span style='background-image: url(" . Storage::url($parkimage->media->path) . ")'></span>
                    </a>";
                }
            } else {
                $parkimages = (clone $parkimage)->offset($request->offset)->limit(9)->get();

                $more_data = (clone $parkimage)->offset($request->offset + 9)->limit(9)->get();

                foreach ($parkimages as $parkimage) {
                    $setBannerClass = "btn-primary bannerBtn";
                    $unsetBannerClass = "btn-danger unsetBanner";

                    $btnName = $check_mark = $box_shadow = '';
                    $bannerBtnClass = $setBannerClass;
                    if ($parkimage->set_as_banner != 1) {
                        $btnName = "Set As Banner";
                    } else {
                        $box_shadow = "box-shadow";
                        $bannerBtnClass = $unsetBannerClass;
                        $btnName = "Unset Banner";
                        $check_mark = "<div class='check-mark'> <svg xmlns='http://www.w3.org/2000/svg' width='30'
                        height='30' fill='#2fa224' class='bi bi-check-circle-fill'
                        viewBox='0 0 16 16'>
                        <path
                            d='M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z'>
                        </path>
                    </svg> </div>";
                    }

                    $html .= " <a class='elem $box_shadow' href='" . Storage::url($parkimage->media->path) . "'
                    title='" . ucfirst($parkimage->media->name) . "'
                    data-lcl-thumb='" . Storage::url($parkimage->media->path) . "'>
                    <span style='background-image: url(" . Storage::url($parkimage->media->path) . ")'></span>
                    <div class='text-center p-2 galleryBannerBtn d-none'>
                        <button class='btn btn-sm " . $bannerBtnClass . "' id='$parkimage->img_tmp_id'>$btnName</button>
                    </div>$check_mark</a>";
                }
            }


            return response()->json(['html' => $html, 'more_data' => count($more_data)]);
        }

        $page_title = "Parks";
        $active_page = "park";
        $total_users_park_images = ParkImage::where('park_id', $park->id)->where('is_verified', 1)->whereNotNull('user_id')->count();
        return view('admin.park.imageview', compact('parkimages', 'park', 'active_page', 'total_users_park_images'));
    }

    public function draggableSort(Request $request)
    {
        if ($request->ajax()) {
            foreach ($request->id as $index => $val) {
                if ($request->notDragged == 'false') {
                    ParkImage::where('park_id', $request->park_id)->where('id', $val)->update(['sort_index' => $index]);
                }
            }

            // if (!empty($request->id)) {
            //     if (!empty($request->old_index_val)) {
            //         $i = 0;
            //         foreach ($request->id  as $index => $val) {
            //             ParkImage::where('park_id', $request->park_id)->where('id', $val)->update(['sort_index' => $request->old_index_val[$i]]);
            //             $i++;
            //         }
            //     }

            //     else {

            //     }
            // }
        }
    }
}
