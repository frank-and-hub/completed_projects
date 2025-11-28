<?php

namespace App\Console\Commands;

use App\Models\ParkImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdatedParkImgTempId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:tempid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command use for update img_tmp_id for park image table';

    /**
     * Execute the console command.
     *
     * @return int
     */

    public static function randomTmpId()
    {

      return  bin2hex(random_bytes(8));
        
    }
    public function handle()
    {
        try {
            DB::beginTransaction();
            $park_img =  ParkImage::whereNull('img_tmp_id')->whereNotNull('user_id')->get();
            foreach ($park_img as $img) {
                $img->update(
                    [
                        'img_tmp_id' => $this->randomTmpId(),
                    ]
                );
            }

            echo "\033[01;32m  ParkImage Img Tmp Id Updated successfully ! ... ✅ \033[0m\n";
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }
}
