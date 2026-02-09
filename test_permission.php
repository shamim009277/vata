<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $permissions = App\Models\Permission::with('menu')->get();
    echo "Permissions count: " . $permissions->count() . "\n";
    if ($permissions->isNotEmpty()) {
        $p = $permissions->first();
        echo "First permission: " . $p->name . "\n";
        echo "Menu: " . ($p->menu ? $p->menu->title : 'null') . "\n";
    }
    echo "Success";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
