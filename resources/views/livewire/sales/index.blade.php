<div class="space-y-4">
    <div class="card p-4">
        <div class="flex flex-wrap gap-3">
            <select wire:model.live="status" class="input max-w-[180px]">
                <option value="">Semua status</option>
                <option value="completed">Selesai</option>
                <option value="void">Void</option>
            </select>
            <select wire:model.live="paymentMethod" class="input max-w-[180px]">
                <option value="">Semua metode</option>
                <option value="cash">Tunai</option>
                <option value="qris">QRIS</option>
                <option value="transfer">Transfer</option>
            </select>
            <input wire:model.live="dateFrom" type="date" class="input max-w-[180px]">
            <input wire:model.live="dateTo" type="date" class="input max-w-[180px]">
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Kasir</th>
                        <th>Metode</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td><a href="{{ route('sales.show', $sale) }}" class="text-primary font-medium">{{ $sale->invoice_no }}</a></td>
                            <td>{{ $sale->cashier?->name ?? '-' }}</td>
                            <td>{{ ucfirst($sale->payment_method) }}</td>
                            <td class="tabular">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                            <td>
                                @if($sale->status === 'void')
                                    <span class="badge badge-danger">Void</span>
                                @else
                                    <span class="badge badge-success">Selesai</span>
                                @endif
                            </td>
                            <td>{{ $sale->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-content-secondary">Belum ada transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $sales->links() }}</div>
    </div>
</div>
