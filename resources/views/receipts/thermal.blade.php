<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Struk {{ $sale->invoice_no }}</title>
    <style>
        body { font-family: monospace; font-size: 12px; margin: 20px; width: 300px; }
        .center { text-align: center; }
        .line { border-top: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="center">
        <div class="bold">KasirKu</div>
        <div>POS UMKM Indonesia</div>
    </div>

    <div class="line"></div>

    <div>
        <div>Invoice: {{ $sale->invoice_no }}</div>
        <div>Kasir: {{ $sale->cashier?->name ?? '-' }}</div>
        <div>Tgl: {{ $sale->created_at->format('d/m/Y H:i') }}</div>
    </div>

    <div class="line"></div>

    <table>
        @foreach($sale->items as $item)
            <tr>
                <td>{{ $item->product_name }} x {{ $item->qty }}</td>
                <td class="right">{{ rupiah($item->subtotal) }}</td>
            </tr>
        @endforeach
    </table>

    <div class="line"></div>

    <table>
        <tr>
            <td>Subtotal</td>
            <td class="right">{{ rupiah($sale->subtotal) }}</td>
        </tr>
        <tr>
            <td>Diskon</td>
            <td class="right">{{ rupiah($sale->discount) }}</td>
        </tr>
        <tr class="bold">
            <td>Total</td>
            <td class="right">{{ rupiah($sale->total) }}</td>
        </tr>
        <tr>
            <td>Bayar</td>
            <td class="right">{{ rupiah($sale->paid_amount) }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td class="right">{{ rupiah($sale->change_amount) }}</td>
        </tr>
    </table>

    <div class="line"></div>
    <div class="center">Terima kasih</div>
</body>
</html>
