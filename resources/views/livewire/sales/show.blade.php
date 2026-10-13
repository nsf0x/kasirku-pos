<div class="card p-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-sm text-content-secondary">Invoice</p>
            <h2 class="text-2xl font-semibold">{{ $sale->invoice_no }}</h2>
        </div>
        <div>
            @if($sale->status === 'void')
                <span class="badge badge-danger">Void</span>
            @else
                <span class="badge badge-success">Selesai</span>
            @endif
        </div>
    </div>

    @if(auth()->user()->isOwner() && $sale->status === 'completed')
        <div class="mt-4 flex justify-end">
            <button type="button" class="btn btn-danger btn-sm" wire:click="openVoidDialog">Batalkan transaksi</button>
        </div>
    @endif

    <div class="mt-6 grid gap-4 md:grid-cols-2">
        <div>
            <p class="text-sm text-content-secondary">Kasir</p>
            <p class="font-medium">{{ $sale->cashier?->name ?? '-' }}</p>
        </div>
        <div>
            <p class="text-sm text-content-secondary">Waktu</p>
            <p class="font-medium">{{ $sale->created_at->format('d M Y H:i') }}</p>
        </div>
        <div>
            <p class="text-sm text-content-secondary">Metode bayar</p>
            <p class="font-medium">{{ ucfirst($sale->payment_method) }}</p>
        </div>
        <div>
            <p class="text-sm text-content-secondary">Total</p>
            <p class="font-medium tabular">Rp {{ number_format($sale->total, 0, ',', '.') }}</p>
        </div>

    </div>

    <div class="mt-6">
        <h3 class="font-semibold">Item</h3>
        <ul class="mt-3 space-y-2">
            @foreach($sale->items as $item)
                <li class="flex items-center justify-between gap-3 border-b border-border pb-2">
                    <div>
                        <p class="font-medium">{{ $item->product_name }}</p>
                        <p class="text-sm text-content-secondary">{{ $item->qty }} x Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                    </div>
                    <p class="font-medium tabular">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</div>

@if($showVoidDialog)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4" role="dialog" aria-modal="true" aria-labelledby="void-dialog-title">
        <form wire:submit="void" class="w-full max-w-md space-y-4 rounded-2xl border border-border bg-surface p-5 shadow-dialog dark:border-border-dark dark:bg-surface-dark">
            <div>
                <p class="text-sm text-content-secondary">{{ $sale->invoice_no }}</p>
                <h3 id="void-dialog-title" class="text-xl font-semibold">Batalkan transaksi</h3>
            </div>
            <p class="rounded-xl bg-warning-50 p-3 text-sm text-warning-500">Stok akan dikembalikan. Tindakan ini tercatat dan tidak dapat dibatalkan.</p>
            <div>
                <label for="void-reason" class="mb-1 block text-sm font-medium">Alasan pembatalan</label>
                <textarea id="void-reason" wire:model="voidReason" maxlength="255" rows="3" class="input !h-auto !py-3"></textarea>
                @error('voidReason') <p class="mt-1 text-sm text-danger-500">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3">
                <button type="button" class="btn btn-secondary flex-1" wire:click="$set('showVoidDialog', false)">Kembali</button>
                <button type="submit" class="btn btn-danger flex-1" wire:loading.attr="disabled">Konfirmasi void</button>
            </div>
        </form>
    </div>
@endif
