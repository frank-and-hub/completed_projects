<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'adminds@yopmail.com'], [
            'fullname' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 1,
        ]);
    }
}
