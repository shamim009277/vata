<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Setting up admin role and permissions...\n";

// Create admin role
$adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

// Get all admin permissions
$adminPermissions = [
    'menus.index', 'menus.create', 'menus.edit', 'menus.delete',
    'roles.index', 'roles.create', 'roles.edit', 'roles.delete',
    'permissions.index', 'permissions.create', 'permissions.edit', 'permissions.delete',
    'users.index', 'users.create', 'users.edit', 'users.delete',
    'dashboard.access'
];

// Assign all permissions to admin role
foreach ($adminPermissions as $permissionName) {
    $permission = \App\Models\Permission::where('name', $permissionName)->first();
    if ($permission) {
        $adminRole->givePermissionTo($permission);
        echo "Assigned permission: $permissionName to admin role\n";
    }
}

// Assign admin role to first user
$firstUser = \App\Models\User::first();
if ($firstUser) {
    $firstUser->assignRole($adminRole);
    echo "Assigned admin role to user: " . $firstUser->email . "\n";
} else {
    echo "No users found in the database.\n";
}

echo "\nAdmin role setup completed successfully!\n";
