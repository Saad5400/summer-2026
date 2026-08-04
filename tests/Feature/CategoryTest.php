<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
    }

    public function test_categories_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('categories.index'));

        $response->assertOk();
    }

    public function test_guests_cannot_visit_categories_page(): void
    {
        $response = $this->get(route('categories.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_create_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'اشتراكات منصات',
            'type' => 'expense',
            'icon' => 'monitor',
            'color' => '#ff0000',
            'sort_order' => 10,
        ]);

        $response->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'اشتراكات منصات',
            'type' => 'expense',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_update_own_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create([
            'name' => 'الاسم القديم',
            'type' => 'expense',
        ]);

        $response = $this->actingAs($user)->put(route('categories.update', $category), [
            'name' => 'الاسم الجديد',
            'type' => 'expense',
        ]);

        $response->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'الاسم الجديد',
        ]);
    }

    public function test_user_cannot_update_system_category(): void
    {
        $user = User::factory()->create();
        $systemCategory = Category::whereNull('user_id')->first();

        $response = $this->actingAs($user)->put(route('categories.update', $systemCategory), [
            'name' => 'محاولة تعديل',
            'type' => $systemCategory->type,
        ]);

        $response->assertForbidden();
    }

    public function test_user_cannot_update_other_users_category(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $category = Category::factory()->for($user1)->create([
            'type' => 'expense',
        ]);

        $response = $this->actingAs($user2)->put(route('categories.update', $category), [
            'name' => 'محاولة اختراق',
            'type' => 'expense',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create([
            'type' => 'expense',
        ]);

        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));

        $this->assertSoftDeleted($category);
    }

    public function test_user_cannot_delete_system_category(): void
    {
        $user = User::factory()->create();
        $systemCategory = Category::whereNull('user_id')->first();

        $response = $this->actingAs($user)->delete(route('categories.destroy', $systemCategory));

        $response->assertForbidden();
    }

    public function test_category_validation_requires_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'type' => 'expense',
        ]);

        $response->assertSessionHasErrors('name');
    }
}
