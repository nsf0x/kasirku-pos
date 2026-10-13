<?php

namespace Tests\Feature;

use App\Livewire\Categories\Index as CategoriesIndex;
use App\Livewire\Products\Form as ProductForm;
use App\Livewire\Sales\Show as SaleShow;
use App\Livewire\Stock\Index as StockIndex;
use App\Livewire\Users\Index as UsersIndex;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class OperationalManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_and_rename_categories(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        Livewire::actingAs($owner)
            ->test(CategoriesIndex::class)
            ->call('openCreateDialog')
            ->set('name', 'Minuman')
            ->call('save')
            ->assertHasNoErrors();

        $category = Category::query()->where('name', 'Minuman')->firstOrFail();

        Livewire::actingAs($owner)
            ->test(CategoriesIndex::class)
            ->call('edit', $category->id)
            ->set('name', 'Minuman Dingin')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Minuman Dingin']);
    }

    public function test_owner_can_create_cashier_account_and_change_password(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        Livewire::actingAs($owner)
            ->test(UsersIndex::class)
            ->call('openCreateDialog')
            ->set('newName', 'Sari')
            ->set('newEmail', 'SARI@example.test')
            ->set('newPassword', 'a-strong-password')
            ->set('newPassword_confirmation', 'a-strong-password')
            ->call('createCashier')
            ->assertHasNoErrors();

        $cashier = User::query()->where('email', 'sari@example.test')->firstOrFail();
        $this->assertSame('cashier', $cashier->role);
        $this->assertTrue(Hash::check('a-strong-password', $cashier->password));

        Livewire::actingAs($owner)
            ->test(UsersIndex::class)
            ->call('openPasswordDialog', $cashier->id)
            ->set('newPassword', 'another-strong-password')
            ->set('newPassword_confirmation', 'another-strong-password')
            ->call('resetCashierPassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('another-strong-password', $cashier->fresh()->password));
    }

    public function test_disabling_cashier_terminates_their_next_web_request(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $cashier->update(['is_active' => false]);

        $this->actingAs($cashier)
            ->get('/kasir')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_owner_can_record_stock_in_and_opname_from_stock_page(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $category = Category::query()->create(['name' => 'Barang']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'sku' => 'BRG-001',
            'name' => 'Barang A',
            'cost_price' => 100,
            'price' => 200,
            'stock' => 4,
            'min_stock' => 1,
            'is_active' => true,
        ]);

        Livewire::actingAs($owner)
            ->test(StockIndex::class)
            ->call('openStockDialog', 'in')
            ->set('productId', (string) $product->id)
            ->set('quantity', '3')
            ->set('note', 'Restock')
            ->call('recordStockMovement')
            ->assertHasNoErrors();

        Livewire::actingAs($owner)
            ->test(StockIndex::class)
            ->call('openStockDialog', 'adjust')
            ->set('productId', (string) $product->id)
            ->set('quantity', '5')
            ->set('note', 'Hasil opname')
            ->call('recordStockMovement')
            ->assertHasNoErrors();

        $this->assertSame(5, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'adjust',
            'qty' => -2,
        ]);
    }

    public function test_cashiers_cannot_record_stock_movements(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        Livewire::actingAs($cashier)
            ->test(StockIndex::class)
            ->call('openStockDialog', 'in')
            ->assertForbidden();

        $this->assertSame(0, StockMovement::query()->count());
    }

    public function test_creating_product_records_its_opening_stock_as_a_stock_movement(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $category = Category::query()->create(['name' => 'Minuman']);

        Livewire::actingAs($owner)
            ->test(ProductForm::class)
            ->set('sku', 'MIN-100')
            ->set('name', 'Air mineral')
            ->set('category_id', $category->id)
            ->set('cost_price', '1000')
            ->set('price', '2000')
            ->set('stock', 8)
            ->set('min_stock', 2)
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::query()->where('sku', 'MIN-100')->firstOrFail();
        $this->assertSame(8, $product->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'qty' => 8,
        ]);
    }

    public function test_owner_can_void_sale_from_the_transaction_detail_page(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $cashier = User::factory()->create(['role' => 'cashier']);
        $category = Category::query()->create(['name' => 'Minuman']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'sku' => 'MIN-200',
            'name' => 'Teh',
            'cost_price' => 1000,
            'price' => 2000,
            'stock' => 5,
            'min_stock' => 1,
            'is_active' => true,
        ]);
        $sale = app(SaleService::class)->create($cashier->id, [
            ['product_id' => $product->id, 'qty' => 2],
        ], 0, 'cash', 4000);

        Livewire::actingAs($owner)
            ->test(SaleShow::class, ['sale' => $sale])
            ->call('openVoidDialog')
            ->set('voidReason', 'Salah input')
            ->call('void')
            ->assertHasNoErrors();

        $this->assertSame('void', $sale->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_owner_cannot_delete_the_last_owner_account(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)
            ->delete('/profile', ['password' => 'password'])
            ->assertSessionHasErrorsIn('userDeletion', 'password');

        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }
}
