<?php

namespace Database\Seeders;

use App\Models\Stock;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\StockOutDetail;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Str;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cari SEMUA file CSV di dalam folder seeders
        $csvFiles = glob(database_path('seeders/*.csv'));

        if (empty($csvFiles)) {
            $this->command->error("Tidak ada file CSV yang ditemukan di folder seeders.");
            return;
        }

        $this->command->info("Ditemukan " . count($csvFiles) . " file CSV. Memulai proses gabungan...");

        $products = [];
        $stockOuts = [];
        $totalRowCount = 0;

        foreach ($csvFiles as $csvFile) {
            $fileName = basename($csvFile);
            $this->command->info("-> Membaca file: {$fileName} ...");

            $file = fopen($csvFile, 'r');

            // Lewati baris pertama (Header)
            fgetcsv($file);
            $fileRowCount = 0;

            // Membaca baris demi baris
            while (($row = fgetcsv($file)) !== false) {
                // Pastikan memiliki KODE BARANG (Kolom 6 / index 5)
                if (!isset($row[5]) || trim($row[5]) === '') {
                    continue;
                }

                // Filter Keterangan (Kolom 21 / index 20) harus "Penjualan"
                if (!isset($row[20]) || strtolower(trim($row[20])) !== 'penjualan') {
                    continue;
                }

                // Ambil Qty (Kolom 14 / index 13) dan abaikan Qty Minus (Retur)
                $qty = (int) $row[13];
                if ($qty <= 0) {
                    continue;
                }

                $fileRowCount++;
                $totalRowCount++;

                // Pemetaan Kolom CSV
                $dateString   = trim($row[0]);  // Kolom 1: Tanggal
                $customerName = trim($row[3]) ?: 'Pelanggan Umum'; // Kolom 4: Nama Customer
                $productCode  = trim($row[5]);  // Kolom 6: KODE BARANG (Patokan Utama)
                $batchCode    = trim($row[6]) ?: 'TANPA-KODE';     // Kolom 7: Kode Batch
                $productName  = trim($row[10]); // Kolom 11: Nama Barang
                $unit         = trim($row[11]) ?: 'Pcs'; // Kolom 12: Unit

                // Kolom 15: Harga Satuan (Ubah jadi angka bulat)
                $priceString = str_replace(['Rp', '.', ',', ' '], '', $row[14]);
                $price       = (float) $priceString;

                // Mengelompokkan Data Produk BERDASARKAN KODE BARANG
                if (!isset($products[$productCode])) {
                    $products[$productCode] = [
                        'name'          => $productName, // Simpan nama untuk dibuatkan master
                        'unit'          => $unit,
                        'price'         => round($price),
                        'selling_price' => round($price * 1.2),
                        'batches'       => []
                    ];
                }

                // Hitung total qty yang dibutuhkan UNTUK SETIAP KODE BATCH pada KODE BARANG ini
                if (!isset($products[$productCode]['batches'][$batchCode])) {
                    $products[$productCode]['batches'][$batchCode] = 0;
                }
                $products[$productCode]['batches'][$batchCode] += $qty;

                // Parse Tanggal
                try {
                    // 'd' = Hari (01-31), 'm' = Bulan (01-12), 'y' = Tahun 2 digit (misal: 26)
                    $parsedDate = Carbon::createFromFormat('d/m/y', $dateString)->startOfDay();
                } catch (\Exception $e) {
                    try {
                        // Fallback (cadangan) jika ternyata Excel merubahnya jadi 4 digit tahun (dd/mm/yyyy)
                        $parsedDate = Carbon::createFromFormat('d/m/Y', $dateString)->startOfDay();
                    } catch (\Exception $e2) {
                        // Fallback aman terakhir jika format benar-benar hancur/kosong
                        $parsedDate = Carbon::now()->startOfDay();
                    }
                }

                // Kumpulkan data transaksi Stock Out
                $stockOuts[] = [
                    'product_code'  => $productCode, // Patokan untuk mencari Master Stock nanti
                    'customer_name' => $customerName,
                    'batch_code'    => $batchCode,
                    'qty'           => $qty,
                    'price'         => round($price),
                    'date'          => $parsedDate
                ];
            }

            fclose($file);
            $this->command->info("   Selesai mengambil {$fileRowCount} baris Penjualan dari {$fileName}.");
        }

        $this->command->info("---");
        $this->command->info("Total keseluruhan: {$totalRowCount} baris data Penjualan Valid dari " . count($csvFiles) . " file.");
        $this->command->info("Mempersiapkan Master Barang dan Modal Batch (Stock Ins)...");

        $createdStocks = [];

        // 3. BUAT MASTER BARANG & STOCK IN
        foreach ($products as $code => $data) {
            // Gunakan KODE BARANG sebagai pencarian firstOrCreate
            $stock = Stock::firstOrCreate(
                ['code' => $code], // Kondisi pencarian
                [
                    'name'          => $data['name'],
                    'unit'          => $data['unit'],
                    'qty'           => 0,
                    'price'         => $data['price'],
                    'selling_price' => $data['selling_price'],
                    'category_id'   => 1
                ]
            );

            $totalQtyMaster = 0;

            // Buat Riwayat Stock In persis sesuai Batch Code
            foreach ($data['batches'] as $bCode => $neededQty) {
                $modalAwal = $neededQty + 20; // Dilebihkan 20 Pcs
                $totalQtyMaster += $modalAwal;

                StockIn::create([
                    'stock_id'      => $stock->id,
                    'batch_code'    => $bCode,
                    'qty'           => $modalAwal,
                    'qty_remaining' => $modalAwal,
                    'created_at'    => Carbon::parse('2026-01-01')
                ]);
            }

            // Update Total Qty di Master Stock
            $stock->increment('qty', $totalQtyMaster);

            // Simpan model berdasarkan kode agar mudah dicari saat Stock Out
            $createdStocks[$code] = $stock;
        }

        $this->command->info("Memproses " . count($stockOuts) . " Transaksi Penjualan (FIFO)...");

        // 4. PROSES STOCK OUT DAN POTONG BATCH (FIFO)
        foreach ($stockOuts as $idx => $out) {
            // Ambil ID stok berdasarkan kode barang
            $stock = $createdStocks[$out['product_code']];
            $qtyToDeduct = $out['qty'];

            // Generate Invoice otomatis
            $invoice = 'INV-' . $out['date']->format('Ymd') . '-' . str_pad($idx + 1, 5, '0', STR_PAD_LEFT);

            // Simpan header Penjualan (StockOut)
            $stockOutRecord = StockOut::create([
                'stock_id'      => $stock->id,
                'qty'           => $qtyToDeduct,
                'total_price'   => $out['price'] * $qtyToDeduct,
                'customer_name' => $out['customer_name'],
                'invoice'       => $invoice,
                'created_at'    => $out['date'],
                'updated_at'    => $out['date'],
            ]);

            // LOGIKA FIFO: Ambil batch yang stoknya masih sisa
            $batches = StockIn::where('stock_id', $stock->id)
                ->where('qty_remaining', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();

            $remainingDeduct = $qtyToDeduct;

            foreach ($batches as $batch) {
                if ($remainingDeduct <= 0) break;

                if ($batch->qty_remaining >= $remainingDeduct) {
                    $qtyTaken = $remainingDeduct;
                    $batch->qty_remaining -= $remainingDeduct;
                    $remainingDeduct = 0;
                } else {
                    $qtyTaken = $batch->qty_remaining;
                    $remainingDeduct -= $batch->qty_remaining;
                    $batch->qty_remaining = 0;
                }

                $batch->save();

                // Simpan Detail Potongan
                StockOutDetail::create([
                    'stock_out_id' => $stockOutRecord->id,
                    'stock_in_id'  => $batch->id,
                    'qty_taken'    => $qtyTaken
                ]);
            }

            // Kurangi qty keseluruhan barang
            $stock->decrement('qty', $qtyToDeduct);
        }

        $this->command->info("Seeder Selesai! Riwayat Penjualan beserta Rincian Batch berhasil dibuat.");
    }
}
