<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = [
            "name" => "Administrator",
            "email" => "admin@parkscape.com",
            "email_verified_at" => now(),
            "password" => Hash::make("12345678"),
        ];

        $admin = \App\Models\User::firstOrCreate(["email" => $admin['email']], $admin);

        $admin->assignRole('admin');
    }
}
