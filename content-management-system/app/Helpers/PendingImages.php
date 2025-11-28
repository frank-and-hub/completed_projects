<?php

namespace App\Helpers;

use App\Models\ParkImage;
use App\Models\Pendingimage;
use Illuminate\Support\Facades\DB;

class PendingImages
{
    public $pending_img;

    public function __construct(public int $user_id, public int $park_id)
    {

        $this->pending_img = Pendingimage::query();
    }

    public function getData()
    {
        $pendingImg = $this->pending_img->select('*', DB::raw("
        (pendingimages.total_pending_image+total_verify_image) as totalImg
        "))->where('park_id', $this->park_id)->where('user_id', $this->user_id);

        $totalImg = $pendingImg->clone()->value('totalImg');
        $totalVerified = $pendingImg->clone()->value('total_verify_image');
        $totalPending = $pendingImg->clone()->value('total_pending_image');

        $data = collect([
            'totalImg' => $totalImg,
            'totalVerifiedImg' => $totalVerified,
            'totalPendingImg' => $totalPending
        ]);

        return $data;
    }

    public function update()
    {

        $parkimg = ParkImage::Where('park_id', $this->park_id)->where('user_id', $this->user_id)->where('is_archived', false);
        if ($parkimg->count()>0) {
            $this->pending_img->where('park_id', $this->park_id)->where('user_id', $this->user_id)
                ->update([
                    'total_verify_image' => $parkimg->clone()->where('is_verified', true)->count(),
                    'total_pending_image' => $parkimg->clone()->where('is_verified', false)->count(),
                ]);
        }else{
            $this->pending_img->where('park_id', $this->park_id)->where('user_id', $this->user_id)->delete();
        }
    }

    public function delete()
    {
        return $this->pending_img->where('park_id', $this->park_id)->where('user_id', $this->user_id)->delete();
    }


}
