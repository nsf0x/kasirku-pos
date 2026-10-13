<?php

namespace App\Livewire\Dashboard;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $this->authorize('viewAny', Sale::class);

        $todaySales = Sale::completed()->whereDate('created_at', today())->sum('total');
        $todayTransactions = Sale::completed()->whereDate('created_at', today())->count();
        $yesterdaySales = Sale::completed()->whereDate('created_at', today()->subDay())->sum('total');

        $todayProductProfit = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereDate('sales.created_at', today())
            ->selectRaw('SUM((sale_items.price - sale_items.cost_price) * sale_items.qty) as total_profit')
            ->value('total_profit') ?? 0;
        $todayDiscount = Sale::completed()
            ->whereDate('created_at', today())
            ->sum('discount');
        $todayProfit = $todayProductProfit - $todayDiscount;

        $lowStockProducts = Product::with('category')
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->where('stock', '>', 0)
            ->orderBy('stock')
            ->limit(6)
            ->get();

        $topProducts = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->selectRaw('sale_items.product_name as name, SUM(sale_items.qty) as total_qty')
            ->groupBy('sale_items.product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $chartStart = now()->subDays(6)->startOfDay();
        $chartSales = Sale::completed()
            ->whereBetween('created_at', [$chartStart, now()->endOfDay()])
            ->selectRaw('DATE(created_at) as day, SUM(total) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('day')
            ->get();

        $chartLabels = [];
        $chartValues = [];
        $current = $chartStart->copy();

        for ($i = 0; $i < 7; $i++) {
            $label = $current->format('d M');
            $chartLabels[] = $label;

            $value = $chartSales->firstWhere('day', $current->format('Y-m-d'))?->total ?? 0;
            $chartValues[] = (float) $value;

            $current->addDay();
        }

        $paymentBreakdown = Sale::completed()
            ->whereBetween('created_at', [$chartStart, now()->endOfDay()])
            ->selectRaw('payment_method, SUM(total) as total')
            ->groupBy('payment_method')
            ->get();

        return view('livewire.dashboard.index', compact(
            'todaySales',
            'todayTransactions',
            'yesterdaySales',
            'todayProfit',
            'lowStockProducts',
            'topProducts',
            'chartLabels',
            'chartValues',
            'paymentBreakdown'
        ))->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
