<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        protected string $fromDate,
        protected string $toDate,
    ) {}

    public function query(): Builder
    {
        return Sale::completed()
            ->with(['cashier', 'items'])
            ->when($this->fromDate, fn ($query) => $query->whereDate('created_at', '>=', $this->fromDate))
            ->when($this->toDate, fn ($query) => $query->whereDate('created_at', '<=', $this->toDate))
            ->orderByDesc('created_at');
    }

    public function map($sale): array
    {
        return [
            $sale->invoice_no,
            $sale->created_at->format('Y-m-d H:i:s'),
            $sale->cashier?->name ?? '-',
            strtoupper($sale->payment_method),
            (float) $sale->subtotal,
            (float) $sale->discount,
            (float) $sale->total,
            $sale->gross_profit,
        ];
    }

    public function headings(): array
    {
        return ['Invoice', 'Tanggal', 'Kasir', 'Metode Pembayaran', 'Subtotal', 'Diskon', 'Total', 'Laba Kotor'];
    }
}
