<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Dashboard
        Menu::create([
            'title' => 'ড্যাশবোর্ড',
            'icon' => 'bx bx-home-circle',
            'route' => 'dashboard',
            'order' => 1,
        ]);

        // 2. চালান
        $invoice = Menu::create([
            'title' => 'চালান',
            'icon' => 'bx bx-receipt',
            'order' => 2,
        ]);
        $invoice->children()->createMany([
            ['title' => 'আজকের চালান', 'route' => 'invoices.index', 'order' => 1],
            ['title' => 'অগ্রিম চালান', 'route' => 'invoice.advance', 'order' => 2],
            ['title' => 'সব চালান', 'route' => 'invoice.all', 'order' => 3],
        ]);

        // 3. কাঁচা ইট প্রোডাকশন
        $production = Menu::create([
            'title' => 'কাঁচা ইট প্রোডাকশন',
            'icon' => 'bx bx-box',
            'order' => 3,
        ]);
        $production->children()->createMany([
            ['title' => 'আজকের প্রোডাকশন', 'route' => 'row-productions.index', 'order' => 1],
            ['title' => 'সব প্রোডাকশন', 'route' => 'row-productions.all', 'order' => 2],
        ]);

        // 4. ডেলিভারি
        $delivery = Menu::create([
            'title' => 'ডেলিভারি',
            'icon' => 'bx bx-rocket',
            'order' => 4,
        ]);
        $delivery->children()->createMany([
            ['title' => 'আজকের ডেলিভারি', 'route' => 'deliveries.index', 'order' => 1],
            ['title' => 'আজ ডেলিভারি যাবে', 'route' => 'invoice.advance', 'order' => 2],
            ['title' => 'সব ডেলিভারি লিস্ট', 'route' => 'delivery.all', 'order' => 3],
        ]);

        // 5. পেমেন্ট খাতা
        Menu::create([
            'title' => 'পেমেন্ট খাতা',
            'icon' => 'bx bx-bar-chart',
            'route' => 'payment-khata.index',
            'order' => 5,
        ]);

        // 6. লোড খাতা
        Menu::create([
            'title' => 'লোড খাতা',
            'icon' => 'bx bx-bar-chart',
            'route' => 'loads.index',
            'order' => 6,
        ]);

        // 7. আনলোড
        Menu::create([
            'title' => 'আনলোড',
            'icon' => 'bx bx-bar-chart',
            'route' => 'unloads.index',
            'order' => 7,
        ]);

        // 8. মালামাল স্টক
        Menu::create([
            'title' => 'মালামাল স্টক',
            'icon' => 'bx bx-bar-chart',
            'route' => 'assets.index',
            'order' => 8,
        ]);

        // 9. কাস্টমার
        Menu::create([
            'title' => 'কাস্টমার',
            'icon' => 'bx bx-user-circle',
            'route' => 'customers.index',
            'order' => 9,
        ]);

        // 10. গাড়ির হিসাব
        Menu::create([
            'title' => 'গাড়ির হিসাব',
            'icon' => 'bx bx-car',
            'route' => 'vehicles.dashboard',
            'order' => 10,
        ]);

        // 11. আবহাওয়া
        $weather = Menu::create([
            'title' => 'আবহাওয়া',
            'icon' => 'bx bx-cloud-light-rain',
            'order' => 11,
        ]);
        $weather->children()->createMany([
            [
                'title' => '১০ দিনের আবহাওয়া',
                'url' => 'https://weather.com/weather/tenday/l/3c8d640dae52e6fe935bb35b95ee5dfbb31b395253882e2578f216c762e22c62',
                'order' => 1,
            ],
            [
                'title' => 'আজকের আবহাওয়া',
                'url' => 'https://weather.com/weather/hourbyhour/l/3c8d640dae52e6fe935bb35b95ee5dfbb31b395253882e2578f216c762e22c62',
                'order' => 2,
            ],
        ]);

        // 12. সেটিং
        $setting = Menu::create([
            'title' => 'সেটিং',
            'icon' => 'bx bx-cog',
            'order' => 12,
        ]);
        $setting->children()->createMany([
            ['title' => 'শ্রেণি এবং রেট', 'route' => 'items.index', 'order' => 1],
            ['title' => 'খতিয়ান', 'route' => 'payment_head.index', 'order' => 2],
            ['title' => 'স্টক লিস্ট', 'route' => 'stock-lists.index', 'order' => 3],
            ['title' => 'মাঠ লিস্ট', 'route' => 'field-lists.index', 'order' => 4],
            ['title' => 'খতিয়ান', 'route' => 'payment_head.index', 'order' => 5],
            ['title' => 'ভাটার তথ্য', 'route' => 'business-store.index', 'order' => 6],
        ]);

        // 13. অ্যাডমিনিস্ট্রেশন
        $admin = Menu::create([
            'title' => 'অ্যাডমিনিস্ট্রেশন',
            'icon' => 'bx bx-shield-quarter',
            'order' => 13,
        ]);
        $admin->children()->createMany([
            ['title' => 'মেনু ম্যানেজমেন্ট', 'route' => 'admin.menus.index', 'order' => 1],
            ['title' => 'রোল ম্যানেজমেন্ট', 'route' => 'admin.roles.index', 'order' => 2],
            ['title' => 'ইউজার ম্যানেজমেন্ট', 'route' => 'admin.users.index', 'order' => 3],
            ['title' => 'পারমিশন ম্যানেজমেন্ট', 'route' => 'admin.permissions.index', 'order' => 4],
        ]);
    }
}
