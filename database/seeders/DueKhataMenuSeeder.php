<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class DueKhataMenuSeeder extends Seeder
{
    public function run(): void
    {
        // Create parent "বাকি খাতা"
        $parent = Menu::where('title', 'বাকি খাতা')->first();
        if (! $parent) {
            $parent = Menu::create([
                'title' => 'বাকি খাতা',
                'icon'  => 'bx bx-wallet',
                'order' => 7, // place after existing groups
                'is_active' => true,
            ]);
        }

        // Child: আজকের জমা
        $todayMenu = Menu::where('title', 'আজকের জমা')->where('parent_id', $parent->id)->first();
        if (! $todayMenu) {
            $todayMenu = Menu::create([
                'title' => 'আজকের জমা',
                'route' => 'due.today_deposit',
                'icon'  => 'bx bx-calendar-check',
                'parent_id' => $parent->id,
                'order' => 1,
                'permission_name' => 'due.today_deposit',
                'is_active' => true,
            ]);
        }
        Permission::firstOrCreate(['name' => 'due.today_deposit', 'guard_name' => 'web'])->update(['menu_id' => $todayMenu->id]);

        // Child: আজ জমা দেবে
        $willTodayMenu = Menu::where('title', 'আজ জমা দেবে')->where('parent_id', $parent->id)->first();
        if (! $willTodayMenu) {
            $willTodayMenu = Menu::create([
                'title' => 'আজ জমা দেবে',
                'route' => 'due.will_deposit_today',
                'icon'  => 'bx bx-time-five',
                'parent_id' => $parent->id,
                'order' => 2,
                'permission_name' => 'due.will_deposit_today',
                'is_active' => true,
            ]);
        }
        Permission::firstOrCreate(['name' => 'due.will_deposit_today', 'guard_name' => 'web'])->update(['menu_id' => $willTodayMenu->id]);

        // Child: সব বাকি লিস্ট
        $allDueMenu = Menu::where('title', 'সব বাকি লিস্ট')->where('parent_id', $parent->id)->first();
        if (! $allDueMenu) {
            $allDueMenu = Menu::create([
                'title' => 'সব বাকি লিস্ট',
                'route' => 'due.all',
                'icon'  => 'bx bx-list-ul',
                'parent_id' => $parent->id,
                'order' => 3,
                'permission_name' => 'due.all',
                'is_active' => true,
            ]);
        }
        Permission::firstOrCreate(['name' => 'due.all', 'guard_name' => 'web'])->update(['menu_id' => $allDueMenu->id]);
    }
}

