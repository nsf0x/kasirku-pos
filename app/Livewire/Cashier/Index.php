<?php

namespace App\Livewire\Cashier;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

class Index extends Component
{
    // Search & filter
    public string $search = '';
    public ?int $categoryId = null;

    // Cart items: [['product_id', 'name', 'sku', 'price', 'stock', 'category_color', 'qty'], ...]
    public array $cart = [];

    // Discount
    public float $discountAmount = 0;
    public string $discountType = 'nominal'; // nominal | percent
    public string $discountInput = '';

    // Payment dialog
    public bool $showPaymentDialog = false;
    public string $paymentMethod = 'cash';
    public string $paidInput = '';
    #[Locked]
    public ?string $checkoutKey = null;

    // Success state
    public bool $showSuccess = false;
    public ?Sale $lastSale = null;

    // Mobile cart panel
    public bool $showMobileCart = false;

    #[Computed]
    public function products()
    {
        return Product::with('category')
            ->active()
            ->when($this->categoryId, fn($q) => $q->where('category_id', $this->categoryId))
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            }))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product || !$product->is_active) {
            $this->dispatch('toast', message: 'Produk tidak tersedia.', type: 'error');
            return;
        }

        if ($product->stock === 0) {
            $this->dispatch('toast', message: "Stok {$product->name} habis.", type: 'error');
            return;
        }

        foreach ($this->cart as &$item) {
            if ($item['product_id'] === $productId) {
                if ($item['qty'] >= $product->stock) {
                    $this->dispatch('toast', message: "Stok {$product->name} tinggal {$product->stock}.", type: 'error');
                    return;
                }
                $item['qty']++;
                return;
            }
        }

        $this->cart[] = [
            'product_id'     => $product->id,
            'name'           => $product->name,
            'sku'            => $product->sku,
            'price'          => (float) $product->price,
            'stock'          => $product->stock,
            'min_stock'      => $product->min_stock,
            'category_color' => $this->getCategoryColor($product->category?->name ?? ''),
            'qty'            => 1,
        ];
    }

    public function removeFromCart(int $productId): void
    {
        $this->cart = array_values(
            array_filter($this->cart, fn($i) => $i['product_id'] !== $productId)
        );
    }

    public function incrementQty(int $productId): void
    {
        foreach ($this->cart as &$item) {
            if ($item['product_id'] === $productId) {
                $product = Product::find($productId);
                if ($product && $item['qty'] >= $product->stock) {
                    $this->dispatch('toast', message: "Stok {$product->name} tinggal {$product->stock}.", type: 'error');
                    return;
                }
                $item['qty']++;
                return;
            }
        }
    }

    public function decrementQty(int $productId): void
    {
        foreach ($this->cart as &$item) {
            if ($item['product_id'] === $productId) {
                if ($item['qty'] <= 1) {
                    $this->removeFromCart($productId);
                    return;
                }
                $item['qty']--;
                return;
            }
        }
    }

    public function updateDiscount(): void
    {
        $val = (float) str_replace(['.', ','], ['', '.'], $this->discountInput);
        if ($this->discountType === 'percent') {
            $val = min(100, max(0, $val));
            $this->discountAmount = round($this->getSubtotalProperty() * $val / 100);
        } else {
            $this->discountAmount = max(0, min($val, $this->getSubtotalProperty()));
        }
    }

    public function clearCart(): void
    {
        $this->cart          = [];
        $this->discountAmount = 0;
        $this->discountInput = '';
        $this->paidInput     = '';
    }

    #[On('open-payment-dialog')]
    public function openPaymentDialog(): void
    {
        if (empty($this->cart)) return;
        $this->showPaymentDialog = true;
        $this->paidInput         = '';
        $this->checkoutKey ??= (string) Str::uuid();
    }

    #[On('close-dialogs')]
    public function closeDialogs(): void
    {
        $this->showPaymentDialog = false;
        $this->showMobileCart    = false;
    }

    public function setPaidAmount(string $amount): void
    {
        $this->paidInput = $amount;
    }

    public function appendDigit(string $digit): void
    {
        if ($digit === '⌫') {
            $this->paidInput = substr($this->paidInput, 0, -1);
            return;
        }
        // Prevent leading zeros
        if ($this->paidInput === '0' && $digit !== '000') {
            $this->paidInput = $digit;
            return;
        }
        $this->paidInput .= $digit;
    }

    public function setExact(): void
    {
        $this->paidInput = (string) (int) $this->getTotalProperty();
    }

    public function processPayment(SaleService $saleService): void
    {
        if (! $this->showPaymentDialog || $this->checkoutKey === null) {
            $this->dispatch('toast', message: 'Buka dialog pembayaran sebelum menyelesaikan transaksi.', type: 'error');
            return;
        }

        if (empty($this->cart)) {
            $this->dispatch('toast', message: 'Tambahkan produk sebelum memproses pembayaran.', type: 'error');
            return;
        }

        if (! in_array($this->paymentMethod, ['cash', 'qris', 'transfer'], true)) {
            $this->dispatch('toast', message: 'Metode pembayaran tidak valid.', type: 'error');
            return;
        }

        $paid = $this->paymentMethod === 'cash'
            ? (float) str_replace(['.', ','], ['', '.'], $this->paidInput)
            : $this->getTotalProperty();

        if ($this->paymentMethod === 'cash' && $paid < $this->getTotalProperty()) {
            $this->dispatch('toast', message: 'Jumlah bayar kurang dari total.', type: 'error');
            return;
        }

        try {
            $items = array_map(
                fn($i) => ['product_id' => $i['product_id'], 'qty' => $i['qty']],
                $this->cart
            );

            $sale = $saleService->create(
                userId: auth()->id(),
                items: $items,
                discount: $this->discountAmount,
                paymentMethod: $this->paymentMethod,
                paidAmount: $paid,
                idempotencyKey: $this->checkoutKey,
            );

            $this->lastSale          = $sale->load(['items', 'cashier']);
            $this->showPaymentDialog = false;
            $this->showSuccess       = true;
            $this->clearCart();
            $this->checkoutKey = null;
        } catch (RuntimeException|InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function newTransaction(): void
    {
        $this->showSuccess = false;
        $this->lastSale    = null;
    }

    public function printReceipt(): void
    {
        if (!$this->lastSale) return;
        $this->dispatch(
            'print-receipt',
            html: view('receipts.thermal', [
                'sale' => $this->lastSale->load(['items', 'cashier']),
            ])->render()
        );
    }

    // ── Computed values ────────────────────────────────────────────────────────

    public function getSubtotalProperty(): float
    {
        return collect($this->cart)->sum(fn($i) => $i['price'] * $i['qty']);
    }

    public function getTotalProperty(): float
    {
        return max(0, $this->getSubtotalProperty() - $this->discountAmount);
    }

    public function getChangeProperty(): float
    {
        $paid = (float) str_replace(['.', ','], ['', '.'], $this->paidInput);
        return max(0, $paid - $this->getTotalProperty());
    }

    public function getCartCountProperty(): int
    {
        return collect($this->cart)->sum('qty');
    }

    private function getCategoryColor(string $categoryName): string
    {
        $colors = ['#0E7C86', '#2E9E5B', '#D98A1F', '#7C6BCE', '#D64545', '#1B7FC4', '#AA6B2E', '#5B6672'];
        $idx    = abs(crc32($categoryName)) % count($colors);
        return $colors[$idx];
    }

    public function render()
    {
        return view('livewire.cashier.index', [
            'subtotal'  => $this->getSubtotalProperty(),
            'total'     => $this->getTotalProperty(),
            'change'    => $this->getChangeProperty(),
            'cartCount' => $this->getCartCountProperty(),
        ])->layout('layouts.cashier');
    }
}
