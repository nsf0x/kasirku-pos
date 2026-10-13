<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1b2430; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #dfe5eb; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
        .summary { margin-bottom: 16px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
    </style>
</head>
<body>
    <h2>Laporan Penjualan KasirKu</h2>

    <div class="summary">
        <div class="summary-row"><span>Total Penjualan</span><strong>{{ rupiah($summary['total_revenue']) }}</strong></div>
        <div class="summary-row"><span>Diskon</span><strong>{{ rupiah($summary['total_discount']) }}</strong></div>
        <div class="summary-row"><span>Transaksi</span><strong>{{ $summary['total_transactions'] }}</strong></div>
        <div class="summary-row"><span>Gross Profit</span><strong>{{ rupiah($summary['gross_profit']) }}</strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Kasir</th>
                <th>Metode</th>
                <th>Subtotal</th>
                <th>Diskon</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->invoice_no }}</td>
                    <td>{{ $sale->cashier?->name ?? '-' }}</td>
                    <td>{{ strtoupper($sale->payment_method) }}</td>
                    <td>{{ rupiah($sale->subtotal) }}</td>
                    <td>{{ rupiah($sale->discount) }}</td>
                    <td>{{ rupiah($sale->total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
