<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AdminPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Menu permissions
        Permission::firstOrCreate(['name' => 'menus.index']);
        Permission::firstOrCreate(['name' => 'menus.create']);
        Permission::firstOrCreate(['name' => 'menus.edit']);
        Permission::firstOrCreate(['name' => 'menus.delete']);

        // Role permissions
        Permission::firstOrCreate(['name' => 'roles.index']);
        Permission::firstOrCreate(['name' => 'roles.create']);
        Permission::firstOrCreate(['name' => 'roles.edit']);
        Permission::firstOrCreate(['name' => 'roles.delete']);

        // Permission permissions
        Permission::firstOrCreate(['name' => 'permissions.index']);
        Permission::firstOrCreate(['name' => 'permissions.create']);
        Permission::firstOrCreate(['name' => 'permissions.edit']);
        Permission::firstOrCreate(['name' => 'permissions.delete']);

        // User permissions
        Permission::firstOrCreate(['name' => 'users.index']);
        Permission::firstOrCreate(['name' => 'users.create']);
        Permission::firstOrCreate(['name' => 'users.edit']);
        Permission::firstOrCreate(['name' => 'users.delete']);

        // Dashboard permission
        Permission::firstOrCreate(['name' => 'dashboard.access']);

        echo "Admin permissions created successfully!\n";
    }
}
