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
            'Maxcreamer',
            'Gula pasir',
            'Susu UHT',
            'Gula merah',
            'Susu kental manis',
            'Bubuk kopi',
            'Bubuk Green Tea',
            'Bubuk Thai Tea',
            'Bubuk Vanila late',
            'Bubuk Taro',
            'Bubuk Red Velvet',
            'Bubuk Avocado',
            'Bubuk Chocolate',
            'Bubuk Buble Gum',
            'Bubuk Vanila late',
            'Bubuk Jeruk', // Added missing stock
            'Bubuk Strowberry', // Added missing stock
            'Bubuk Melon', // Added missing stock
            'Bubuk Mangga', // Added missing stock
            'Extra joss' // Added missing stock
        ];

        foreach ($bahanBaku as $item) {
            Stock::create([
                'name' => $item,
                'jumlah_gr' => 1000
            ]);
        }
    }
}
