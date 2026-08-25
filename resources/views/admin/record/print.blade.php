<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Penjualan</title>

    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #111; margin: 0; padding: 20px; }
        .header { margin-bottom: 16px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .title { font-size: 16px; font-weight: 700; margin: 0 0 4px; text-transform: uppercase; }
        .meta { font-size: 11px; margin: 2px 0; color: #444; }

        /* Mengatur font tabel menjadi lebih kecil dan rapi */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #aaa; padding: 5px 6px; font-size: 10px; vertical-align: top; }
        th { text-align: left; background: #e9ecef; font-weight: bold; }

        .right { text-align: right; }
        .center { text-align: center; }
        .nowrap { white-space: nowrap; }

        /* Style untuk list batch */
        .batch-list { margin: 0; padding-left: 12px; color: #222; }
        .batch-list li { margin-bottom: 2px; }

        .total-row th { background: #d6d8db; font-size: 11px; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 12px;">
        <button onclick="window.print()" style="padding: 6px 12px; cursor:pointer;">Print Laporan</button>
    </div>

    <div class="header">
        <p class="title">Laporan Barang Keluar / Penjualan</p>
        <p class="meta">
            <strong>Periode:</strong>
            @if(!empty($dateRangeLabel))
                {{ $dateRangeLabel }}
            @else
                {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
            @endif
        </p>
        @if(!empty($search))
            <p class="meta"><strong>Pencarian:</strong> {{ $search }}</p>
        @endif
        <p class="meta"><strong>Dicetak pada:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="center" style="width: 3%;">No</th>
                <th class="nowrap">Faktur & Pelanggan</th>
                <th>Produk</th>
                <th>Rincian Pengambilan (Batch)</th>
                <th class="center">Qty</th>
                <th class="right">Total Penjualan</th>
                <th class="right">Keuntungan</th>
                <th class="nowrap">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalSales = 0;
                $grandTotalProfit = 0;
            @endphp

            @forelse($records as $index => $row)
                @php
                    // Hitung modal dan keuntungan
                    $modal = $row->stock ? ($row->stock->price * $row->qty) : 0;
                    $keuntungan = $row->total_price - $modal;

                    // Akumulasi total keseluruhan
                    $grandTotalSales += $row->total_price;
                    $grandTotalProfit += $keuntungan;
                @endphp
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $row->invoice ?? '-' }}</strong><br>
                        {{ $row->customer_name ?? '-' }}
                    </td>
                    <td>{{ optional($row->stock)->name ?? 'Produk Dihapus' }}</td>
                    <td>
                        @if($row->details && $row->details->isNotEmpty())
                            <ul class="batch-list">
                                @foreach($row->details as $detail)
                                    <li>
                                        {{ $detail->stockIn->batch_code ?? 'Tanpa Kode' }}
                                        (Qty: {{ $detail->qty_taken }})
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <span style="color:#777; font-style:italic;">-</span>
                        @endif
                    </td>
                    <td class="center">{{ $row->qty }} {{ optional($row->stock)->unit }}</td>
                    <td class="right">Rp {{ number_format($row->total_price, 0, ',', '.') }}</td>
                    <td class="right" style="color: green; font-weight: bold;">
                        Rp {{ number_format($keuntungan, 0, ',', '.') }}
                    </td>
                    <td class="nowrap">{{ optional($row->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center">Tidak ada data penjualan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if($records->isNotEmpty())
        <tfoot>
            <tr class="total-row">
                <th colspan="5" class="right">TOTAL KESELURUHAN</th>
                <th class="right">Rp {{ number_format($grandTotalSales, 0, ',', '.') }}</th>
                <th class="right" style="color: green;">Rp {{ number_format($grandTotalProfit, 0, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
        @endif
    </table>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });

        window.addEventListener('afterprint', function () {
            // Uncomment baris di bawah jika ingin tab otomatis tertutup setelah print
            window.close();
        });
    </script>
</body>
</html>
