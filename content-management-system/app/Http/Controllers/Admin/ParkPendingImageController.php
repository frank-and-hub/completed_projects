<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ParkImage;
use App\Models\Parks;
use App\Models\Pendingimage;
use App\Models\User;
use App\Models\Media;
use App\Services\RevalidateApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\URL;
use App\Helpers\PendingImages;

class ParkPendingImageController extends Controller
{

    protected $revalidateApi;

    public function __construct(RevalidateApiService $revalidateApi)
    {
        $this->revalidateApi = $revalidateApi;
    }

    public function view()
    {
        $active_page = "park";
        $page_title = "Parks";

        return view('admin.park.pending_images.index', compact('active_page', 'page_title'));
    }
    public function dt_list1(Request $request)
    {
        if ($request->ajax()) {
            $_order = request('order');
            $_columns = request('columns');
            $order_by = $_columns[$_order[0]['column']]['name'];
            $order_dir = $_order[0]['dir'];
            $search = request('search');
            $skip = request('start');
            $take = request('length');
            $user = $request->user();

            $query = Parks::whereHas('park_images', function ($q) {
                $q->whereNotNull('user_id');
            });

            $recordsTotal = $query->count();

            if (isset($search['value'])) {
                $query->Where(function ($q) use ($search) {
                    $q->whereRaw("name LIKE '%" . $search['value'] . "%' ");
                });
            }

            $recordsFiltered = $query->count();
            $data = $query->orderBy($order_by, $order_dir)->skip($skip)->take($take)->get();

            foreach ($data as &$d) {
                $park_image_1 = $d->park_images()->where('set_as_banner', '1')->first();
                $park_images = ParkImage::where('park_id', $d->id)
                    ->where('status', '1')->get();
                $image = $park_image_1 ? $park_image_1->media->full_path : asset('images/default.jpg');

                // $countQuery = $d->park_images()->whereNotNull('user_id');

                // $total_unverified_images = $d->total_user_pending_image;
                // $total_verified_images = $d->total_user_verified_image;

                // $pending_icon = ($total_unverified_images > 0) ? "<span class='text-danger'><i class='bx bxs-time' style='font-size:1.2rem;'></i></span>" : null;
                // $verified_icon = ($total_verified_images > 0) ? "<span class='text-primary'><i class='bx bx-check' style='font-size:1.5rem; font-weight: bolder;
                //     vertical-align: -3px;'></i></span>" : null;

                // $d->pending_images = $total_unverified_images . " " . $pending_icon;

                // $d->verified_images = "<div>" . $total_verified_images . " " . $verified_icon . "</div>";
                $d->username = 0;
                $d->pending_images = '0';
                $d->verified_images = '0';

                $d->name = "<img src=' $image' alt='Logo' height='50px' width='50px' style='border-radius: 10px;'> <a class='text-reset' style='text-decoration:none;' href='" . route('admin.park.details', $d->id) . "' rel='tooltip' title='Go To Details'>" . $d->name . "</a>";
                $tooltipTitle = "Verify Image";
                $infoBtn = true;
                $d->action = View::make('components.admin.actioncomponent', compact('infoBtn', 'tooltipTitle'))->render();
            }

            return [
                "draw" => request('draw'),
                "recordsTotal" => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                "data" => $data,
            ];
        }

        $user = $request->user();
        if (!$user->can('park-show')) {
            abort(404);
        }
        $active_page = "park";
        $page_title = "Pending Images";
        $custom_headings = "Parks";
        $parks = ParkImage::whereNotNull('user_id')->where('is_verified', 0)->get();

        return view('admin.park.pending_images', compact('active_page', 'page_title', 'custom_headings', 'parks'));
    }

    public function dt_list(Request $request)
    {
        if ($request->ajax()) {
            $_order = request('order');
            $_columns = request('columns');
            $order_by = $_columns[$_order[0]['column']]['name'];
            $order_dir = $_order[0]['dir'];
            $search = request('search');
            $skip = request('start');
            $take = request('length');
            $user = $request->user();

            $query = Pendingimage::select(
                '*',
                DB::raw('(select name from parks where parks.id=pendingimages.park_id) as name'),
                DB::raw('(select name from users where users.id=pendingimages.user_id) as username'),
            )
                ->WhereHas('user', function ($q) {
                    $q->role('user');
                });

            $recordsTotal = $query->count();

            if (isset($search['value'])) {
                $query->WhereHas('park', function ($q) use ($search) {
                    $q->WhereRaw("name LIKE '%" . $search['value'] . "%'");
                })->orWhereHas('user', function ($q) use ($search) {
                    $q->WhereRaw("name LIKE '%" . $search['value'] . "%'");
                });
            }

            $recordsFiltered = $query->count();

            $data = $query->skip($skip)->take($take)->orderByRaw("$order_by $order_dir")->get();

            foreach ($data as &$d) {
                $detailsRoute = route('admin.park.details', $d->park->id);
                $park_image = $d->park->park_images()->where('set_as_banner', '1')->first();
                $image = $park_image ? $park_image->media->full_path : asset('images/default.jpg');

                $d->name = "<img src=' $image' alt='Logo' height='50px' width='50px' style='border-radius: 10px;'> <a class='text-reset' style='text-decoration:none;' href='$detailsRoute' rel='tooltip' title='Go To Details'>" . $d->park->name . "</a>";
                $image = (!is_null($d->user->image_id)) ? $d->user->image->full_path : asset('images/user.svg');

                $d->username = "<div class='d-flex'><a href='" . route('admin.user.view', $d->user->id) . "'><img src='" . $image . "'alt='Logo' height='40px' width='40px' style='border-radius: 10px'><span class='ml-2'>" . $d->user->name . "</span></a></div>";

                $tooltipTitle = "Verify Image";
                $infoBtn = true;
                // $deleteRoute = route('admin.delete.useruploaded.images', $d->user->id);
                $detailsRoute = route('admin.park.pendingimage.view', [$d->park->id, $d->user->id]);
                $detailsRouteTooltipTitle = "Verify Pending Image";
                $d->action = View::make('components.admin.actioncomponent', compact('detailsRouteTooltipTitle', 'detailsRoute'))->render();
            }
            return [
                "draw" => request('draw'),
                "recordsTotal" => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                "data" => $data,
            ];
        }

        $user = $request->user();
        if (!$user->can('park-show')) {
            abort(404);
        }

        $active_page = "park";
        $page_title = "Pending Images";
        $custom_headings = "Parks";
        $parks = ParkImage::whereNotNull('user_id')->where('is_verified', 0)->get();

        return view('admin.park.pending_images', compact('active_page', 'page_title', 'custom_headings', 'parks'));
    }

    public function show_unverified_images(Request $request, Parks $park, User $user)
    {
        $parkImages = ParkImage::where('user_id', $user->id)->where('park_id', $park->id)->where('status', '1')->orderBy('id', 'DESC')
            ->get();
        $total_image = $parkImages->count();
        $total_verified_image = ParkImage::where('user_id', $user->id)->where('park_id', $park->id)
            ->where('status', '1')->where('is_verified', 1)->count();
        $total_unverified_image = $total_image - $total_verified_image;

        if ($request->ajax()) {

            // $parkImages = ParkImage::where('user_id', $user->id)->where('park_id', $park->id)->where('status', '1')->where('is_archived', 0)->orderBy('id', 'DESC')
            //     ->get();
            $is_archived = 0;
            if (count($parkImages) > 0) {
                $is_archived = $parkImages->first()->is_archived == 1;
            }

            $html = View::make('components.admin.pendingimagecomponent', compact('parkImages'))->render();

            return response()->Json([
                'data' => $html,
                'images' => [
                    'all_image' => $total_image,
                    'verified_image' => $total_verified_image,
                    'unverified_image' => $total_unverified_image,
                ],
                'is_archived' => $is_archived

            ]);
        }

        if (!$park) {
            return back()->with('error', __('admin.park_not_found'));
        }

        if (!$user) {
            return back()->with('error', __('admin.user_not_found'));
        }

        $active_page = "park";
        $page_title = "Parks";
        $breadcrumbs = collect([
            ['route' => route('admin.park.pendingimage'), 'name' => 'Pending Images']
        ]);

        $parkImages = Parkimage::where('user_id', $user->id)->where('park_id', $park->id)->where('status', '1')->where('is_archived', 0);
        $total_user_uploaded_image = clone $parkImages->orderBy('id', 'DESC')->get();
        $total_unverified_images = $total_unverified_image;
        $total_verified_images = $total_verified_image;
        $verifiedImages = Parkimage::where('user_id', $user->id)->where('park_id', $park->id)->where('is_verified', 1)->where('status', '1')->orderBy('id', 'DESC')->get();

        return view(
            'admin.park.pending_images.view',
            compact(
                'active_page',
                'page_title',
                'breadcrumbs',
                'park',
                'user',
                'total_user_uploaded_image',
                'verifiedImages',
                'total_verified_images',
                'total_unverified_images'
            )
        );
    }
    public function verify_unverifyimg(Request $request)
    {
        if ($request->ajax()) {
            // $parkimg = Parkimage::whereIn('id', $request->id)->where('is_archived', 0);

            $parkimg = Parkimage::whereIn('id', $request->id);
            $pendingImages = new PendingImages($request->user_id, $request->park_id);
            $park = Parks::findOrFail($request->park_id);
            switch ($request->status) {
                case 'unverify':
                    $parkimg->clone()->update(['is_verified' => 0]);
                    break;
                case 'verify':
                    $parkimg->clone()->update(['is_verified' => 1]);
                    break;
                case 'archive':
                    $parkimg->clone()->update(['is_archived' => 1]);
                    $pendingImages->delete();
                    return response()->json(['msg' => '', 'status' => 'archived']);
            }

            $pendingImages->update();
            $this->revalidateApi->revalidatePark($park);
            return response()->json(['msg' => 'Images is unverified successfully', 'status' => 1]);
        }
    }


    public function deleteUserUploadedImage(Request $request, $park_id, $user_id)
    {
        if ($request->ajax()) {

            $delete = ParkImage::whereIn('id', $request->id)->delete();
            $park = Parks::findOrFail($request->park_id);
            $pendingImg = new PendingImages($user_id, $park_id);
            $pendingImg->update();
            if ($delete) {
                $this->revalidateApi->revalidatePark($park);
                return response()->json([
                    'msg' => 'Successfully deleted',
                    'status' => $delete
                ]);
            }

            return response()->json([
                'msg' => 'Something is wrong',
                'status' => $delete
            ]);
        }
    }

    // public function deleteUserUploadedImage(Request $request, $userId)
    // {
    //     try {
    //         if ($request->ajax()) {
    //             DB::beginTransaction();

    //             $user = User::find($userId);

    //             $userParkImages= ParkImage::where('user_id',$userId)->get();
    //             foreach($userParkImages as $parkImg){
    //                 Media::find($parkImg->media_id)->delete();
    //             }

    //             foreach ($user->parkimages() as $parkImg) {
    //                 $parkImg->delete();
    //             }

    //             $user->pendingimages()->first()->delete();
    //             DB::commit();
    //             return response()->json([
    //                 'msg' => __('admin.delete_user_uploded_image'),
    //                 'status' => 1,

    //             ]);
    //         }
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //     }
    // }
}
