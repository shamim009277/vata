<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class GrantRegularInvoicePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permission = Permission::firstOrCreate([
            'name' => 'invoice.regular',
            'guard_name' => 'web',
        ]);

        // Grant to 'admin' role if exists
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($permission);
        }

        // Optionally grant to 'Super Admin' role if used explicitly
        $super = Role::where('name', 'Super Admin')->first();
        if ($super) {
            $super->givePermissionTo($permission);
        }
    }
}

