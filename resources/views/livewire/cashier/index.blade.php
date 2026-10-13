<div class="min-h-screen bg-bg dark:bg-bg-dark">
    <div class="mx-auto max-w-7xl px-4 py-5 md:px-6 lg:px-8">
        <header class="brand-header mb-6 flex flex-col gap-4 p-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="brand-mark">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                    </svg>
                </div>
                <div>
                    <span class="section-tag">KasirKu</span>
                    <h1 class="mt-2 text-2xl font-bold text-content-primary dark:text-content-primary-dark">Point of Sale</h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="rounded-2xl border border-border bg-white/70 px-3 py-2 text-sm shadow-sm dark:border-border-dark dark:bg-slate-900/60">
                    <span class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-content-secondary">Kasir</span>
                    <p class="font-semibold text-content-primary dark:text-content-primary-dark">{{ auth()->user()->name }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">Keluar</button>
                </form>
            </div>
        </header>

        <div class="grid gap-6 xl:grid-cols-[1.45fr_0.75fr]">
            <section class="card p-4 md:p-5">
                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-medium text-content-secondary">Cari produk</p>
                        <h2 class="mt-1 text-xl font-semibold text-content-primary dark:text-content-primary-dark">Produk aktif</h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="$set('categoryId', null)">Semua</button>
                        @foreach($categories as $category)
                            <button type="button" class="btn btn-sm {{ $categoryId === $category->id ? 'btn-primary' : 'btn-secondary' }}" wire:click="$set('categoryId', {{ $category->id }})">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="mb-5">
                    <input
                        data-search-input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama produk atau SKU…"
                        class="input"
                    >
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @forelse($this->products as $product)
                        <button type="button" wire:click="addToCart({{ $product->id }})" class="product-card">
                            <div>
                                <div class="mb-3 flex items-start justify-between gap-2">
                                    <span class="rounded-full bg-primary/10 px-2 py-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-primary">{{ $product->category?->name ?? '-' }}</span>
                                    @if($product->stock <= $product->min_stock)
                                        <span class="rounded-full bg-warning-50 px-2 py-1 text-[11px] font-semibold text-warning-500">Stok rendah</span>
                                    @endif
                                </div>
                                <h3 class="text-lg font-semibold text-content-primary dark:text-content-primary-dark">{{ $product->name }}</h3>
                                <p class="mt-1 text-sm text-content-secondary">SKU: {{ $product->sku }}</p>
                            </div>

                            <div class="mt-4 flex items-end justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-content-secondary">Harga</p>
                                    <p class="text-xl font-bold text-content-primary dark:text-content-primary-dark">{{ rupiah($product->price) }}</p>
                                </div>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-sm font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                    {{ $product->stock }} pcs
                                </span>
                            </div>
                        </button>
                    @empty
                        <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-border bg-bg p-10 text-center text-content-secondary dark:border-border-dark dark:bg-bg-dark">
                            Produk tidak ditemukan untuk filter saat ini.
                        </div>
                    @endforelse
                </div>
            </section>

            <aside class="card flex flex-col p-4 md:p-5">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-content-secondary">Keranjang</p>
                        <h2 class="text-xl font-semibold text-content-primary dark:text-content-primary-dark">{{ $cartCount }} item</h2>
                    </div>
                    @if(!empty($cart))
                        <button type="button" class="btn btn-ghost btn-sm" wire:click="clearCart()">Bersihkan</button>
                    @endif
                </div>

                <div class="space-y-3">
                    @forelse($cart as $index => $item)
                        <div class="order-item">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-content-primary dark:text-content-primary-dark">{{ $item['name'] }}</p>
                                    <p class="text-xs text-content-secondary">{{ rupiah($item['price']) }} / pcs</p>
                                </div>
                                <button type="button" wire:click="removeFromCart({{ $item['product_id'] }})" class="text-sm text-danger-500 hover:text-danger-600">Hapus</button>
                            </div>

                            <div class="mt-3 flex items-center justify-between">
                                <div class="inline-flex items-center rounded-full border border-border dark:border-border-dark">
                                    <button type="button" wire:click="decrementQty({{ $item['product_id'] }})" class="h-9 w-9 text-lg text-content-secondary">−</button>
                                    <span class="min-w-10 text-center text-sm font-semibold">{{ $item['qty'] }}</span>
                                    <button type="button" wire:click="incrementQty({{ $item['product_id'] }})" class="h-9 w-9 text-lg text-content-secondary">+</button>
                                </div>
                                <p class="text-base font-bold text-content-primary dark:text-content-primary-dark">{{ rupiah($item['price'] * $item['qty']) }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-border bg-bg p-6 text-center text-content-secondary dark:border-border-dark dark:bg-bg-dark">
                            Keranjang masih kosong.
                        </div>
                    @endforelse
                </div>

                <div class="mt-5 space-y-3 rounded-2xl bg-bg p-4 dark:bg-bg-dark">
                    <div class="flex items-center justify-between text-sm text-content-secondary">
                        <span>Subtotal</span>
                        <span class="font-semibold text-content-primary dark:text-content-primary-dark">{{ rupiah($subtotal) }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <select wire:model.live="discountType" class="input !min-h-10 !py-2 text-sm">
                            <option value="nominal">Diskon nominal</option>
                            <option value="percent">Diskon persen</option>
                        </select>
                        <input wire:model.live.debounce.200ms="discountInput" type="text" placeholder="0" class="input !min-h-10 !py-2 text-right text-sm" />
                    </div>

                    <div class="flex items-center justify-between text-sm text-content-secondary">
                        <span>Diskon</span>
                        <span class="font-semibold text-content-primary dark:text-content-primary-dark">{{ rupiah($discountAmount) }}</span>
                    </div>

                    <div class="flex items-center justify-between border-t border-border pt-3 text-base dark:border-border-dark">
                        <span class="font-semibold text-content-primary dark:text-content-primary-dark">Total</span>
                        <span class="text-2xl font-bold text-primary">{{ rupiah($total) }}</span>
                    </div>
                </div>

                <div class="mt-5">
                    <button type="button" class="btn btn-primary w-full" wire:click="openPaymentDialog()" @disabled(empty($cart))>
                        Bayar sekarang
                    </button>
                </div>
            </aside>
        </div>
    </div>

    @if($showPaymentDialog)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
            <div class="w-full max-w-lg rounded-3xl border border-border bg-surface p-5 shadow-2xl dark:border-border-dark dark:bg-surface-dark">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-content-secondary">Pembayaran</p>
                        <h3 class="text-2xl font-bold text-content-primary dark:text-content-primary-dark">{{ rupiah($total) }}</h3>
                    </div>
                    <button type="button" class="btn btn-ghost btn-sm" wire:click="closeDialogs()">Tutup</button>
                </div>

                <div class="mb-4 grid gap-2 sm:grid-cols-3">
                    <button type="button" wire:click="$set('paymentMethod', 'cash')" class="btn {{ $paymentMethod === 'cash' ? 'btn-primary' : 'btn-secondary' }} btn-sm">Tunai</button>
                    <button type="button" wire:click="$set('paymentMethod', 'qris')" class="btn {{ $paymentMethod === 'qris' ? 'btn-primary' : 'btn-secondary' }} btn-sm">QRIS</button>
                    <button type="button" wire:click="$set('paymentMethod', 'transfer')" class="btn {{ $paymentMethod === 'transfer' ? 'btn-primary' : 'btn-secondary' }} btn-sm">Transfer</button>
                </div>

                <label class="mb-2 block text-sm font-medium text-content-secondary">Nominal bayar</label>
                <input type="text" wire:model.live="paidInput" class="input mb-4 text-right text-xl font-semibold" placeholder="0" />

                <div class="mb-4 grid grid-cols-3 gap-2">
                    @foreach(['1','2','3','4','5','6','7','8','9','000','0','⌫'] as $digit)
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="appendDigit('{{ $digit }}')">
                            {{ $digit }}
                        </button>
                    @endforeach
                </div>

                <div class="mb-4 flex items-center justify-between rounded-2xl bg-bg p-3 dark:bg-bg-dark">
                    <span class="text-sm text-content-secondary">Kembalian</span>
                    <span class="text-xl font-bold text-content-primary dark:text-content-primary-dark">{{ rupiah($change) }}</span>
                </div>

                <div class="flex gap-3">
                    <button type="button" class="btn btn-secondary flex-1" wire:click="setExact()">Exact</button>
                    <button type="button" class="btn btn-primary flex-1" wire:click="processPayment()" wire:loading.attr="disabled" wire:target="processPayment">
                        <span wire:loading.remove wire:target="processPayment">Selesaikan</span>
                        <span wire:loading wire:target="processPayment">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($showSuccess && $lastSale)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
            <div class="w-full max-w-md rounded-3xl border border-border bg-surface p-5 shadow-2xl dark:border-border-dark dark:bg-surface-dark">
                <div class="mb-4 text-center">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-success-50 text-success-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-content-primary dark:text-content-primary-dark">Pembayaran berhasil</h3>
                    <p class="mt-2 text-sm text-content-secondary">{{ $lastSale->invoice_no }}</p>
                </div>

                <div class="mb-4 rounded-2xl bg-bg p-3 text-sm dark:bg-bg-dark">
                    <div class="flex items-center justify-between">
                        <span class="text-content-secondary">Total</span>
                        <span class="font-bold text-content-primary dark:text-content-primary-dark">{{ rupiah($lastSale->total) }}</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-content-secondary">Metode</span>
                        <span class="font-medium text-content-primary dark:text-content-primary-dark">{{ strtoupper($lastSale->payment_method) }}</span>
                    </div>
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                    <button type="button" class="btn btn-secondary" wire:click="printReceipt()">Cetak struk</button>
                    <button type="button" class="btn btn-primary" wire:click="newTransaction()">Transaksi baru</button>
                </div>
            </div>
        </div>
    @endif
</div>
