<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\Support\Str;

class SaleService
{
    /**
     * Create a new sale inside a DB transaction.
     *
     * @param  int    $userId      Cashier's user ID
     * @param  array  $items       [['product_id' => X, 'qty' => Y], ...]
     * @param  float  $discount    Discount amount (nominal)
     * @param  string $paymentMethod  cash|qris|transfer
     * @param  float  $paidAmount  Amount tendered by customer
     * @return Sale
     *
     * @throws \RuntimeException  When stock is insufficient
     * @throws \InvalidArgumentException  When business rules are violated
     */
    public function create(
        int $userId,
        array $items,
        float $discount,
        string $paymentMethod,
        float $paidAmount,
        ?string $idempotencyKey = null
    ): Sale {
        if ($idempotencyKey !== null && ! Str::isUuid($idempotencyKey)) {
            throw new InvalidArgumentException('Kunci permintaan transaksi tidak valid.');
        }

        if (! in_array($paymentMethod, ['cash', 'qris', 'transfer'], true)) {
            throw new InvalidArgumentException('Metode pembayaran tidak valid.');
        }

        if (! is_finite($discount) || $discount < 0 || ! is_finite($paidAmount) || $paidAmount < 0) {
            throw new InvalidArgumentException('Nominal transaksi tidak valid.');
        }

        $quantities = [];
        foreach ($items as $item) {
            if (! isset($item['product_id'], $item['qty'])
                || filter_var($item['product_id'], FILTER_VALIDATE_INT) === false
                || filter_var($item['qty'], FILTER_VALIDATE_INT) === false
                || (int) $item['product_id'] < 1
                || (int) $item['qty'] < 1) {
                throw new InvalidArgumentException('Produk dan jumlah item harus valid.');
            }

            $productId = (int) $item['product_id'];
            $quantities[$productId] = ($quantities[$productId] ?? 0) + (int) $item['qty'];
        }

        if ($quantities === []) {
            throw new InvalidArgumentException('Transaksi harus memiliki minimal satu produk.');
        }

        return DB::transaction(function () use ($userId, $items, $discount, $paymentMethod, $paidAmount, $idempotencyKey) {
            $cashier = User::query()->where('is_active', true)->find($userId);
            if (! $cashier) {
                throw new InvalidArgumentException('Akun pengguna tidak aktif atau tidak ditemukan.');
            }

            if ($idempotencyKey !== null) {
                DB::table('sale_requests')->insertOrIgnore([
                    'request_key' => $idempotencyKey,
                    'created_at' => now(),
                ]);

                $request = DB::table('sale_requests')
                    ->where('request_key', $idempotencyKey)
                    ->lockForUpdate()
                    ->first();

                if ($request?->sale_id !== null) {
                    $existingSale = Sale::query()->findOrFail($request->sale_id);
                    if ($existingSale->user_id !== $userId) {
                        throw new InvalidArgumentException('Kunci transaksi sudah digunakan oleh akun lain.');
                    }

                    return $existingSale->load('items');
                }
            }

            $quantities = [];
            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $quantities[$productId] = ($quantities[$productId] ?? 0) + (int) $item['qty'];
            }

            // Lock unique product rows before checking stock.
            $productIds = array_keys($quantities);
            $products   = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 2. Validate stock availability
            $lineItems = [];
            $subtotal  = 0;

            foreach ($quantities as $productId => $qty) {
                $product = $products->get($productId);

                if (! $product) {
                    throw new InvalidArgumentException("Produk ID {$productId} tidak ditemukan.");
                }

                if (! $product->is_active) {
                    throw new \InvalidArgumentException("Produk \"{$product->name}\" tidak aktif.");
                }

                if ($product->stock < $qty) {
                    throw new \RuntimeException(
                        "Stok \"{$product->name}\" tidak cukup. Sisa stok: {$product->stock}."
                    );
                }

                $lineSubtotal = $product->price * $qty;
                $subtotal    += $lineSubtotal;

                $lineItems[] = [
                    'product'      => $product,
                    'qty'          => $qty,
                    'line_subtotal'=> $lineSubtotal,
                ];
            }

            // 3. Validate discount does not exceed subtotal
            if ($discount > $subtotal) {
                throw new InvalidArgumentException('Diskon tidak boleh melebihi subtotal.');
            }

            $total = $subtotal - $discount;

            // 4. Validate paid amount (for cash)
            if ($paymentMethod !== 'cash' && $paidAmount !== $total) {
                throw new InvalidArgumentException('Pembayaran non-tunai harus dicatat sebesar total transaksi.');
            }

            if ($paymentMethod === 'cash' && $paidAmount < $total) {
                throw new InvalidArgumentException('Jumlah bayar kurang dari total transaksi.');
            }

            $changeAmount = $paymentMethod === 'cash' ? max(0, $paidAmount - $total) : 0;

            // 5. Generate unique daily invoice number (safe against concurrency)
            $invoiceNo = $this->generateInvoiceNo();

            // 6. Persist Sale
            $sale = Sale::create([
                'invoice_no'     => $invoiceNo,
                'user_id'        => $userId,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => $total,
                'paid_amount'    => $paidAmount,
                'change_amount'  => $changeAmount,
                'payment_method' => $paymentMethod,
                'status'         => 'completed',
            ]);

            if ($idempotencyKey !== null) {
                DB::table('sale_requests')
                    ->where('request_key', $idempotencyKey)
                    ->update(['sale_id' => $sale->id]);
            }

            // 7. Persist items, update stock, create movements
            foreach ($lineItems as $line) {
                /** @var Product $product */
                $product = $line['product'];

                SaleItem::create([
                    'sale_id'      => $sale->id,
                    'product_id'   => $product->id,
                    'product_name' => $product->name,   // snapshot
                    'qty'          => $line['qty'],
                    'price'        => $product->price,  // snapshot
                    'cost_price'   => $product->cost_price, // snapshot
                    'subtotal'     => $line['line_subtotal'],
                ]);

                $product->decrement('stock', $line['qty']);

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id'    => $userId,
                    'sale_id'    => $sale->id,
                    'type'       => 'sale',
                    'qty'        => -$line['qty'],
                    'note'       => "Penjualan {$invoiceNo}",
                    'created_at' => now(),
                ]);
            }

            return $sale->load('items');
        }, attempts: 3);
    }

    /**
     * Void an existing sale.
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function void(Sale $sale, int $voidedBy, string $reason): Sale
    {
        $reason = trim($reason);
        if ($reason === '' || mb_strlen($reason) > 255) {
            throw new InvalidArgumentException('Alasan void wajib diisi dan maksimal 255 karakter.');
        }

        return DB::transaction(function () use ($sale, $voidedBy, $reason) {
            $owner = User::query()
                ->where('role', 'owner')
                ->where('is_active', true)
                ->find($voidedBy);

            if (! $owner) {
                throw new InvalidArgumentException('Hanya owner aktif yang dapat membatalkan transaksi.');
            }

            $lockedSale = Sale::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($sale->id);

            if ($lockedSale->status === 'void') {
                throw new \RuntimeException('Transaksi ini sudah di-void.');
            }

            // Restore stock for each item
            foreach ($lockedSale->items as $item) {
                $product = Product::withTrashed()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($product) {
                    $product->increment('stock', $item->qty);

                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id'    => $voidedBy,
                        'sale_id'    => $lockedSale->id,
                        'type'       => 'void',
                        'qty'        => $item->qty,
                        'note'       => "Void {$sale->invoice_no}: {$reason}",
                        'created_at' => now(),
                    ]);
                }
            }

            $lockedSale->update([
                'status'     => 'void',
                'void_reason'=> $reason,
                'voided_by'  => $voidedBy,
                'voided_at'  => now(),
            ]);

            ActivityLog::record(
                action: 'sale.void',
                userId: $voidedBy,
                subjectType: Sale::class,
                subjectId: $lockedSale->id,
                meta: ['invoice_no' => $lockedSale->invoice_no, 'reason' => $reason]
            );

            return $lockedSale->fresh(['items', 'cashier', 'voidedByUser']);
        }, attempts: 3);
    }

    /**
     * Generate a daily sequential invoice number: INV-YYYYMMDD-0001
     * Uses a locked per-day sequence row so concurrent checkouts cannot reuse an invoice number.
     */
    private function generateInvoiceNo(): string
    {
        $date   = now()->format('Ymd');
        $prefix = "INV-{$date}-";

        DB::table('sale_sequences')->insertOrIgnore([
            'sequence_date' => $date,
            'sequence' => 0,
        ]);

        $sequence = DB::table('sale_sequences')
            ->where('sequence_date', $date)
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            throw new \RuntimeException('Nomor invoice tidak dapat dikunci.');
        }

        $last = Sale::where('invoice_no', 'like', "{$prefix}%")
            ->orderByDesc('invoice_no')
            ->value('invoice_no');
        $lastSequence = $last ? (int) substr($last, strlen($prefix)) : 0;
        $nextSequence = max((int) $sequence->sequence, $lastSequence) + 1;

        DB::table('sale_sequences')
            ->where('sequence_date', $date)
            ->update(['sequence' => $nextSequence]);

        return $prefix . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
