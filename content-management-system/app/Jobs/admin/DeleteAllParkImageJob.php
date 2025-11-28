<?php

namespace App\Jobs\admin;

use App\Models\Media;
use App\Models\ParkImage;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteAllParkImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $parkimages = ParkImage::where('status', '0')->whereDate('created_at','<',Carbon::today()->toDateString());
            $media_id =  collect($parkimages->pluck('media_id'));
            Media::whereIn('id', $media_id)->delete();
            $parkimages->delete();
        }
        catch(Exception $e){
            Log::error($e);
        }


    }
}
