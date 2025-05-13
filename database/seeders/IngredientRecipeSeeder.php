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
        $recipes = [
            // Milk Series
            ['product_id' => 1, 'stock_id' => 1, 'gram_ml' => 10], // Original bobda - Maxcreamer
            ['product_id' => 1, 'stock_id' => 2, 'gram_ml' => 50], // Original bobda - Gula pasir
            ['product_id' => 1, 'stock_id' => 3, 'gram_ml' => 100], // Original bobda - Susu UHT
            ['product_id' => 1, 'stock_id' => 4, 'gram_ml' => 50], // Original bobda - Gula merah
            ['product_id' => 1, 'stock_id' => 5, 'gram_ml' => 50], // Original bobda - Susu kental manis

            ['product_id' => 2, 'stock_id' => 9, 'gram_ml' => 30], // Vanila late - Bubuk Vanila late
            ['product_id' => 2, 'stock_id' => 2, 'gram_ml' => 50], // Vanila late - Gula pasir
            ['product_id' => 2, 'stock_id' => 3, 'gram_ml' => 100], // Vanila late - Susu UHT
            ['product_id' => 2, 'stock_id' => 5, 'gram_ml' => 50], // Vanila late - Susu kental manis

            ['product_id' => 3, 'stock_id' => 1, 'gram_ml' => 10], // Green Tea - Maxcreamer
            ['product_id' => 3, 'stock_id' => 7, 'gram_ml' => 30], // Green Tea - Bubuk Green Tea
            ['product_id' => 3, 'stock_id' => 2, 'gram_ml' => 50], // Green Tea - Gula pasir
            ['product_id' => 3, 'stock_id' => 3, 'gram_ml' => 100], // Green Tea - Susu UHT
            ['product_id' => 3, 'stock_id' => 5, 'gram_ml' => 50], // Green Tea - Susu kental manis

            ['product_id' => 4, 'stock_id' => 1, 'gram_ml' => 10], // Thai Tea - Maxcreamer
            ['product_id' => 4, 'stock_id' => 8, 'gram_ml' => 30], // Thai Tea - Bubuk Thai Tea
            ['product_id' => 4, 'stock_id' => 2, 'gram_ml' => 50], // Thai Tea - Gula pasir
            ['product_id' => 4, 'stock_id' => 3, 'gram_ml' => 100], // Thai Tea - Susu UHT
            ['product_id' => 4, 'stock_id' => 5, 'gram_ml' => 50], // Thai Tea - Susu kental manis

            ['product_id' => 5, 'stock_id' => 10, 'gram_ml' => 30], // Taro - Bubuk Taro
            ['product_id' => 5, 'stock_id' => 2, 'gram_ml' => 50], // Taro - Gula pasir
            ['product_id' => 5, 'stock_id' => 3, 'gram_ml' => 100], // Taro - Susu UHT
            ['product_id' => 5, 'stock_id' => 5, 'gram_ml' => 50], // Taro - Susu kental manis

            ['product_id' => 6, 'stock_id' => 11, 'gram_ml' => 30], // Red Velvet - Bubuk Red Velvet
            ['product_id' => 6, 'stock_id' => 2, 'gram_ml' => 50], // Red Velvet - Gula pasir
            ['product_id' => 6, 'stock_id' => 3, 'gram_ml' => 100], // Red Velvet - Susu UHT
            ['product_id' => 6, 'stock_id' => 5, 'gram_ml' => 50], // Red Velvet - Susu kental manis

            ['product_id' => 7, 'stock_id' => 12, 'gram_ml' => 30], // Avocado - Bubuk Avocado
            ['product_id' => 7, 'stock_id' => 2, 'gram_ml' => 50], // Avocado - Gula pasir
            ['product_id' => 7, 'stock_id' => 3, 'gram_ml' => 100], // Avocado - Susu UHT
            ['product_id' => 7, 'stock_id' => 5, 'gram_ml' => 50], // Avocado - Susu kental manis

            ['product_id' => 8, 'stock_id' => 13, 'gram_ml' => 30], // Chocolate - Bubuk Chocolate
            ['product_id' => 8, 'stock_id' => 2, 'gram_ml' => 50], // Chocolate - Gula pasir
            ['product_id' => 8, 'stock_id' => 3, 'gram_ml' => 100], // Chocolate - Susu UHT
            ['product_id' => 8, 'stock_id' => 5, 'gram_ml' => 50], // Chocolate - Susu kental manis

            ['product_id' => 9, 'stock_id' => 14, 'gram_ml' => 30], // Buble Gum - Bubuk Buble Gum
            ['product_id' => 9, 'stock_id' => 2, 'gram_ml' => 50], // Buble Gum - Gula pasir
            ['product_id' => 9, 'stock_id' => 3, 'gram_ml' => 100], // Buble Gum - Susu UHT
            ['product_id' => 9, 'stock_id' => 5, 'gram_ml' => 50], // Buble Gum - Susu kental manis

            ['product_id' => 10, 'stock_id' => 6, 'gram_ml' => 15], // Ice kopi - Bubuk kopi
            ['product_id' => 10, 'stock_id' => 2, 'gram_ml' => 50], // Ice kopi - Gula pasir
            ['product_id' => 10, 'stock_id' => 3, 'gram_ml' => 100], // Ice kopi - Susu UHT
            ['product_id' => 10, 'stock_id' => 5, 'gram_ml' => 30], // Ice kopi - Susu kental manis

            ['product_id' => 11, 'stock_id' => 6, 'gram_ml' => 15], // Kopi susu - Bubuk kopi
            ['product_id' => 11, 'stock_id' => 2, 'gram_ml' => 50], // Kopi susu - Gula pasir
            ['product_id' => 11, 'stock_id' => 3, 'gram_ml' => 100], // Kopi susu - Susu UHT
            ['product_id' => 11, 'stock_id' => 5, 'gram_ml' => 30], // Kopi susu - Susu kental manis

            ['product_id' => 12, 'stock_id' => 15, 'gram_ml' => 30], // Vanila - Bubuk Vanila
            ['product_id' => 12, 'stock_id' => 2, 'gram_ml' => 50], // Vanila - Gula pasir
            ['product_id' => 12, 'stock_id' => 3, 'gram_ml' => 100], // Vanila - Susu UHT
            ['product_id' => 12, 'stock_id' => 5, 'gram_ml' => 50], // Vanila - Susu kental manis

            // Ice Bland
            ['product_id' => 13, 'stock_id' => 20, 'gram_ml' => 20], // Cappucino - Bubuk Cappucino
            ['product_id' => 13, 'stock_id' => 2, 'gram_ml' => 30], // Cappucino - Gula pasir
            ['product_id' => 13, 'stock_id' => 5, 'gram_ml' => 20], // Cappucino - Susu kental manis

            ['product_id' => 14, 'stock_id' => 13, 'gram_ml' => 20], // Chocolate - Bubuk Chocolate
            ['product_id' => 14, 'stock_id' => 2, 'gram_ml' => 30], // Chocolate - Gula pasir
            ['product_id' => 14, 'stock_id' => 5, 'gram_ml' => 20], // Chocolate - Susu kental manis

            ['product_id' => 15, 'stock_id' => 10, 'gram_ml' => 20], // Taro - Bubuk Taro
            ['product_id' => 15, 'stock_id' => 2, 'gram_ml' => 30], // Taro - Gula pasir
            ['product_id' => 15, 'stock_id' => 5, 'gram_ml' => 20], // Taro - Susu kental manis

            ['product_id' => 16, 'stock_id' => 12, 'gram_ml' => 20], // Avocado - Bubuk Avocado
            ['product_id' => 16, 'stock_id' => 2, 'gram_ml' => 30], // Avocado - Gula pasir
            ['product_id' => 16, 'stock_id' => 5, 'gram_ml' => 20], // Avocado - Susu kental manis

            ['product_id' => 17, 'stock_id' => 16, 'gram_ml' => 20], // Jeruk - Bubuk Jeruk
            ['product_id' => 17, 'stock_id' => 2, 'gram_ml' => 30], // Jeruk - Gula pasir
            ['product_id' => 17, 'stock_id' => 5, 'gram_ml' => 20], // Jeruk - Susu kental manis

            ['product_id' => 18, 'stock_id' => 17, 'gram_ml' => 20], // Strowberry - Bubuk Strowberry
            ['product_id' => 18, 'stock_id' => 2, 'gram_ml' => 30], // Strowberry - Gula pasir
            ['product_id' => 18, 'stock_id' => 5, 'gram_ml' => 20], // Strowberry - Susu kental manis

            ['product_id' => 19, 'stock_id' => 18, 'gram_ml' => 20], // Mangga - Bubuk Mangga
            ['product_id' => 19, 'stock_id' => 2, 'gram_ml' => 30], // Mangga - Gula pasir
            ['product_id' => 19, 'stock_id' => 5, 'gram_ml' => 20], // Mangga - Susu kental manis

            ['product_id' => 20, 'stock_id' => 19, 'gram_ml' => 20], // Extra joss - Extra joss
            ['product_id' => 20, 'stock_id' => 5, 'gram_ml' => 20], // Extra joss - Susu kental manis
        ];

        foreach ($recipes as $recipe) {
            IngredientRecipe::create($recipe);
        }
    }
}
