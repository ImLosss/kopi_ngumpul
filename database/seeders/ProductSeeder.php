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
            'Category_id' => 1
        ]);
    }
}
