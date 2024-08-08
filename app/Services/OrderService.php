<?php
namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderService {
    public static function checkStatusOrder ($id) 
    {
        $order = Order::findOrFail($id);
        $cart = Cart::where('order_id', $id)->where('status_id', '!=', 1)->orderBy('status_id', 'asc')->first();
        $antar = Cart::where('order_id', $id)->where('status_id', 4)->first();
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
        // dd($orderIds);
        foreach ($orderIds as $id) {
            $order = Order::findOrFail($id);
            $cart = Cart::where('order_id', $id)->where('status_id', '!=', 1)->orderBy('status_id', 'asc')->first();
            $antar = Cart::where('order_id', $id)->where('status_id', 4)->first();
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

            if(!$cart) continue;

            if ($antar) {
                $order->update([
                    'status_id' => $antar->status_id
                ]);
                continue;
            }

            $order->update([
                'status_id' => $cart->status_id
            ]);
        }
    }

    public function fixAllStatusOrder() 
    {
        try {
            $user = Auth::user();

            if($user->hasRole('partner')) {
                $order_id = Cart::with('order')->where('partner_price', '!=', 0)->distinct('order_id')->get(['order_id']);
                
                $orderIdsArr = $order_id->pluck('order_id')->toArray();

                $this->checkStatusOrderArr($orderIdsArr);

                return redirect()->back()->with('alert', 'success')->with('message', 'Order berhasil di fix');
            } else {
                $order_id = Cart::with('order')->where('partner_price', 0)->distinct('order_id')->get(['order_id']);
                
                $orderIdsArr = $order_id->pluck('order_id')->toArray();

                $this->checkStatusOrderArr($orderIdsArr);

                return redirect()->back()->with('alert', 'success')->with('message', 'Order berhasil di fix');
            }
        } catch (\Throwable $e) {
            return redirect()->back()->with('alert', 'error')->with('message', 'Something Error');
        }
    }
}

?>