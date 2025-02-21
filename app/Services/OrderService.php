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

    public static function checkPaymentOrder ($id) 
    {
        $order = Order::findOrFail($id);
        $payment = Cart::where('order_id', $id)->where('pembayaran', false)->first();
        
        // dd($payment);

        if($payment)
            $order->update([
                'pembayaran' => $payment->pembayaran
            ]);
        else {
            $order->update([
                'pembayaran' => true
            ]);
        }
    }
}

?>