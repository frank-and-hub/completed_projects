<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->adminSeed();

        $this->call([
            CountryStateCitySeeder::class,
        ]);
    }

    public function adminSeed()
    {

        DB::table('users')->insert([
            'role_id' => Role::where('name', '=', 'admin')->pluck('id')->first(),
            'first_name' => 'CSR Admin',
            'last_name' => '',
            'email' => 'csradmin@yopmail.com',
            'password' => Hash::make('admin@csr'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->command->info('Admin Done');
    }
}
