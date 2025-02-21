<?php
namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class OrderService {
    public static function addCart ($id_product, $jumlah) 
    {
        $product = Product::findOrFail($id_product);

        foreach ($product->stocks as $item) {
            $item->update(['jumlah_gr' => $item->jumlah_gr - ($item->pivot->gram_ml * $jumlah)]);
        }

        return;
    }

    public static function deleteCart ($id_product, $jumlah) 
    {
        $product = Product::findOrFail($id_product);

        foreach ($product->stocks as $item) {
            $item->update(['jumlah_gr' => $item->jumlah_gr + ($item->pivot->gram_ml * $jumlah)]);
        }

        return;
    }
}

?>