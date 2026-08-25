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
        .meta { font-size: 12px; margin: 0; color: #555; }

        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 12px; vertical-align: top; }
        th { text-align: left; background: #f5f5f5; }
        .right { text-align: right; }
        .center { text-align: center; }
        .nowrap { white-space: nowrap; }

        /* Styling untuk list batch agar rapi */
        .batch-list { margin: 0; padding-left: 16px; color: #333; font-size: 11px; }
        .batch-list li { margin-bottom: 2px; }

        @media print {
            .no-print { display: none !important; }
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
                <th class="center" style="width: 5%;">No</th>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Product</th>
                <th class="right">Qty</th>
                <th style="width: 25%;">Rincian Batch</th>
                <th class="right">Total Price</th>
                <th class="nowrap">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $row)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $row->invoice ?? '-' }}</td>
                    <td>{{ $row->customer_name ?? '-' }}</td>
                    <td>{{ optional($row->stock)->name ?? 'Produk tidak ditemukan' }}</td>
                    <td class="right">
                        {{ $row->qty }} {{ optional($row->stock)->unit }}
                    </td>
                    <td>
                        {{-- Logika Menampilkan Rincian Batch yang Terpotong --}}
                        @if($row->details->isEmpty())
                            <span style="color: #999; font-style:italic;">Tidak ada rincian batch</span>
                        @else
                            <ul class="batch-list">
                                @foreach($row->details as $detail)
                                    <li>
                                        <strong>{{ optional($detail->stockIn)->batch_code ?? 'Tanpa Kode' }}</strong>
                                        (Diambil: {{ $detail->qty_taken }})
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td class="right nowrap">Rp {{ number_format($row->total_price, 0, ',', '.') }}</td>
                    <td class="nowrap">{{ optional($row->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center">Tidak ada data histori penjualan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });

        window.addEventListener('afterprint', function () {
            // Uncomment baris di bawah ini jika ingin tab otomatis tertutup setelah print
            // window.close();
        });
    </script>
</body>
</html>
