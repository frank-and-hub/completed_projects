<?php

namespace App\Console\Commands;

use App\Models\ParkImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class UpdateSubadminParkImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:parkimage';

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
        DB::beginTransaction();
        $Parkimages = ParkImage::whereNotNull('user_id')->where('is_verified',0)->where('status','1')->get();
        foreach($Parkimages as $parkImg){
            if($parkImg->user->hasRole('subadmin')){
                $parkImg->update(['is_verified'=>1]);
            }
        }


        DB::commit();
        echo "\033[01;32m  ParkImage Table Updated Successfully ! ... ☑️ \033[0m\n";

    }
}
