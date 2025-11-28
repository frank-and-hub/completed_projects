<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeader extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $data = [];
        foreach (range(1, 100) as $index) {
            $data[] = [
                'name' => $faker->name,
                'email' => $faker->email,
                'password' => Hash::make($faker->password),
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ];
        }
        $data[] = [
            'name' => $faker->name,
            'email' => 'admin@mail.com',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
            'role' => '1'
        ];
        // dd($data);
        User::insert($data);
    }
}
