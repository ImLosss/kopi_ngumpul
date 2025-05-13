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
        $bahanBaku = [
            'Maxcreamer', // 1
            'Gula pasir', // 2
            'Susu UHT', // 3
            'Gula merah', // 4
            'Susu kental manis', // 5
            'Bubuk kopi', // 6
            'Bubuk Green Tea', // 7
            'Bubuk Thai Tea', // 8
            'Bubuk Vanila late', // 9
            'Bubuk Taro', // 10
            'Bubuk Red Velvet', // 11
            'Bubuk Avocado', // 12
            'Bubuk Chocolate', // 13
            'Bubuk Buble Gum', // 14
            'Bubuk Vanila', // 15
            'Bubuk Jeruk', // 16
            'Bubuk Strowberry', // 17
            'Bubuk Mangga', // 18
            'Extra joss', // 19
            'Bubuk Cappucino' // 20
        ];

        foreach ($bahanBaku as $item) {
            Stock::create([
                'name' => $item,
                'jumlah_gr' => 1000
            ]);
        }
    }
}
