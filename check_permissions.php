<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Permission;

$permissions = Permission::all();
echo "Total Permissions: " . $permissions->count() . "\n";
foreach ($permissions as $p) {
    echo $p->name . " (Menu ID: " . ($p->menu_id ?? 'NULL') . ")\n";
}
