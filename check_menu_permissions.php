<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== Checking Menus ===" . PHP_EOL;
$menus = App\Models\Menu::all();
foreach ($menus as $menu) {
    echo "Menu: " . $menu->title . " (ID: " . $menu->id . ")" . PHP_EOL;
}

echo PHP_EOL . "=== Checking for অগ্রিম চালান Menu ===" . PHP_EOL;
$advanceInvoiceMenu = App\Models\Menu::where('title', 'অগ্রিম চালান')->first();
if ($advanceInvoiceMenu) {
    echo "Found অগ্রিম চালান menu with ID: " . $advanceInvoiceMenu->id . PHP_EOL;
    
    echo "Permissions for this menu:" . PHP_EOL;
    $menuPermissions = App\Models\Permission::where('menu_id', $advanceInvoiceMenu->id)->get();
    if ($menuPermissions->count() > 0) {
        foreach ($menuPermissions as $perm) {
            echo "  - " . $perm->name . PHP_EOL;
        }
    } else {
        echo "  No permissions found for this menu." . PHP_EOL;
        
        // Create permissions for this menu
        echo "Creating permissions for অগ্রিম চালান..." . PHP_EOL;
        $permissionNames = ['view', 'create', 'edit', 'delete'];
        foreach ($permissionNames as $permName) {
            $fullPermissionName = 'অগ্রিম_চালান_' . $permName;
            
            // Check if permission already exists
            $existingPermission = App\Models\Permission::where('name', $fullPermissionName)->first();
            if (!$existingPermission) {
                App\Models\Permission::create([
                    'name' => $fullPermissionName,
                    'menu_id' => $advanceInvoiceMenu->id,
                ]);
                echo "  Created permission: " . $fullPermissionName . PHP_EOL;
            } else {
                echo "  Permission already exists: " . $fullPermissionName . PHP_EOL;
            }
        }
    }
} else {
    echo "অগ্রিম চালান menu not found!" . PHP_EOL;
}
