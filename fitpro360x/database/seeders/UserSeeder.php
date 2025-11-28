<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inputs = [
            [
                'fullname' => 'admin',
                'email' => 'adminds@yopmail.com',
                'password' => Hash::make('admin123'),
                'role' => 1,
            ],
        ];

        User::insert($inputs);
    }
}
