<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class RawBrickProductionMenuSeeder extends Seeder
{
    public function run(): void
    {
        $reportParent = Menu::where('title', 'রিপোর্ট')->first()
            ?? Menu::where('title', 'Reports')->first();

        if (! $reportParent) {
            // Create parent if not exists
            $reportParent = Menu::create([
                'title' => 'রিপোর্ট',
                'icon'  => 'bx bx-bar-chart',
                'order' => 50,
            ]);
        }

        // Check if child already exists
        $exists = Menu::where('title', 'কাঁচা ইট প্রোডাকশন')
            ->where('parent_id', $reportParent->id)
            ->first();

        if (! $exists) {
            Menu::create([
                'title' => 'কাঁচা ইট প্রোডাকশন',
                'route' => 'reports.raw_brick_production',
                'icon'  => 'bx bx-box',
                'parent_id' => $reportParent->id,
                'order' => 3,
                // Optional: tie to existing reports permission, or leave null for all
                'permission_name' => null,
                'is_active' => true,
            ]);
        }
    }
}

