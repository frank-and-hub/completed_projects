<?php

namespace App\Console\Commands;

use App\Models\Parks;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CreateRatings extends Command
{

    protected $signature = 'create:ratings';

    protected $description = "\033[03;33m This command only for create dummy rating for testing purpose only dev or local server not live ! \033[0m \n";



    public function handle()
    {
        try {

            $users = User::role('user')->WhereNotNull('email_verified_at')->orderBy('id','DESC')->limit(10)->get();
            $parks = Parks::where('active',1)->orderBy('id','DESC')->limit(10)->get();
            $faker = \Faker\Factory::create();
            DB::beginTransaction();
            foreach($users as $user){

                foreach($parks as $park){
                    $rating = rand(1,5);
                    Rating::UpdateOrCreate(['user_id'=>$user->id,'park_id'=>$park->id],['user_id'=>$user->id,'park_id'=>$park->id,
                    'review'=>$faker->text, 'rating'=>$rating]);
                }
            }
            DB::commit();
            echo "\033[01;32m  New dummy ratings created successfully ! ... ✅ \033[0m\n";


        } catch (\Exception $e) {
            Log::error($e);
            echo $e;
            DB::rollBack();
        }
    }
}
