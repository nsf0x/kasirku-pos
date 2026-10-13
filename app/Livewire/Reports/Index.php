<?php

namespace App\Livewire\Reports;

use App\Exports\SalesReportExport;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    public string $fromDate = '';
    public string $toDate = '';

    public function mount(): void
    {
        $this->fromDate = now()->subDays(30)->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
    }

    public function exportExcel()
    {
        $this->validateDateRange();

        return Excel::download(
            new SalesReportExport($this->fromDate, $this->toDate),
            'laporan-penjualan-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function exportPdf()
    {
        $this->validateDateRange();

        $sales = $this->buildBaseQuery()->with(['cashier', 'items'])->orderByDesc('created_at')->get();
        $summary = $this->getSummary($sales);

        return response()->streamDownload(function () use ($sales, $summary) {
            echo Pdf::loadView('exports.sales-report-pdf', [
                'sales' => $sales,
                'summary' => $summary,
            ])->output();
        }, 'laporan-penjualan-' . now()->format('YmdHis') . '.pdf');
    }

    protected function buildBaseQuery()
    {
        return Sale::completed()
            ->when($this->fromDate, fn ($query) => $query->whereDate('created_at', '>=', $this->fromDate))
            ->when($this->toDate, fn ($query) => $query->whereDate('created_at', '<=', $this->toDate));
    }

    protected function validateDateRange(): void
    {
        $this->validate([
            'fromDate' => ['required', 'date_format:Y-m-d'],
            'toDate' => ['required', 'date_format:Y-m-d', 'after_or_equal:fromDate'],
        ]);
    }

    public function updatedFromDate(): void
    {
        $this->validateOnly('fromDate', ['fromDate' => ['required', 'date_format:Y-m-d']]);
        if ($this->toDate !== '') {
            $this->validateOnly('toDate', ['toDate' => ['required', 'date_format:Y-m-d', 'after_or_equal:fromDate']]);
        }
    }

    public function updatedToDate(): void
    {
        $this->validateOnly('toDate', [
            'toDate' => ['required', 'date_format:Y-m-d', 'after_or_equal:fromDate'],
        ]);
    }

    protected function getSummary($sales): array
    {
        return [
            'total_revenue' => $sales->sum('total'),
            'total_discount' => $sales->sum('discount'),
            'total_transactions' => $sales->count(),
            'total_items' => $sales->sum(fn ($sale) => $sale->items->sum('qty')),
            'average_ticket' => $sales->count() > 0 ? $sales->avg('total') : 0,
            'gross_profit' => $sales->sum(fn ($sale) => $sale->gross_profit),
        ];
    }

    public function render()
    {
        $this->authorize('viewAny', User::class);

        $filtersValid = Validator::make([
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
        ], [
            'fromDate' => ['required', 'date_format:Y-m-d'],
            'toDate' => ['required', 'date_format:Y-m-d', 'after_or_equal:fromDate'],
        ])->passes();

        $sales = $this->buildBaseQuery()
            ->with(['cashier', 'items'])
            ->orderByDesc('created_at')
            ->when(! $filtersValid, fn ($query) => $query->whereRaw('1 = 0'))
            ->limit(25)->get();

        $summary = $filtersValid
            ? $this->getDatabaseSummary()
            : [
                'total_revenue' => 0,
                'total_discount' => 0,
                'total_transactions' => 0,
                'total_items' => 0,
                'average_ticket' => 0,
                'gross_profit' => 0,
            ];

        return view('livewire.reports.index', compact('sales', 'summary'))
            ->layout('layouts.app', ['title' => 'Laporan']);
    }

    protected function getDatabaseSummary(): array
    {
        $aggregate = $this->buildBaseQuery()
            ->selectRaw('COALESCE(SUM(total), 0) as total_revenue')
            ->selectRaw('COALESCE(SUM(discount), 0) as total_discount')
            ->selectRaw('COUNT(*) as total_transactions')
            ->selectRaw('COALESCE(AVG(total), 0) as average_ticket')
            ->first();

        $totalItems = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->when($this->fromDate, fn ($query) => $query->whereDate('sales.created_at', '>=', $this->fromDate))
            ->when($this->toDate, fn ($query) => $query->whereDate('sales.created_at', '<=', $this->toDate))
            ->sum('sale_items.qty');

        $itemProfit = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->when($this->fromDate, fn ($query) => $query->whereDate('sales.created_at', '>=', $this->fromDate))
            ->when($this->toDate, fn ($query) => $query->whereDate('sales.created_at', '<=', $this->toDate))
            ->selectRaw('COALESCE(SUM((sale_items.price - sale_items.cost_price) * sale_items.qty), 0) as item_profit')
            ->value('item_profit');

        return [
            'total_revenue' => (float) $aggregate->total_revenue,
            'total_discount' => (float) $aggregate->total_discount,
            'total_transactions' => (int) $aggregate->total_transactions,
            'total_items' => (int) $totalItems,
            'average_ticket' => (float) $aggregate->average_ticket,
            'gross_profit' => (float) $itemProfit - (float) $aggregate->total_discount,
        ];
    }
}
