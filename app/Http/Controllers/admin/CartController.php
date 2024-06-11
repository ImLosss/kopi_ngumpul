<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $cart = Cart::with('product')->findOrFail($id);
            $order = Order::findOrFail($cart->order_id);

            $message = 'Berhasil menghapus ' . $cart->product->name . ' dari keranjang';

            DB::transaction(function () use ($order, $cart) {
                $order->update([
                    'total' => $order->total - $cart->total,
                    'profit' => $order->profit - $cart->profit
                ]);

                $product = Product::findOrFail($cart->product_id);
                $product->update([
                    'jumlah' => $product->jumlah + $cart->jumlah
                ]);

                $cart->delete();
            });

            return redirect()->back()->with('alert', 'success')->with('message', $message);
        } catch (\Throwable $e) {
            return redirect()->back()->with('alert', 'error')->with('message', 'Something Error!');
        }
    }
}
