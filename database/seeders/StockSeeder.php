<?php

namespace Database\Seeders;

use App\Models\Stock;
use App\Models\StockOut;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Daftar file CSV dengan mapping ke bulan sekarang
        $csvFiles = $this->getCsvFiles();

        $allProducts = [];
        $allStockOuts = [];

        foreach ($csvFiles as $csvFile) {
            $this->command->info('Memproses file: ' . basename($csvFile['file']) . ' -> Bulan: ' . $csvFile['target_month']);

            if (!file_exists($csvFile['file'])) {
                $this->command->warn('File CSV tidak ditemukan: ' . $csvFile['file']);
                continue;
            }

            $result = $this->processCsvFile($csvFile['file'], $csvFile['target_date']);

            // Gabungkan produk dari semua file
            foreach ($result['products'] as $productName => $quantity) {
                if (!isset($allProducts[$productName])) {
                    $allProducts[$productName] = $quantity;
                } else {
                    $allProducts[$productName] += $quantity;
                }
            }

            // Gabungkan stock outs dari semua file
            $allStockOuts = array_merge($allStockOuts, $result['stockOuts']);
        }

        // Insert produk ke tabel stocks menggunakan Eloquent
        $createdStocks = [];
        foreach ($allProducts as $productName => $totalQty) {
            // Cek apakah produk sudah ada
            $existingStock = Stock::where('name', $productName)->first();

            if ($existingStock) {
                // Update quantity jika produk sudah ada
                $existingStock->update(['qty' => $existingStock->qty + $totalQty]);
                $createdStocks[$productName] = $existingStock;
                $this->command->info("Updated stock untuk: {$productName}");
            } else {
                // Buat produk baru
                $stock = Stock::create([
                    'name' => $productName,
                    'category_id' => 1,
                    'qty' => $totalQty,
                    'price' => 10000
                ]);
                $createdStocks[$productName] = $stock;
                $this->command->info("Created new stock untuk: {$productName}");
            }
        }

        // Insert stock_outs menggunakan Eloquent
        foreach ($allStockOuts as $stockOut) {
            $stock = $createdStocks[$stockOut['product_name']];

            StockOut::create([
                'stock_id' => $stock->id,
                'qty' => $stockOut['qty'],
                'total_price' => $stock->price * $stockOut['qty'],
                'created_at' => $stockOut['created_at'],
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Berhasil mengimpor ' . count($allProducts) . ' produk dan ' . count($allStockOuts) . ' stock outs dari ' . count($csvFiles) . ' file CSV');
    }

    /**
     * Mendapatkan daftar file CSV dengan mapping ke bulan terbaru
     */
    private function getCsvFiles(): array
    {
        $csvFiles = [];

        // 12 file CSV yang tersedia (lengkap dari Januari sampai Desember 2019)
        $availableFiles = [
            'July',      // i=5  -> Mei 2025
            'June',      // i=6  -> April 2025
            'May',       // i=7  -> Maret 2025
            'April',     // i=8  -> Februari 2025
            'March',     // i=9  -> Januari 2025
            'February',  // i=10 -> Desember 2024
            'January'    // i=11 -> November 2024
        ];

        // Mapping untuk 12 bulan
        for ($i = 0; $i < 7; $i++) {
            // PERBAIKAN: Langsung akses array dengan index $i (bukan $i % 5)
            $csvMonth = $availableFiles[$i];

            $fileName = "Sales_{$csvMonth}_2019.csv";
            $filePath = database_path("seeders/{$fileName}");

            // Skip jika file tidak ada
            if (!file_exists($filePath)) {
                $this->command->warn("File tidak ditemukan, skip: {$fileName}");
                continue;
            }

            // Gunakan startOfMonth untuk menghindari masalah hari invalid
            $targetDate = Carbon::now()->startOfMonth()->subMonths($i);

            $csvFiles[] = [
                'file' => $filePath,
                'target_date' => $targetDate,
                'target_month' => $targetDate->format('F Y'),
                'csv_month' => $csvMonth
            ];

            $this->command->info("Mapping: Sales_{$csvMonth}_2019.csv -> {$targetDate->format('F Y')}");
        }

        return $csvFiles;
    }

    /**
     * Memproses satu file CSV dengan tanggal target
     */
    private function processCsvFile(string $csvFile, Carbon $targetDate): array
    {
        $file = fopen($csvFile, 'r');

        // Skip header row
        fgetcsv($file);

        $products = [];
        $stockOuts = [];

        // Baca setiap baris CSV
        while (($row = fgetcsv($file)) !== false) {
            // Skip baris kosong
            if (empty($row[0]) || empty($row[1])) {
                continue;
            }

            $orderId = $row[0];
            $productName = trim($row[1]);
            $quantity = (int) $row[2];
            $originalOrderDate = $row[3];

            // Kumpulkan produk unik dan total quantity
            if (!isset($products[$productName])) {
                $products[$productName] = $quantity;
            } else {
                $products[$productName] += $quantity;
            }

            // Parse tanggal asli untuk mendapatkan hari dan jam
            $originalDate = $this->parseOriginalOrderDate($originalOrderDate);

            // Buat tanggal baru dengan tahun dan bulan target, tapi hari dan jam dari data asli
            $newDate = Carbon::create(
                $targetDate->year,
                $targetDate->month,
                min($originalDate->day, $targetDate->daysInMonth), // Pastikan hari tidak exceed
                $originalDate->hour,
                $originalDate->minute,
                0
            );

            // Simpan data stock out untuk nanti
            $stockOuts[] = [
                'product_name' => $productName,
                'qty' => $quantity,
                'created_at' => $newDate,
            ];
        }

        fclose($file);

        return [
            'products' => $products,
            'stockOuts' => $stockOuts
        ];
    }

    /**
     * Parse tanggal order asli dari CSV
     */
    private function parseOriginalOrderDate(string $orderDate): Carbon
    {
        try {
            return Carbon::createFromFormat('n/j/Y G:i', $orderDate);
        } catch (\Exception $e) {
            // Jika gagal parse, gunakan tanggal random di bulan tersebut
            return Carbon::now()->setDay(rand(1, 28))->setHour(rand(8, 20))->setMinute(rand(0, 59));
        }
    }
}
