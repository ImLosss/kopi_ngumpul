<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Stok Barang</title>

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

        .batch-list { margin: 0; padding-left: 16px; color: #333; }
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
        <p class="title">Laporan Stok Barang (Inventory)</p>
        <p class="meta">Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
        @if(!empty($search))
            <p class="meta">Pencarian: {{ $search }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th class="center" style="width: 5%;">No</th>
                <th style="width: 25%;">Nama Barang</th>
                <th style="width: 10%;">Satuan</th>
                <th style="width: 45%;">Rincian Batch & Sisa Stok</th>
                <th class="right" style="width: 15%;">Total Qty</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stocks as $index => $row)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->unit }}</td>
                    <td>
                        {{-- Logika pengelompokan batch langsung di Blade --}}
                        @php
                            $groupedBatches = [];
                            // Pastikan relasi stockIns sudah di-load dari controller
                            if($row->stockIns) {
                                foreach($row->stockIns as $batch) {
                                    if($batch->qty_remaining > 0) {
                                        $code = $batch->batch_code ?: 'Tanpa Kode';
                                        if(!isset($groupedBatches[$code])) {
                                            $groupedBatches[$code] = 0;
                                        }
                                        $groupedBatches[$code] += $batch->qty_remaining;
                                    }
                                }
                            }
                        @endphp

                        @if(empty($groupedBatches))
                            <span style="color: #999; font-style:italic;">Stok Kosong / Habis</span>
                        @else
                            <ul class="batch-list">
                                @foreach($groupedBatches as $code => $qty)
                                    <li><strong>{{ $code }}</strong> (Sisa: {{ $qty }})</li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td class="right" style="font-weight: bold; font-size: 14px;">
                        {{ $row->qty }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="center">Tidak ada data barang saat ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });

        window.addEventListener('afterprint', function () {
            // Opsional: Tutup tab setelah print selesai
            // window.close();
        });
    </script>
</body>
</html>
