<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $icons = ['utensils', 'car', 'home', 'receipt', 'gamepad', 'shopping-bag', 'heart', 'book-open', 'more-horizontal', 'briefcase', 'laptop', 'trending-up', 'gift'];
        $colors = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#06b6d4', '#8b5cf6', '#ec4899', '#6366f1', '#6b7280', '#16a34a', '#0ea5e9', '#7c3aed', '#d946ef'];

        return [
            'name' => fake()->unique()->word(),
            'type' => fake()->randomElement(['expense', 'income']),
            'icon' => fake()->randomElement($icons),
            'color' => fake()->randomElement($colors),
            'sort_order' => fake()->numberBetween(0, 99),
        ];
    }
}
