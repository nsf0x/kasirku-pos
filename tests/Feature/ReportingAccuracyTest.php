<?php

namespace Tests\Feature;

use App\Exports\SalesReportExport;
use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportingAccuracyTest extends TestCase
{
    use RefreshDatabase;

    private function product(Category $category, string $sku, string $name, int $stock = 10, bool $active = true): Product
    {
        return Product::query()->create([
            'category_id' => $category->id,
            'sku' => $sku,
            'name' => $name,
            'cost_price' => 1000,
            'price' => 2000,
            'stock' => $stock,
            'min_stock' => 3,
            'is_active' => $active,
        ]);
    }

    public function test_dashboard_uses_only_completed_sales_and_real_low_stock_comparison(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $cashier = User::factory()->create(['role' => 'cashier']);
        $category = Category::query()->create(['name' => 'Minuman']);
        $soldProduct = $this->product($category, 'MIN-300', 'Teh aktif', 4);
        $voidProduct = $this->product($category, 'MIN-301', 'Teh dibatalkan');
        $inactiveLowStock = $this->product($category, 'MIN-302', 'Teh nonaktif', 1, false);
        $service = app(SaleService::class);

        $service->create($cashier->id, [
            ['product_id' => $soldProduct->id, 'qty' => 1],
        ], 0, 'cash', 2000);
        $voidSale = $service->create($cashier->id, [
            ['product_id' => $voidProduct->id, 'qty' => 1],
        ], 0, 'cash', 2000);
        $service->void($voidSale, $owner->id, 'Salah input');

        Livewire::actingAs($owner)
            ->test(DashboardIndex::class)
            ->assertSee('Teh aktif')
            ->assertDontSee('Teh dibatalkan')
            ->assertDontSee('Teh nonaktif');

        $this->assertSame(3, $soldProduct->fresh()->stock);
        $this->assertSame(10, $voidProduct->fresh()->stock);
        $this->assertSame(1, $inactiveLowStock->fresh()->stock);
    }

    public function test_report_rejects_invalid_date_ranges_without_failing_the_page(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        Livewire::actingAs($owner)
            ->test(ReportsIndex::class)
            ->set('fromDate', '2026-99-40')
            ->assertHasErrors(['fromDate']);
    }

    public function test_excel_report_includes_discount_adjusted_gross_profit(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $category = Category::query()->create(['name' => 'Rumah tangga']);
        $product = $this->product($category, 'RMT-100', 'Kain lap');
        $sale = app(SaleService::class)->create($owner->id, [
            ['product_id' => $product->id, 'qty' => 2],
        ], 500, 'cash', 3500);

        $export = new SalesReportExport(now()->format('Y-m-d'), now()->format('Y-m-d'));
        $row = $export->map($sale->load(['cashier', 'items']));

        $this->assertSame(1500.0, $row[7]);
        $this->assertSame(1, $export->query()->count());
    }
}
