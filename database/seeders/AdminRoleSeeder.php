<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class AdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create or get the admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Get all admin permissions
        $permissions = Permission::whereIn('name', [
            'menus.index', 'menus.create', 'menus.edit', 'menus.delete',
            'roles.index', 'roles.create', 'roles.edit', 'roles.delete',
            'permissions.index', 'permissions.create', 'permissions.edit', 'permissions.delete',
            'users.index', 'users.create', 'users.edit', 'users.delete',
            'dashboard.access'
        ])->get();

        // Assign all permissions to admin role
        $adminRole->syncPermissions($permissions);

        // Assign admin role to first user (or create a default admin user)
        $adminUser = User::first();
        if ($adminUser) {
            $adminUser->assignRole('admin');
            echo "Admin role assigned to user: {$adminUser->name}\n";
        }

        echo "Admin role and permissions configured successfully!\n";
    }
}
