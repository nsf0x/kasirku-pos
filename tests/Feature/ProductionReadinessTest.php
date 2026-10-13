<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_demo_login_url_never_authenticates_a_user(): void
    {
        $response = $this->get('/demo-login/cashier');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_cashiers_cannot_open_owner_pages(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)
            ->get('/dashboard')
            ->assertForbidden();

        $this->actingAs($cashier)
            ->get('/pengguna')
            ->assertForbidden();
    }

    public function test_owner_can_open_dashboard(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_database_seeder_does_not_remove_existing_business_data(): void
    {
        User::factory()->create(['role' => 'owner']);
        $category = Category::query()->create(['name' => 'Inventaris usaha']);

        app(DatabaseSeeder::class)->run();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Inventaris usaha',
        ]);
        $this->assertDatabaseCount('users', 1);
    }
}
