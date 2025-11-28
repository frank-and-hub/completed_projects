<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CreateUser extends Command
{
    protected $signature = 'create:users';

    protected $description = "\033[03;33m This command only for making dummy users for testing purpose. Use this command only dev or local server not live ! \033[0m \n";

    public function handle()
    {
        try {
            DB::beginTransaction();
            $faker = \Faker\Factory::create();
            for ($i = 0; $i < 10; $i++) {
               $user =  User::create([
                    'name' => $faker->firstName . " " . $faker->lastName,
                    'email' => $faker->email,
                    'password'=>Hash::make('Qwerty12345'),
                    'email_verified_at'=>Carbon::now(),
                ]);
                $user->assignRole('user');
            }
            DB::commit();
            echo "\033[01;32m  New dummy users created successfully ! ... ✅ \033[0m\n";
        } catch (\Exception $e) {
            Log::error($e);
            echo $e;
            DB::rollBack();
        }
    }
}
