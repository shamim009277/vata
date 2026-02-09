<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class PermissionSyncSeeder extends Seeder
{
    public function run()
    {
        // 1. Sync from Menus
        $menus = Menu::all();

        foreach ($menus as $menu) {
            $permissionName = $menu->permission_name;

            if (empty($permissionName) && !empty($menu->route)) {
                $permissionName = $menu->route;
                $menu->update(['permission_name' => $permissionName]);
            }

            if (!empty($permissionName)) {
                $permission = Permission::firstOrCreate(['name' => $permissionName]);
                $permission->menu_id = $menu->id;
                $permission->save();
            }
        }
        
        // 2. Ensure standard resource permissions for known resources
        // These might not be in the menu directly but are needed for CRUD
        $resources = [
            'vehicles',
            'vehicle-trips',
            'vehicle-fuels',
            'vehicle-services',
            'delivery-challans',
            // Add others if they are not in menu but are resources
            'items', 'stock-lists', 'field-lists', 'payment_head', 'modules', 'invoices',
            'row-productions', 'deliveries', 'payment-khata', 'loads', 'unloads', 'assets', 'customers',
            'admin.menus', 'admin.roles', 'admin.permissions', 'admin.users'
        ];

        foreach ($resources as $resource) {
            $actions = ['index', 'create', 'edit', 'destroy'];
            
            // Find a menu that might be related (optional, for grouping)
            // e.g. 'vehicles' resource might relate to 'vehicles.dashboard' menu
            $relatedMenu = null;
            if (Str::startsWith($resource, 'vehicle')) {
                $relatedMenu = Menu::where('route', 'vehicles.dashboard')->first();
            } elseif ($resource === 'delivery-challans') {
                 $relatedMenu = Menu::where('route', 'delivery.all')->first(); // Approximation
            } else {
                 $relatedMenu = Menu::where('route', $resource.'.index')->first();
            }

            foreach ($actions as $action) {
                $permName = $resource . '.' . $action;
                $permission = Permission::firstOrCreate(['name' => $permName]);
                
                if ($relatedMenu) {
                    $permission->menu_id = $relatedMenu->id;
                    $permission->save();
                }
            }
        }

        echo "Permissions synced with Menus and Resources successfully.\n";
    }
}
