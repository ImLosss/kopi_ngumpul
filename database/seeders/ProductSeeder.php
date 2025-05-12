<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::insert([
            // Milk Series
            ['name' => 'Original bobda', 'harga' => 10000, 'category_id' => 1],
            ['name' => 'Vanila late', 'harga' => 10000, 'category_id' => 1],
            ['name' => 'Green Tea', 'harga' => 10000, 'category_id' => 1],
            ['name' => 'Thai Tea', 'harga' => 10000, 'category_id' => 1],
            ['name' => 'Taro', 'harga' => 10000, 'category_id' => 1],
            ['name' => 'Red Velvet', 'harga' => 10000, 'category_id' => 1],
            ['name' => 'Avocado', 'harga' => 10000, 'category_id' => 1],
            ['name' => 'Chocolate', 'harga' => 10000, 'category_id' => 1],
            ['name' => 'Buble Gum', 'harga' => 10000, 'category_id' => 1],
            ['name' => 'Ice kopi', 'harga' => 10000, 'category_id' => 1],
            ['name' => 'Kopi susu', 'harga' => 10000, 'category_id' => 1],

            // Ice Bland
            ['name' => 'Cappucino', 'harga' => 5000, 'category_id' => 2],
            ['name' => 'Chocolate', 'harga' => 5000, 'category_id' => 2],
            ['name' => 'Green Tea', 'harga' => 5000, 'category_id' => 2],
            ['name' => 'Thai Tea', 'harga' => 5000, 'category_id' => 2],
            ['name' => 'Taro', 'harga' => 5000, 'category_id' => 2],
            ['name' => 'Avocado', 'harga' => 5000, 'category_id' => 2],
            ['name' => 'Jeruk', 'harga' => 5000, 'category_id' => 2],
            ['name' => 'Strowberry', 'harga' => 5000, 'category_id' => 2],
            ['name' => 'Melon', 'harga' => 5000, 'category_id' => 2],
            ['name' => 'Mangga', 'harga' => 5000, 'category_id' => 2],
            ['name' => 'Extra joss', 'harga' => 5000, 'category_id' => 2],
            
        ]);
    }
}
