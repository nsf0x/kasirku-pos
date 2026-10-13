<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SaleService $saleService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleService = new SaleService();
    }

    public function test_it_creates_a_sale_and_reduces_stock(): void
    {
        $user = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $category = Category::create(['name' => 'Minuman']);

        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'MIN-100',
            'name' => 'Es Lemon',
            'cost_price' => 4000,
            'price' => 9000,
            'stock' => 10,
            'min_stock' => 3,
            'is_active' => true,
        ]);

        $sale = $this->saleService->create(
            userId: $user->id,
            items: [['product_id' => $product->id, 'qty' => 2]],
            discount: 0,
            paymentMethod: 'cash',
            paidAmount: 18000,
        );

        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'user_id' => $user->id,
            'status' => 'completed',
            'total' => 18000,
        ]);

        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => 2,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'type' => 'sale',
            'qty' => -2,
        ]);

        $product->refresh();
        $this->assertSame(8, $product->stock);
    }

    public function test_it_rejects_insufficient_stock(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Makanan']);

        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'MKN-001',
            'name' => 'Nasi Kucing',
            'cost_price' => 2500,
            'price' => 7000,
            'stock' => 2,
            'min_stock' => 1,
            'is_active' => true,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stok');

        $this->saleService->create(
            userId: $user->id,
            items: [['product_id' => $product->id, 'qty' => 3]],
            discount: 0,
            paymentMethod: 'cash',
            paidAmount: 21000,
        );
    }

    public function test_it_rejects_discount_above_subtotal(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Elektronik']);

        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'ELK-001',
            'name' => 'Power Bank',
            'cost_price' => 100000,
            'price' => 150000,
            'stock' => 5,
            'min_stock' => 2,
            'is_active' => true,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Diskon tidak boleh melebihi subtotal');

        $this->saleService->create(
            userId: $user->id,
            items: [['product_id' => $product->id, 'qty' => 1]],
            discount: 200000,
            paymentMethod: 'cash',
            paidAmount: 200000,
        );
    }

    public function test_it_rejects_cash_payment_below_total(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Perawatan']);

        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'PRT-001',
            'name' => 'Sabun Cuci',
            'cost_price' => 5000,
            'price' => 12000,
            'stock' => 5,
            'min_stock' => 2,
            'is_active' => true,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Jumlah bayar kurang dari total transaksi');

        $this->saleService->create(
            userId: $user->id,
            items: [['product_id' => $product->id, 'qty' => 1]],
            discount: 0,
            paymentMethod: 'cash',
            paidAmount: 10000,
        );
    }

    public function test_it_restores_stock_when_sale_is_voided(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        $category = Category::create(['name' => 'Cemilan']);

        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'CML-001',
            'name' => 'Keripik Kentang',
            'cost_price' => 6000,
            'price' => 15000,
            'stock' => 10,
            'min_stock' => 2,
            'is_active' => true,
        ]);

        $sale = $this->saleService->create(
            userId: $user->id,
            items: [['product_id' => $product->id, 'qty' => 3]],
            discount: 0,
            paymentMethod: 'cash',
            paidAmount: 45000,
        );

        $product->refresh();
        $this->assertSame(7, $product->stock);

        $voided = $this->saleService->void($sale, $user->id, 'Kesalahan input');

        $this->assertSame('void', $voided->status);

        $product->refresh();
        $this->assertSame(10, $product->stock);

        $this->assertDatabaseHas('stock_movements', [
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'type' => 'void',
            'qty' => 3,
        ]);
    }

    public function test_duplicate_product_lines_are_aggregated_before_stock_validation(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Kebutuhan']);
        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'KBT-001',
            'name' => 'Sabun',
            'cost_price' => 1000,
            'price' => 2000,
            'stock' => 3,
            'min_stock' => 1,
            'is_active' => true,
        ]);

        try {
            $this->saleService->create(
                userId: $user->id,
                items: [
                    ['product_id' => $product->id, 'qty' => 2],
                    ['product_id' => $product->id, 'qty' => 2],
                ],
                discount: 0,
                paymentMethod: 'cash',
                paidAmount: 8000,
            );
            $this->fail('Duplicate product quantities must be combined before checking stock.');
        } catch (\RuntimeException $exception) {
            $this->assertSame(3, $product->fresh()->stock);
            $this->assertDatabaseCount('sales', 0);
        }
    }

    public function test_non_cash_payment_must_cover_the_exact_sale_total(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Kebutuhan rumah']);
        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'KRT-001',
            'name' => 'Sapu',
            'cost_price' => 1000,
            'price' => 5000,
            'stock' => 5,
            'min_stock' => 1,
            'is_active' => true,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Pembayaran non-tunai');

        $this->saleService->create(
            userId: $user->id,
            items: [['product_id' => $product->id, 'qty' => 1]],
            discount: 0,
            paymentMethod: 'qris',
            paidAmount: 4000,
        );
    }

    public function test_invalid_sale_payloads_are_rejected_without_creating_transactions(): void
    {
        $user = User::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('minimal satu produk');

        $this->saleService->create(
            userId: $user->id,
            items: [],
            discount: 0,
            paymentMethod: 'cash',
            paidAmount: 0,
        );
    }

    public function test_gross_profit_includes_the_sale_discount(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $category = Category::create(['name' => 'Kebutuhan']);
        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'KBT-002',
            'name' => 'Lap',
            'cost_price' => 1000,
            'price' => 2000,
            'stock' => 5,
            'min_stock' => 1,
            'is_active' => true,
        ]);

        $sale = $this->saleService->create(
            userId: $owner->id,
            items: [['product_id' => $product->id, 'qty' => 2]],
            discount: 500,
            paymentMethod: 'cash',
            paidAmount: 3500,
        );

        $this->assertSame(1500.0, $sale->gross_profit);
    }

    public function test_repeated_checkout_request_returns_the_existing_sale(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $category = Category::create(['name' => 'Dapur']);
        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'DPR-001',
            'name' => 'Sendok',
            'cost_price' => 1000,
            'price' => 2000,
            'stock' => 5,
            'min_stock' => 1,
            'is_active' => true,
        ]);
        $key = (string) \Illuminate\Support\Str::uuid();
        $payload = [
            'userId' => $owner->id,
            'items' => [['product_id' => $product->id, 'qty' => 2]],
            'discount' => 0,
            'paymentMethod' => 'cash',
            'paidAmount' => 4000,
            'idempotencyKey' => $key,
        ];

        $firstSale = $this->saleService->create(...$payload);
        $retriedSale = $this->saleService->create(...$payload);

        $this->assertSame($firstSale->id, $retriedSale->id);
        $this->assertDatabaseCount('sales', 1);
        $this->assertSame(3, $product->fresh()->stock);
    }
}
