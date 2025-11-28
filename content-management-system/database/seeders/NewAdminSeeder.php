<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class NewAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = [
            "name" => "Priyanka",
            "email" => "priyanka@pairroxz.com",
            "email_verified_at" => now(),
            "password" => Hash::make("Queenqueen1"),
        ];

        $admin = \App\Models\User::firstOrCreate(["email" => $admin['email']], $admin);

        $admin->assignRole('admin');

        $admin1 = [
            "name" => "Sonali",
            "email" => "sonali.temani@pairroxz.in",
            "email_verified_at" => now(),
            "password" => Hash::make("Queenqueen1"),
        ];

        $admin1 = \App\Models\User::firstOrCreate(["email" => $admin1['email']], $admin1);

        $admin1->assignRole('admin');
    }
}
