<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Nasi Goreng',
            'jumlah' => 20,
            'modal' => 15000,
            'harga' => 25000,
            'category_id' => 1
        ]);

        Product::create([
            'name' => 'Milo',
            'jumlah' => 223,
            'modal' => 8000,
            'harga' => 18000,
            'category_id' => 2
        ]);

        Product::create([
            'name' => 'Indomie ayam geprek',
            'jumlah' => 25,
            'modal' => 12000,
            'harga' => 21000,
            'category_id' => 2
        ]);
    }
}
