<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RegularInvoiceMenuSeeder extends Seeder
{
    public function run(): void
    {
        $parent = Menu::where('title', 'চালান')->first()
            ?? Menu::where('title', 'Invoice')->first();

        if (! $parent) {
            $parent = Menu::create([
                'title' => 'চালান',
                'icon'  => 'bx bx-receipt',
                'order' => 2,
                'is_active' => true,
            ]);
        }

        $menu = Menu::where('title', 'রেগুলার চালান')
            ->where('parent_id', $parent->id)
            ->first();

        if (! $menu) {
            $menu = Menu::create([
                'title' => 'রেগুলার চালান',
                'route' => 'invoice.regular',
                'icon'  => 'bx bx-receipt',
                'parent_id' => $parent->id,
                'order' => 2,
                'permission_name' => 'invoice.regular',
                'is_active' => true,
            ]);
        }

        // Ensure permission exists and links to this menu
        $perm = Permission::where('name', 'invoice.regular')->first();
        if (! $perm) {
            Permission::create([
                'name' => 'invoice.regular',
                'guard_name' => 'web',
                'menu_id' => $menu->id,
            ]);
        } else {
            if ($perm->menu_id !== $menu->id) {
                $perm->update(['menu_id' => $menu->id]);
            }
        }
    }
}
