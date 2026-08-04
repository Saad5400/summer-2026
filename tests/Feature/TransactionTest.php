<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
    }

    public function test_transactions_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('transactions.index'));

        $response->assertOk();
    }

    public function test_guests_cannot_visit_transactions_page(): void
    {
        $response = $this->get(route('transactions.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_create_expense_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::where('type', 'expense')->first();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'amount' => 150.50,
            'description' => 'غداء في مطعم',
            'date' => '2026-07-15',
            'type' => 'expense',
            'category_id' => $category->id,
        ]);

        $response->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 150.50,
            'description' => 'غداء في مطعم',
            'type' => 'expense',
            'category_id' => $category->id,
        ]);
    }

    public function test_user_can_create_income_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::where('type', 'income')->first();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'amount' => 5000,
            'description' => 'راتب شهري',
            'date' => '2026-07-28',
            'type' => 'income',
            'category_id' => $category->id,
        ]);

        $response->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 5000,
            'type' => 'income',
            'category_id' => $category->id,
        ]);
    }

    public function test_user_can_update_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::where('type', 'expense')->first();
        $transaction = Transaction::factory()->for($user)->expense()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->put(route('transactions.update', $transaction), [
            'amount' => 200,
            'description' => 'مطعم فاخر',
            'date' => '2026-07-20',
            'type' => 'expense',
            'category_id' => $category->id,
        ]);

        $response->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 200,
            'description' => 'مطعم فاخر',
        ]);
    }

    public function test_user_cannot_update_other_users_transaction(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $category = Category::where('type', 'expense')->first();
        $transaction = Transaction::factory()->for($user1)->expense()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user2)->put(route('transactions.update', $transaction), [
            'amount' => 999,
            'description' => 'محاولة اختراق',
            'date' => '2026-07-20',
            'type' => 'expense',
            'category_id' => $category->id,
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::where('type', 'expense')->first();
        $transaction = Transaction::factory()->for($user)->expense()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->delete(route('transactions.destroy', $transaction));

        $response->assertRedirect(route('transactions.index'));

        $this->assertSoftDeleted($transaction);
    }

    public function test_user_cannot_delete_other_users_transaction(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $category = Category::where('type', 'expense')->first();
        $transaction = Transaction::factory()->for($user1)->expense()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user2)->delete(route('transactions.destroy', $transaction));

        $response->assertForbidden();
    }

    public function test_transaction_validation_requires_amount(): void
    {
        $user = User::factory()->create();
        $category = Category::where('type', 'expense')->first();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'description' => 'بدون مبلغ',
            'date' => '2026-07-15',
            'type' => 'expense',
            'category_id' => $category->id,
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_transaction_validation_requires_type(): void
    {
        $user = User::factory()->create();
        $category = Category::where('type', 'expense')->first();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'amount' => 100,
            'description' => 'نوع غير صحيح',
            'date' => '2026-07-15',
            'type' => 'invalid_type',
            'category_id' => $category->id,
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_transactions_can_be_filtered_by_type(): void
    {
        $user = User::factory()->create();
        $expenseCategory = Category::where('type', 'expense')->first();
        $incomeCategory = Category::where('type', 'income')->first();

        Transaction::factory()->for($user)->expense()->create(['category_id' => $expenseCategory->id]);
        Transaction::factory()->for($user)->expense()->create(['category_id' => $expenseCategory->id]);
        Transaction::factory()->for($user)->income()->create(['category_id' => $incomeCategory->id]);

        $response = $this->actingAs($user)->get(route('transactions.index', ['type' => 'expense']));

        $response->assertOk();

        $transactions = $response->inertiaProps('transactions')['data'];
        $this->assertCount(2, $transactions);
        foreach ($transactions as $t) {
            $this->assertEquals('expense', $t['type']);
        }
    }

    public function test_transactions_can_be_filtered_by_category(): void
    {
        $user = User::factory()->create();
        $cat1 = Category::where('type', 'expense')->first();
        $cat2 = Category::where('type', 'expense')->where('id', '!=', $cat1->id)->first();

        Transaction::factory()->for($user)->expense()->create(['category_id' => $cat1->id]);
        Transaction::factory()->for($user)->expense()->create(['category_id' => $cat2->id]);
        Transaction::factory()->for($user)->expense()->create(['category_id' => $cat2->id]);

        $response = $this->actingAs($user)->get(route('transactions.index', ['category_id' => $cat2->id]));

        $response->assertOk();

        $transactions = $response->inertiaProps('transactions')['data'];
        $this->assertCount(2, $transactions);
        foreach ($transactions as $t) {
            $this->assertEquals($cat2->id, $t['category_id']);
        }
    }

    public function test_transactions_can_be_searched(): void
    {
        $user = User::factory()->create();
        $category = Category::where('type', 'expense')->first();

        Transaction::factory()->for($user)->expense()->create([
            'category_id' => $category->id,
            'description' => 'فطور في البيك',
        ]);
        Transaction::factory()->for($user)->expense()->create([
            'category_id' => $category->id,
            'description' => 'قهوة الصباح',
        ]);
        Transaction::factory()->for($user)->expense()->create([
            'category_id' => $category->id,
            'description' => 'غداء البيك مرة أخرى',
        ]);

        $response = $this->actingAs($user)->get(route('transactions.index', ['search' => 'البيك']));

        $response->assertOk();

        $transactions = $response->inertiaProps('transactions')['data'];
        $this->assertCount(2, $transactions);
    }

    public function test_transactions_are_paginated(): void
    {
        $user = User::factory()->create();
        $category = Category::where('type', 'expense')->first();

        Transaction::factory()->for($user)->expense()->count(20)->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get(route('transactions.index'));

        $response->assertOk();

        $transactionsProp = $response->inertiaProps('transactions');
        $this->assertCount(15, $transactionsProp['data']);
        $this->assertArrayHasKey('per_page', $transactionsProp);
        $this->assertArrayHasKey('total', $transactionsProp);
        $this->assertEquals(20, $transactionsProp['total']);
    }
}
