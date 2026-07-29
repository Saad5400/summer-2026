<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'طعام ومشروبات', 'type' => 'expense', 'icon' => 'utensils', 'color' => '#ef4444', 'sort_order' => 1],
            ['id' => 2, 'name' => 'مواصلات', 'type' => 'expense', 'icon' => 'car', 'color' => '#f97316', 'sort_order' => 2],
            ['id' => 3, 'name' => 'سكن', 'type' => 'expense', 'icon' => 'home', 'color' => '#eab308', 'sort_order' => 3],
            ['id' => 4, 'name' => 'فواتير', 'type' => 'expense', 'icon' => 'receipt', 'color' => '#22c55e', 'sort_order' => 4],
            ['id' => 5, 'name' => 'ترفيه', 'type' => 'expense', 'icon' => 'gamepad', 'color' => '#06b6d4', 'sort_order' => 5],
            ['id' => 6, 'name' => 'تسوق', 'type' => 'expense', 'icon' => 'shopping-bag', 'color' => '#8b5cf6', 'sort_order' => 6],
            ['id' => 7, 'name' => 'صحة', 'type' => 'expense', 'icon' => 'heart', 'color' => '#ec4899', 'sort_order' => 7],
            ['id' => 8, 'name' => 'تعليم', 'type' => 'expense', 'icon' => 'book-open', 'color' => '#6366f1', 'sort_order' => 8],
            ['id' => 9, 'name' => 'أخرى', 'type' => 'expense', 'icon' => 'more-horizontal', 'color' => '#6b7280', 'sort_order' => 9],
            ['id' => 10, 'name' => 'راتب', 'type' => 'income', 'icon' => 'briefcase', 'color' => '#16a34a', 'sort_order' => 1],
            ['id' => 11, 'name' => 'عمل حر', 'type' => 'income', 'icon' => 'laptop', 'color' => '#0ea5e9', 'sort_order' => 2],
            ['id' => 12, 'name' => 'استثمار', 'type' => 'income', 'icon' => 'trending-up', 'color' => '#7c3aed', 'sort_order' => 3],
            ['id' => 13, 'name' => 'هدية', 'type' => 'income', 'icon' => 'gift', 'color' => '#d946ef', 'sort_order' => 4],
            ['id' => 14, 'name' => 'أخرى', 'type' => 'income', 'icon' => 'more-horizontal', 'color' => '#6b7280', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            Category::insertOrIgnore($category);
        }
    }
}
