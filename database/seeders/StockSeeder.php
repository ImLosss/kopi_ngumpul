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

        // Mapping: May->Oktober, April->September, March->Agustus, February->Juli, January->Juni
        $csvToCurrentMonthMapping = [
            'May' => 0,      // Bulan sekarang (Oktober)
            'April' => 1,    // 1 bulan yang lalu (September)
            'March' => 2,    // 2 bulan yang lalu (Agustus)
            'February' => 3, // 3 bulan yang lalu (Juli)
            'January' => 4,  // 4 bulan yang lalu (Juni)
        ];

        foreach ($csvToCurrentMonthMapping as $csvMonth => $monthsBack) {
            $fileName = "Sales_{$csvMonth}_2019.csv";
            $filePath = database_path("seeders/{$fileName}");

            // Hitung tanggal target (bulan sekarang mundur sesuai mapping)
            $targetDate = Carbon::now()->subMonths($monthsBack);

            $csvFiles[] = [
                'file' => $filePath,
                'target_date' => $targetDate,
                'target_month' => $targetDate->format('F Y'),
                'csv_month' => $csvMonth
            ];
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
