<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'amount' => fake()->randomFloat(2, 1, 5000),
            'description' => fake()->sentence(3),
            'date' => fake()->date(),
            'type' => fake()->randomElement(['expense', 'income']),
            'is_anomaly' => false,
        ];
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
        ]);
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
        ]);
    }

    public function uncategorized(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => null,
        ]);
    }

    public function anomalous(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_anomaly' => true,
            'amount' => fake()->randomFloat(2, 10000, 50000),
        ]);
    }
}
