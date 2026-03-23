<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class GrantDueKhataPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'due.today_deposit',
            'due.will_deposit_today',
            'due.all',
        ];

        foreach ($perms as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        // Grant to admin role
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($perms);
        }

        // If Super Admin role exists explicitly, grant too (Gate::before already allows, but safe)
        $super = Role::where('name', 'Super Admin')->first();
        if ($super) {
            $super->givePermissionTo($perms);
        }
    }
}

