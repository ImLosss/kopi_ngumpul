<?php

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Discount::create([
            'name' => 'Diskon Mahasiswa',
            'percent' => 20,
            'product_id' => 1
        ]);

        Discount::create([
            'name' => 'Diskon Harian',
            'percent' => 15,
            'product_id' => 1
        ]);
    }
}
