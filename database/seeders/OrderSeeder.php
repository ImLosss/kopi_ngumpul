<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Loop untuk membuat 50 order
        for ($i = 1; $i <= 50; $i++) {

            // Atur tanggal order berdasarkan indeks
            if ($i < 10) {
                $createdAt = Carbon::now();
            } elseif ($i < 20) {
                $createdAt = Carbon::now()->subMonths(1);
            } elseif ($i < 30) {
                $createdAt = Carbon::now()->subMonths(2);
            } elseif ($i < 40) {
                $createdAt = Carbon::now()->subMonths(3);
            } else {
                $createdAt = Carbon::now()->subMonths(4);
            }

            // Buat order dengan data contoh
            $order = Order::create([
                'user_id'       => 2,             // contoh user id
                'total'         => 0,                      // total akan dihitung dari cart, bisa diupdate setelahnya
                'status'        => 'selesai',
                'pembayaran'    => true,
                'kasir_id'      => 2,
                'customer_name' => 'Customer ' . $i,
                'created_at'    => $createdAt,
                'updated_at'    => $createdAt,
            ]);

            // Buat 1 sampai 3 data cart untuk order ini
            $cartCount = rand(5, 30);
            $orderTotal = 0; // untuk menghitung total order dari cart

            for ($j = 1; $j <= $cartCount; $j++) {
                $jumlah = rand(1, 5);
                $product = Product::findOrFail(rand(1, 22));
                $harga = $product->harga;
                $total = $jumlah * $harga;
                $orderTotal += $total;

                if($product->id == 3) continue;

                Cart::create([
                    'menu'              => $product->name,
                    'product_id'        => $product->id,
                    'order_id'          => $order->id,
                    'jumlah'            => $jumlah,
                    'harga'             => $harga,
                    'total'             => $total,
                    'pembayaran'        => $order->pembayaran,
                    'payment_method'    => $order->pembayaran ? 'cash' : null,
                    'note'              => 'Note ' . $j,
                    'update_payment_by' => $order->pembayaran ? 'kasir' : null,
                    'created_at'        => $createdAt,
                    'updated_at'        => $createdAt,
                ]);
            }

            // Update total order berdasarkan jumlah total dari cart
            $order->update(['total' => $orderTotal]);
        }
    }
}
