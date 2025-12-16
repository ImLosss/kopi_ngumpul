<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Penjualan</title>

    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #111; }
        .header { margin-bottom: 16px; }
        .title { font-size: 18px; font-weight: 700; margin: 0 0 6px; }
        .meta { font-size: 12px; margin: 0; }

        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 12px; }
        th { text-align: left; background: #f5f5f5; }
        .right { text-align: right; }
        .nowrap { white-space: nowrap; }

        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 12px;">
        <button onclick="window.print()">Print</button>
    </div>

    <div class="header">
        <p class="title">Laporan Barang Keluar / Laporan Penjualan</p>
        <p class="meta">
            Periode:
            @if(!empty($dateRangeLabel))
                {{ $dateRangeLabel }}
            @else
                {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
            @endif
        </p>
        @if(!empty($search))
            <p class="meta">Pencarian: {{ $search }}</p>
        @endif
        <p class="meta">Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th class="right">Qty</th>
                <th class="right">Total Price</th>
                <th class="nowrap">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $row)
                <tr>
                    <td>{{ optional($row->stock)->name ?? 'Produk tidak ditemukan' }}</td>
                    <td class="right">{{ $row->qty }}</td>
                    <td class="right">Rp {{ number_format($row->total_price, 0, ',', '.') }}</td>
                    <td class="nowrap">{{ optional($row->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });

        window.addEventListener('afterprint', function () {
            window.close();
        });
    </script>
</body>
</html>
