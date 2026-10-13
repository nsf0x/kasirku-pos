<div class="space-y-6">
    <div class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
        <div class="card p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm text-content-secondary">Persediaan saat ini</p>
                    <h3 class="text-xl font-semibold">Produk</h3>
                </div>
                @if(auth()->user()->isOwner())
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="openStockDialog('in')">Stok masuk</button>
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="openStockDialog('out')">Stok keluar</button>
                        <button type="button" class="btn btn-primary btn-sm" wire:click="openStockDialog('adjust')">Opname</button>
                    </div>
                @endif
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>SKU</th>
                            <th>Stok</th>
                            <th>Min</th>
                            <th>Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->sku }}</td>
                                <td class="tabular">{{ $product->stock }}</td>
                                <td class="tabular">{{ $product->min_stock }}</td>
                                <td class="tabular">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-8 text-content-secondary">Belum ada produk.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $products->links() }}</div>
        </div>

        <div class="card p-5">
            <h3 class="text-xl font-semibold">Mutasi terbaru</h3>
            <div class="mt-4 space-y-3">
                @forelse($movements as $movement)
                    <div class="border-b border-border pb-2">
                        <div class="flex items-center justify-between">
                            <span class="font-medium">{{ $movement->product?->name ?? '-' }}</span>
                            <span class="tabular {{ $movement->qty > 0 ? 'text-success' : 'text-danger' }}">{{ $movement->qty }}</span>
                        </div>
                        <p class="text-sm text-content-secondary">{{ $movement->type }} • {{ $movement->user?->name ?? '-' }}</p>
                    </div>
                @empty
                    <p class="text-content-secondary">Belum ada mutasi stok.</p>
                @endforelse
            </div>
        </div>

        @if($showStockDialog)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4" role="dialog" aria-modal="true" aria-labelledby="stock-dialog-title">
                <form wire:submit="recordStockMovement" class="w-full max-w-lg space-y-4 rounded-2xl border border-border bg-surface p-5 shadow-dialog dark:border-border-dark dark:bg-surface-dark">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-content-secondary">Catat persediaan</p>
                            <h3 id="stock-dialog-title" class="text-xl font-semibold">
                                {{ ['in' => 'Stok masuk', 'out' => 'Stok keluar', 'adjust' => 'Opname stok'][$movementType] }}
                            </h3>
                        </div>
                        <button type="button" class="btn btn-ghost btn-sm" wire:click="$set('showStockDialog', false)">Tutup</button>
                    </div>

                    <div>
                        <label for="stock-product" class="mb-1 block text-sm font-medium">Produk</label>
                        <select id="stock-product" wire:model="productId" class="input">
                            <option value="">Pilih produk</option>
                            @foreach($allProducts as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} (stok {{ $product->stock }})</option>
                            @endforeach
                        </select>
                        @error('productId') <p class="mt-1 text-sm text-danger-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="stock-quantity" class="mb-1 block text-sm font-medium">
                            {{ $movementType === 'adjust' ? 'Jumlah fisik setelah opname' : 'Jumlah unit' }}
                        </label>
                        <input id="stock-quantity" type="number" min="{{ $movementType === 'adjust' ? 0 : 1 }}" step="1" wire:model="quantity" class="input">
                        @error('quantity') <p class="mt-1 text-sm text-danger-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="stock-note" class="mb-1 block text-sm font-medium">Catatan</label>
                        <input id="stock-note" type="text" maxlength="255" wire:model="note" class="input" placeholder="Contoh: pembelian supplier / barang rusak">
                        @error('note') <p class="mt-1 text-sm text-danger-500">{{ $message }}</p> @enderror
                    </div>

                    @if($movementType === 'adjust')
                        <p class="rounded-xl bg-warning-50 p-3 text-sm text-warning-500">
                            Isi jumlah fisik yang benar-benar dihitung. Sistem menyimpan selisih stok secara otomatis.
                        </p>
                    @endif

                    <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled" wire:target="recordStockMovement">
                        <span wire:loading.remove wire:target="recordStockMovement">Simpan mutasi</span>
                        <span wire:loading wire:target="recordStockMovement">Menyimpan...</span>
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
