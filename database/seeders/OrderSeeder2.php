<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data penjualan per produk per bulan (product_id => [bulan => total])
        // 4: Januari, 3: Februari, 2: Maret, 1: April, 0: Mei
        $salesData = [
            // Green Tea Milk Series (product_id 1)
            1 => [
                4 => 86,   // Januari
                3 => 154,  // Februari
                2 => 107,  // Maret
                1 => 98,   // April
                0 => 109,  // Mei
            ],
            // Green Tea Milk Series (product_id 2)
            2 => [
                4 => 44,
                3 => 47,
                2 => 54,
                1 => 48,
                0 => 59,
            ],
            // Green Tea Milk Series (product_id 3)
            3 => [
                4 => 82,
                3 => 109,
                2 => 98,
                1 => 93,
                0 => 105,
            ],
            // Thai Tea Milk Series (product_id 4)
            4 => [
                4 => 23,
                3 => 48,
                2 => 39,
                1 => 37,
                0 => 42,
            ],
            // Taro Milk Series (product_id 5)
            5 => [
                4 => 12,
                3 => 6,
                2 => 14,
                1 => 11,
                0 => 8,
            ],
            // Taro Milk Series (product_id 6)
            6 => [
                4 => 14,
                3 => 19,
                2 => 17,
                1 => 19,
                0 => 21,
            ],
            // Taro Milk Series (product_id 7)
            7 => [
                4 => 18,
                3 => 42,
                2 => 31,
                1 => 27,
                0 => 31,
            ],
            // Taro Milk Series (product_id 8)
            8 => [
                4 => 62,
                3 => 90,
                2 => 98,
                1 => 97,
                0 => 99,
            ],
            // Taro Milk Series (product_id 9)
            9 => [
                4 => 23,
                3 => 10,
                2 => 14,
                1 => 10,
                0 => 9,
            ],
            // Taro Milk Series (product_id 10)
            10 => [
                4 => 33,
                3 => 44,
                2 => 36,
                1 => 39,
                0 => 41,
            ],
            // Taro Milk Series (product_id 11)
            11 => [
                4 => 17,
                3 => 16,
                2 => 21,
                1 => 19,
                0 => 17,
            ],
            // Taro Milk Series (product_id 12)
            12 => [
                4 => 30,
                3 => 40,
                2 => 38,
                1 => 39,
                0 => 41,
            ],
            // Ice Cappucino (product_id 13)
            13 => [
                4 => 193, // Januari
                3 => 196, // Februari
                2 => 238, // Maret
                1 => 239, // April
                0 => 248, // Mei
            ],
            // Ice Chocolate (product_id 14)
            14 => [
                4 => 60,
                3 => 63,
                2 => 79,
                1 => 79,
                0 => 87,
            ],
            // Ice Taro (product_id 15)
            15 => [
                4 => 22,
                3 => 33,
                2 => 37,
                1 => 36,
                0 => 40,
            ],
            // Ice Avocado (product_id 16)
            16 => [
                4 => 35,
                3 => 30,
                2 => 35,
                1 => 40,
                0 => 48,
            ],
            // Ice Jeruk (product_id 17)
            17 => [
                4 => 22,
                3 => 45,
                2 => 21,
                1 => 28,
                0 => 32,
            ],
            // Ice Strawberry (product_id 18)
            18 => [
                4 => 27,
                3 => 39,
                2 => 27,
                1 => 32,
                0 => 36,
            ],
            // Ice Mangga (product_id 19)
            19 => [
                4 => 21,
                3 => 31,
                2 => 21,
                1 => 26,
                0 => 22,
            ],
            // ExtraJoss (product_id 20)
            20 => [
                4 => 54,
                3 => 98,
                2 => 54,
                1 => 65,
                0 => 32,
            ],
        ];

        foreach ($salesData as $productId => $sales) {
            foreach ($sales as $monthAgo => $totalSales) {
                $createdAt = Carbon::now()->subMonths($monthAgo);

                // Bagi rata ke beberapa order (misal 10 order per bulan)
                $orderCount = 10;
                $salesPerOrder = intdiv($totalSales, $orderCount);
                $remainder = $totalSales % $orderCount;

                for ($i = 1; $i <= $orderCount; $i++) {
                    $order = Order::create([
                        'user_id'       => 2,
                        'total'         => 0,
                        'status'        => 'selesai',
                        'pembayaran'    => true,
                        'kasir_id'      => 2,
                        'customer_name' => 'Customer ' . $productId . ' ' . $monthAgo . '-' . $i,
                        'created_at'    => $createdAt,
                        'updated_at'    => $createdAt,
                    ]);

                    $product = Product::find($productId);

                    // Tambahkan sisa ke order pertama
                    $jumlah = $salesPerOrder + ($i == 1 ? $remainder : 0);
                    $harga = $product ? $product->harga : 0;
                    $total = $jumlah * $harga;

                    Cart::create([
                        'menu'              => $product->name,
                        'product_id'        => $productId,
                        'order_id'          => $order->id,
                        'jumlah'            => $jumlah,
                        'harga'             => $harga,
                        'total'             => $total,
                        'pembayaran'        => $order->pembayaran,
                        'payment_method'    => $order->pembayaran ? 'cash' : null,
                        'note'              => null,
                        'update_payment_by' => $order->pembayaran ? 'kasir' : null,
                        'created_at'        => $createdAt,
                        'updated_at'        => $createdAt,
                    ]);

                    $order->update(['total' => $total]);
                }
            }
        }
    }
}
