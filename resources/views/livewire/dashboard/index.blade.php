<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card primary">
            <p class="text-sm font-medium text-content-secondary">Penjualan hari ini</p>
            <p class="mt-3 text-3xl font-bold tabular text-content-primary dark:text-content-primary-dark">{{ rupiah($todaySales) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-content-secondary">Jumlah transaksi</p>
            <p class="mt-3 text-3xl font-bold tabular text-content-primary dark:text-content-primary-dark">{{ $todayTransactions }}</p>
        </div>
        <div class="stat-card accent">
            <p class="text-sm font-medium text-content-secondary">Laba kotor</p>
            <p class="mt-3 text-3xl font-bold tabular text-content-primary dark:text-content-primary-dark">{{ rupiah($todayProfit) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-content-secondary">Dibanding kemarin</p>
            <p class="mt-3 text-3xl font-bold tabular text-content-primary dark:text-content-primary-dark">
                {{ $yesterdaySales > 0 ? number_format((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 1) : 0 }}%
            </p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[2fr_1fr]">
        <div class="card p-5">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-xl font-semibold text-content-primary dark:text-content-primary-dark">Grafik penjualan 7 hari</h3>
                <span class="badge badge-primary">{{ now()->subDays(6)->format('d M') }} — {{ now()->format('d M') }}</span>
            </div>
            <div class="mt-4 h-72 rounded-2xl bg-bg p-3 dark:bg-bg-dark">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        <div class="card p-5">
            <h3 class="text-xl font-semibold text-content-primary dark:text-content-primary-dark">Produk terlaris</h3>
            <ul class="mt-4 space-y-3">
                @forelse($topProducts as $product)
                    <li class="flex items-center justify-between border-b border-border pb-2">
                        <span class="font-medium text-content-primary dark:text-content-primary-dark">{{ $product->name }}</span>
                        <span class="font-semibold tabular text-primary">{{ $product->total_qty }} pcs</span>
                    </li>
                @empty
                    <li class="text-content-secondary">Belum ada data penjualan.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="card p-5">
            <h3 class="text-xl font-semibold text-content-primary dark:text-content-primary-dark">Stok menipis</h3>
            <div class="mt-4 space-y-3">
                @forelse($lowStockProducts as $product)
                    <div class="flex items-center justify-between border-b border-border pb-2">
                        <div>
                            <p class="font-medium text-content-primary dark:text-content-primary-dark">{{ $product->name }}</p>
                            <p class="text-sm text-content-secondary">{{ $product->category->name ?? '-' }}</p>
                        </div>
                        <span class="badge badge-warning">{{ $product->stock }} tersisa</span>
                    </div>
                @empty
                    <p class="text-content-secondary">Stok aman di semua produk.</p>
                @endforelse
            </div>
        </div>
        <div class="card p-5">
            <h3 class="text-xl font-semibold text-content-primary dark:text-content-primary-dark">Metode bayar</h3>
            <div class="mt-4 space-y-3">
                @php
                    $totalPayment = $paymentBreakdown->sum('total');
                @endphp
                @foreach(['cash' => 'Tunai', 'qris' => 'QRIS', 'transfer' => 'Transfer'] as $key => $label)
                    @php
                        $value = $paymentBreakdown->firstWhere('payment_method', $key)?->total ?? 0;
                        $percent = $totalPayment > 0 ? ($value / $totalPayment) * 100 : 0;
                    @endphp
                    <div class="soft-panel flex justify-between text-sm text-content-secondary">
                        <span>{{ $label }}</span>
                        <span class="font-semibold text-content-primary tabular dark:text-content-primary-dark">{{ number_format($percent, 1) }}%</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('salesChart');
        if (!canvas) return;

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Penjualan',
                    data: @json($chartValues),
                    borderColor: '#0E7C86',
                    backgroundColor: 'rgba(14, 124, 134, 0.16)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#0E7C86',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return 'Rp ' + Number(context.parsed.y).toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#5B6672' }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return 'Rp ' + Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 });
                            },
                            color: '#5B6672'
                        },
                        grid: { color: 'rgba(91, 102, 114, 0.12)' }
                    }
                }
            }
        });
    });
</script>
