<?php

namespace App\Console\Commands;

use App\Models\ParkImage;
use App\Models\Pendingimage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdatePendingImageTbl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:pendingimgtbl';

    /**
     * The console command description.up
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
        try {
            Pendingimage::whereNotNull('id')->delete();
            DB::beginTransaction();
            $parkimages =  ParkImage::whereNotNull('user_id')
                ->groupBy('user_id', 'park_id')->get();
            foreach($parkimages as $parkimage){
                $ParkImg = Parkimage::Where('user_id',$parkimage->user_id)->where('park_id',$parkimage->park_id);
                Pendingimage::firstOrCreate([
                    'park_id' => $parkimage->park_id,
                    'user_id' => $parkimage->user_id,
                    'total_pending_image' => $ParkImg->clone()->where('is_verified',false)->count(),
                    'total_verify_image' => $ParkImg->clone()->where('is_verified',true)->count(),
                ]);
            }



            DB::commit();
            echo "\033[01;32m  Pendingimages Table Updated successfully ! ... ✅ \033[0m\n";

        } catch (\Exception $e) {
            //throw $th;
            DB::rollback();
        }
    }
}
