<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class create_admin extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = [
            'name' => 'Admin',
            'phone' => '123456789',
            'dial_code' => '+27',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin@123'),
            'country'   => 'South Africa',
            'timeZone'  => 'Africa/Johannesburg'
        ];

        $admin = \App\Models\Admin::firstOrCreate(["email" => $admin['email']], $admin);

        $admin->assignRole('admin');
    }
}
