<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                "name" => "admin",
            ],
            [
                "name" => "user",
            ],
            [
                "name" => "subadmin",
            ],

        ];

        foreach ($roles as $role) {
            Role::firstOrCreate($role);
        }

        $permissions = [
            [
                "name" => "park-create",
            ],
            [
                "name" => "park-edit",
            ],
            [
                "name" => "park-delete",
            ],
            [
                "name" => "park-show",
            ],
            [
                "name" => "park-active",
            ],
            [
                "name" => "users-show",
            ],
            [
                "name" => "categories-show",
            ],
            [
                "name" => "features-show",
            ],
            [
                "name" => "custom-page-show",
            ],
            [
                "name" => "dashboard-show",
            ],
            [
                "name" => "show-sub-admins",
            ],
        ];

        foreach ($permissions as $permission) {
            $per = Permission::firstOrCreate($permission);

            $role = Role::where('name', 'admin')->first();
            $per->assignRole($role);

            $role = Role::where('name', 'subadmin')->first();
            if ($permission['name'] == 'park-create' ||$permission['name'] == 'park-show' || $permission['name'] == 'dashboard-show' ) {
                $per->assignRole($role);
            }
        }
    }
}
