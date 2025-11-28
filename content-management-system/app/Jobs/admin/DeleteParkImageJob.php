<?php

namespace App\Jobs\admin;

use App\Models\Media;
use App\Models\ParkImage;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteParkImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }


    public function handle()
    {
        $parkimages = ParkImage::where('status', '0')->where('park_id', $this->id);
        $media_id =  collect($parkimages->pluck('media_id'));
        Media::whereIn('id', $media_id)->delete();
        $parkimages->delete();

    }
}
