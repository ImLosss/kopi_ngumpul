<?php

namespace Database\Seeders;

use App\Models\Stock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Stock::create([
            'name' => 'Aren Coffee',
            'jumlah_gr' => 1500
        ]);

        Stock::create([
            'name' => 'asdasd',
            'jumlah_gr' => 2555
        ]);
    }
}
