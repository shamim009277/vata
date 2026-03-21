<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class ChallanReportMenuSeeder extends Seeder
{
    public function run(): void
    {
        $reportParent = Menu::where('title', 'রিপোর্ট')->first()
            ?? Menu::where('title', 'Reports')->first();

        if (! $reportParent) {
            $reportParent = Menu::create([
                'title' => 'রিপোর্ট',
                'icon'  => 'bx bx-bar-chart',
                'order' => 50,
                'is_active' => true,
            ]);
        }

        $exists = Menu::where('title', 'চালান রিপোর্ট')
            ->where('parent_id', $reportParent->id)
            ->first();

        if (! $exists) {
            Menu::create([
                'title' => 'চালান রিপোর্ট',
                'route' => 'reports.challan',
                'icon'  => 'bx bx-receipt',
                'parent_id' => $reportParent->id,
                'order' => 2,
                'permission_name' => null,
                'is_active' => true,
            ]);
        }
    }
}

