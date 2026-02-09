<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Permission;

echo "Checking permissions with null menu_id (Others)...\n";

$permissions = Permission::whereNull('menu_id')->get();

echo "Found " . $permissions->count() . " permissions:\n";
foreach ($permissions as $p) {
    echo "- " . $p->name . "\n";
}

if ($permissions->count() > 0) {
    Permission::whereNull('menu_id')->delete();
    echo "\nDeleted all permissions with null menu_id.\n";
} else {
    echo "\nNo permissions to delete.\n";
}
