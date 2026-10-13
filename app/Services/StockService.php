<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class StockService
{
    /**
     * Add stock (type: in)
     */
    public function addStock(Product $product, int $qty, int $userId, string $note = ''): void
    {
        $this->ensurePositiveQuantity($qty);

        DB::transaction(function () use ($product, $qty, $userId, $note) {
            $p = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            $previousStock = $p->stock;
            $p->increment('stock', $qty);

            StockMovement::create([
                'product_id' => $p->id,
                'user_id'    => $userId,
                'sale_id'    => null,
                'type'       => 'in',
                'qty'        => $qty,
                'note'       => $note ?: "Stok masuk +{$qty}",
                'created_at' => now(),
            ]);

            ActivityLog::record(
                action: 'stock.in',
                userId: $userId,
                subjectType: Product::class,
                subjectId: $p->id,
                meta: [
                    'qty' => $qty,
                    'note' => $note,
                    'stock_before' => $previousStock,
                    'stock_after' => $previousStock + $qty,
                ]
            );
        });
    }

    /**
     * Adjust stock (type: adjust) — set to exact value
     */
    public function adjustStock(Product $product, int $newStock, int $userId, string $note = ''): void
    {
        if ($newStock < 0) {
            throw new InvalidArgumentException('Jumlah stok tidak boleh negatif.');
        }

        DB::transaction(function () use ($product, $newStock, $userId, $note) {
            $p = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            $previousStock = $p->stock;
            $diff = $newStock - $previousStock;

            if ($diff === 0) {
                return;
            }

            $p->update(['stock' => $newStock]);

            StockMovement::create([
                'product_id' => $p->id,
                'user_id'    => $userId,
                'sale_id'    => null,
                'type'       => 'adjust',
                'qty'        => $diff,
                'note'       => $note ?: "Penyesuaian stok → {$newStock}",
                'created_at' => now(),
            ]);

            ActivityLog::record(
                action: 'stock.adjust',
                userId: $userId,
                subjectType: Product::class,
                subjectId: $p->id,
                meta: ['old_stock' => $previousStock, 'new_stock' => $newStock, 'note' => $note]
            );
        });
    }

    /**
     * Manual out (type: out) — e.g., damaged/expired
     */
    public function removeStock(Product $product, int $qty, int $userId, string $note = ''): void
    {
        $this->ensurePositiveQuantity($qty);

        DB::transaction(function () use ($product, $qty, $userId, $note) {
            $p = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            $previousStock = $p->stock;

            if ($p->stock < $qty) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok \"{$p->name}\" tidak cukup. Sisa: {$p->stock}.",
                ]);
            }

            $p->decrement('stock', $qty);

            StockMovement::create([
                'product_id' => $p->id,
                'user_id'    => $userId,
                'sale_id'    => null,
                'type'       => 'out',
                'qty'        => -$qty,
                'note'       => $note ?: "Stok keluar -{$qty}",
                'created_at' => now(),
            ]);

            ActivityLog::record(
                action: 'stock.out',
                userId: $userId,
                subjectType: Product::class,
                subjectId: $p->id,
                meta: [
                    'qty' => $qty,
                    'note' => $note,
                    'stock_before' => $previousStock,
                    'stock_after' => $previousStock - $qty,
                ]
            );
        });
    }

    private function ensurePositiveQuantity(int $qty): void
    {
        if ($qty < 1) {
            throw new InvalidArgumentException('Jumlah stok harus lebih dari nol.');
        }
    }
}
