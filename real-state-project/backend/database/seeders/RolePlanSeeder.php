<?php

namespace Database\Seeders;

use App\Models\Roleplan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = Role::select('id', 'name')->whereIn('name', ['agency', 'privatelandlord'])->get();
        foreach ($roles as $role) {
            Roleplan::firstOrCreate(
                ['role_id' => $role->id],
                [
                    'is_free'       => 0
                ]
            );
        }
    }
}
