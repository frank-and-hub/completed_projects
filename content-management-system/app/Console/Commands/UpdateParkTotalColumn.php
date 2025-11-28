<?php

namespace App\Console\Commands;

use App\Models\ParkImage;
use App\Models\Parks;
use Illuminate\Console\Command;

class UpdateParkTotalColumn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:totalcolumn';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $parks_ids = collect(Parks::pluck('id'))->toArray();
        $parkimages = ParkImage::whereNotNull('user_id')->get();
        $parks = Parks::get();
        foreach ($parks as $park) {
            $query = $park->park_images()->whereNotNull('user_id');
            $total_img = $query->clone()->count();
            $total_verified_img = $query->clone()->where('is_verified', 1)->count();
            $total_pending_img = $query->clone()->where('is_verified', 0)->count();
            // echo"\n";
            // echo "total_img:".$total_img;
            // echo"\n";
            // echo "total_pending:".$total_pending_img;
            // echo"\n";
            // echo"total_verified:".$total_verified_img;

            Parks::where('id', $park->id)->update([
                'total_user_image' => $total_img,
                'total_user_pending_image' => $total_pending_img,
                'total_user_verified_image' => $total_verified_img
            ]);
        }
        echo "\033[01;32m Successfully updated total_user_image, total_user_pending_image and total_user_verified_image columns  ... ✅ \033[0m\n";

    }
}
