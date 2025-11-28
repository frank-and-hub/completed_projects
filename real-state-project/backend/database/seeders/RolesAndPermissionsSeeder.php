<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Role::firstorcreate(['name' => 'user'],['name' => 'user', 'guard_name' => 'web']);
        Role::firstorcreate(['name' => 'admin'],['name' => 'admin', 'guard_name' => 'admin']);
        Role::firstorcreate(['name' => 'agent'],['name' => 'agent', 'guard_name' => 'admin']);
        Role::firstorcreate(['name' => 'agency'],['name' => 'agency', 'guard_name' => 'admin']);
        Role::firstorcreate(['name' => 'privatelandlord'],['name' => 'privatelandlord', 'guard_name' => 'admin']);

        
        if($admin = Admin::where('email', 'admin@gmail.com')->first()){
            $admin->assignRole('admin');
        }
        
        Role::where('name', 'super admin')->delete();
        
    }
}
