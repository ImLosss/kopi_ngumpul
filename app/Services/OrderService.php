<?php
namespace App\Services;

use App\Models\Cart;
use App\Models\Order;

class OrderService {
    public static function checkStatusOrder ($id) 
    {
        $order = Order::findOrFail($id);
        $cart = Cart::where('order_id', $id)->where('status_id', '!=', 1)->orderBy('status_id', 'asc')->first();
        $antar = Cart::where('order_id', $id)->where('status_id', 3)->first();
        $payment = Cart::where('order_id', $id)->where('pembayaran', false)->first();
        
        if($payment)
            $order->update([
                'pembayaran' => $payment->pembayaran
            ]);
        else {
            $order->update([
                'pembayaran' => true
            ]);
        }

        if(!$cart) return;

        if ($antar) {
            $order->update([
                'status_id' => $antar->status_id
            ]);
            return;
        }

        $order->update([
            'status_id' => $cart->status_id
        ]);
    }

    public static function checkStatusOrderArr ($orderIds) 
    {
        foreach ($orderIds as $id) {
            $order = Order::findOrFail($id);
            $cart = Cart::where('order_id', $id)->where('status_id', '!=', 1)->orderBy('status_id', 'asc')->first();
            $antar = Cart::where('order_id', $id)->where('status_id', 3)->first();
            $payment = Cart::where('order_id', $id)->where('pembayaran', false)->first();
            
            if($payment)
                $order->update([
                    'pembayaran' => $payment->pembayaran
                ]);
            else {
                $order->update([
                    'pembayaran' => true
                ]);
            }

            if(!$cart) return;

            if ($antar) {
                $order->update([
                    'status_id' => $antar->status_id
                ]);
                return;
            }

            $order->update([
                'status_id' => $cart->status_id
            ]);
        }
    }
}

?>