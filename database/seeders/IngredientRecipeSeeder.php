<?php

namespace Database\Seeders;

use App\Models\IngredientRecipe;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IngredientRecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IngredientRecipe::create([
            'product_id' => 1,
            'stock_id' => 1,
            'gram_ml' => 200
        ]);
    }
}
