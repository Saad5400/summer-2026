<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('reports'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_reports_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('reports'));

        $response->assertOk();
    }

    public function test_expense_by_category_is_correctly_aggregated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::factory()->create(['type' => 'expense']);

        Transaction::factory()->for($user)->create([
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 100.00,
            'date' => now(),
        ]);
        Transaction::factory()->for($user)->create([
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 50.00,
            'date' => now(),
        ]);

        $response = $this->get(route('reports'));

        $response->assertOk();
        $props = $response->inertiaProps();
        $this->assertCount(1, $props['expenseByCategory']);
        $this->assertEquals($category->name, $props['expenseByCategory'][0]['name']);
        $this->assertEquals(150.0, $props['expenseByCategory'][0]['total']);
    }

    public function test_monthly_comparison_has_six_months(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::factory()->create(['type' => 'expense']);

        $response = $this->get(route('reports'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('monthlyComparison', 6)
        );
    }

    public function test_spending_trends_has_twelve_months(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('reports'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('spendingTrends', 12)
        );
    }

    public function test_available_months_returns_unique_year_month_pairs(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::factory()->create(['type' => 'expense']);

        Transaction::factory()->for($user)->create([
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 10,
            'date' => now()->subMonths(2),
        ]);
        Transaction::factory()->for($user)->create([
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => 20,
            'date' => now()->subMonths(2),
        ]);
        Transaction::factory()->for($user)->create([
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 30,
            'date' => now(),
        ]);

        $response = $this->get(route('reports'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('availableMonths', 2)
        );
    }

    public function test_summary_shows_current_month_totals(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::factory()->create(['type' => 'expense']);

        Transaction::factory()->for($user)->create([
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 100,
            'date' => now(),
        ]);
        Transaction::factory()->for($user)->create([
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => 500,
            'date' => now(),
        ]);
        Transaction::factory()->for($user)->create([
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 50,
            'date' => now()->subMonths(2),
        ]);

        $response = $this->get(route('reports'));

        $response->assertOk();
        $props = $response->inertiaProps();
        $this->assertEquals(100.0, $props['summary']['expenses']);
        $this->assertEquals(500.0, $props['summary']['income']);
    }
}
