<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.18em] text-primary">Laporan</p>
            <h1 class="mt-1 text-3xl font-bold text-content-primary dark:text-content-primary-dark">Penjualan</h1>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2 rounded-xl border border-border bg-surface px-3 py-2 dark:border-border-dark dark:bg-surface-dark">
                    <label for="report-from-date" class="text-sm text-content-secondary">Dari</label>
                    <input id="report-from-date" type="date" wire:model.live="fromDate" class="rounded-lg border border-border bg-bg px-2 py-1.5 text-sm dark:border-border-dark dark:bg-bg-dark">
                </div>
                @error('fromDate') <span class="text-xs text-danger-500">{{ $message }}</span> @enderror
            </div>
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2 rounded-xl border border-border bg-surface px-3 py-2 dark:border-border-dark dark:bg-surface-dark">
                    <label for="report-to-date" class="text-sm text-content-secondary">Sampai</label>
                    <input id="report-to-date" type="date" wire:model.live="toDate" class="rounded-lg border border-border bg-bg px-2 py-1.5 text-sm dark:border-border-dark dark:bg-bg-dark">
                </div>
                @error('toDate') <span class="text-xs text-danger-500">{{ $message }}</span> @enderror
            </div>
            <button type="button" class="btn btn-secondary btn-sm" wire:click="exportExcel()">Excel</button>
            <button type="button" class="btn btn-primary btn-sm" wire:click="exportPdf()">PDF</button>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="card p-4">
            <p class="text-sm text-content-secondary">Total penjualan</p>
            <p class="mt-2 text-2xl font-bold tabular">{{ rupiah($summary['total_revenue']) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-content-secondary">Transaksi</p>
            <p class="mt-2 text-2xl font-bold tabular">{{ $summary['total_transactions'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-content-secondary">Diskon</p>
            <p class="mt-2 text-2xl font-bold tabular">{{ rupiah($summary['total_discount']) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-content-secondary">Laba kotor</p>
            <p class="mt-2 text-2xl font-bold tabular">{{ rupiah($summary['gross_profit']) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-content-secondary">Rata-rata transaksi</p>
            <p class="mt-2 text-2xl font-bold tabular">{{ rupiah($summary['average_ticket']) }}</p>
        </div>
    </div>

    <div class="card p-5">
        <h3 class="text-xl font-semibold">Detail transaksi</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Tanggal</th>
                        <th>Kasir</th>
                        <th>Metode</th>
                        <th>Subtotal</th>
                        <th>Diskon</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td>{{ $sale->invoice_no }}</td>
                            <td>{{ $sale->created_at->format('d M Y') }}</td>
                            <td>{{ $sale->cashier?->name ?? '-' }}</td>
                            <td>{{ strtoupper($sale->payment_method) }}</td>
                            <td class="tabular">{{ rupiah($sale->subtotal) }}</td>
                            <td class="tabular">{{ rupiah($sale->discount) }}</td>
                            <td class="tabular font-semibold">{{ rupiah($sale->total) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-content-secondary">Belum ada data penjualan pada rentang tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
