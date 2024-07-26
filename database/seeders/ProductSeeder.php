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
            'name' => 'Aren Coffee',
            'jumlah' => 20,
            'modal' => 10000,
            'harga' => 17000,
            'category_id' => 1
        ]);

        Product::create([
            'name' => 'Ujun Pandan Coffee',
            'jumlah' => 223,
            'modal' => 8000,
            'harga' => 17000,
            'category_id' => 1
        ]);

        Product::create([
            'name' => 'Milk Coffee Ice',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 17000,
            'category_id' => 1
        ]);

        Product::create([
            'name' => 'Milk Coffee Hot',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 13000,
            'category_id' => 1
        ]);

        Product::create([
            'name' => 'Americano Ice',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 15000,
            'category_id' => 1
        ]);

        Product::create([
            'name' => 'Americano Hot',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 12000,
            'category_id' => 1
        ]);

        Product::create([
            'name' => 'Espresso',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 12000,
            'category_id' => 1
        ]);

        Product::create([
            'name' => 'Black Coffee',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 12000,
            'category_id' => 1
        ]);

        Product::create([
            'name' => 'Tubruk',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 12000,
            'category_id' => 1
        ]);

        Product::create([
            'name' => 'Dark Chocolate Hot',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 13000,
            'category_id' => 2
        ]);

        Product::create([
            'name' => 'Dark Chocolate Ice',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 15000,
            'category_id' => 2
        ]);

        Product::create([
            'name' => 'Red Velvet Hot',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 12000,
            'category_id' => 2
        ]);

        Product::create([
            'name' => 'Red Velvet Ice',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 17000,
            'category_id' => 2
        ]);

        Product::create([
            'name' => 'Milk Shake Ice',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 10000,
            'category_id' => 2
        ]);

        Product::create([
            'name' => 'Milk Shake Hot',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 15000,
            'category_id' => 2
        ]);

        Product::create([
            'name' => 'Taro Hot',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 12000,
            'category_id' => 2
        ]);

        Product::create([
            'name' => 'Taro Ice',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 15000,
            'category_id' => 2
        ]);

        Product::create([
            'name' => 'Extrajoss Milk',
            'jumlah' => 25,
            'modal' => 7000,
            'harga' => 10000,
            'category_id' => 2
        ]);

        Product::create([
            'name' => 'Air Gelas',
            'jumlah' => 25,
            'modal' => 500,
            'harga' => 1000,
            'category_id' => 2
        ]);

        Product::create([
            'name' => 'Thai Tea',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 15000,
            'category_id' => 3
        ]);

        Product::create([
            'name' => 'Green Tea',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 15000,
            'category_id' => 3
        ]);

        Product::create([
            'name' => 'Lemon Tea Hot',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 12000,
            'category_id' => 3
        ]);

        Product::create([
            'name' => 'Lemon Tea Ice',
            'jumlah' => 25,
            'modal' => 9000,
            'harga' => 15000,
            'category_id' => 3
        ]);

        Product::create([
            'name' => 'Strawberry Tea Ice',
            'jumlah' => 25,
            'modal' => 9000,
            'harga' => 15000,
            'category_id' => 3
        ]);

        Product::create([
            'name' => 'Strawberry Tea Hot',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 12000,
            'category_id' => 3
        ]);

        Product::create([
            'name' => 'Strawberry Milk',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 15000,
            'category_id' => 3
        ]);

        Product::create([
            'name' => 'Strawberry Milk Tea',
            'jumlah' => 25,
            'modal' => 10000,
            'harga' => 17000,
            'category_id' => 3
        ]);

        Product::create([
            'name' => 'Strawberry Lemon Tea',
            'jumlah' => 25,
            'modal' => 10000,
            'harga' => 17000,
            'category_id' => 3
        ]);

        Product::create([
            'name' => 'Original Tea Hot',
            'jumlah' => 25,
            'modal' => 7000,
            'harga' => 1000,
            'category_id' => 3
        ]);

        Product::create([
            'name' => 'Original Tea Ice',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 12000,
            'category_id' => 3
        ]);

        Product::create([
            'name' => 'Milk Tea Hot',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 12000,
            'category_id' => 3
        ]);

        Product::create([
            'name' => 'Milk Tea Ice',
            'jumlah' => 25,
            'modal' => 9000,
            'harga' => 15000,
            'category_id' => 3
        ]);

        Product::create([
            'name' => 'Pitcher Tea',
            'jumlah' => 25,
            'modal' => 15000,
            'harga' => 25000,
            'category_id' => 3
        ]);

        Product::create([
            'name' => 'Leci Tea',
            'jumlah' => 25,
            'modal' => 9000,
            'harga' => 15000,
            'category_id' => 3
        ]);

        Product::create([
            'name' => 'Vanilla Tea',
            'jumlah' => 25,
            'modal' => 9000,
            'harga' => 15000,
            'category_id' => 3
        ]);

        Product::create([
            'name' => 'Roti Panggang',
            'jumlah' => 25,
            'modal' => 7000,
            'harga' => 14000,
            'category_id' => 4
        ]);

        Product::create([
            'name' => 'Kentang Goreng',
            'jumlah' => 25,
            'modal' => 7000,
            'harga' => 14000,
            'category_id' => 4
        ]);

        Product::create([
            'name' => 'Mie Rebus',
            'jumlah' => 25,
            'modal' => 4000,
            'harga' => 10000,
            'category_id' => 4
        ]);

        Product::create([
            'name' => 'Mie Rebus Double',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 15000,
            'category_id' => 4
        ]);

        Product::create([
            'name' => 'Mie Goreng',
            'jumlah' => 25,
            'modal' => 4000,
            'harga' => 10000,
            'category_id' => 4
        ]);

        Product::create([
            'name' => 'Mie Goreng Double',
            'jumlah' => 25,
            'modal' => 8000,
            'harga' => 15000,
            'category_id' => 4
        ]);
    }
}
