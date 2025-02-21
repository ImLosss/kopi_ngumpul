<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CashierRequest;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\PartnerProduct;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:cashierAccess');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        Order::firstOrCreate(
            ['status' => 'cart']
        );

        $data['categories'] = Category::with('product.stocks')->get();
        $data['order'] = Order::with(['carts.product'])->where('status', 'cart')->first();

        return view('admin.cashier.index', $data);
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
    public function store(CashierRequest $request)
    {
        // dd($request);
        try {
            $user = Auth::user();
            DB::transaction(function () use ($request) {
                $menu = Product::findOrFail($request->menu);

                $order = Order::firstOrCreate(
                    ['status' => 'cart']
                );

                $cart = Cart::where('product_id', $request->menu)->where('order_id', $order->id)->first();

                if(!$cart) {
                    Cart::create([
                        'menu' => $menu->name,
                        'product_id' => $request->menu,
                        'order_id' => $order->id,
                        'jumlah' => $request->jumlah,
                        'harga' => $request->harga,
                        'total' => $request->total,
                        'note' => $request->note
                    ]);
                } else {
                    $cart->update([
                        'jumlah' => $cart->jumlah + $request->jumlah,
                        'harga' => $request->harga,
                        'total' => $cart->total + $request->total,
                        'note' => $request->note
                    ]);
                }

                OrderService::addCart($request->menu, $request->jumlah);

                $total = Cart::where('order_id', $order->id)->get()->sum('total');

                $order->update([
                    'total' => $total
                ]);

            });
            
            return redirect()->back()->with('alert', 'success')->with('message', 'Berhasil menambahkan cart');
        } catch (\Throwable $e) {
            dd($e);
            return redirect()->back()->with('alert', 'error')->with('message', 'Something Error!');
        }
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
                ]);

                $product = Product::findOrFail($cart->product_id);
                $product->update([
                    'jumlah' => $product->jumlah + $cart->jumlah
                ]);

                OrderService::deleteCart($cart->product_id, $cart->jumlah);

                $cart->forceDelete();
            });

            return redirect()->back()->with('alert', 'success')->with('message', $message);
        } catch (\Throwable $e) {
            return redirect()->back()->with('alert', 'error')->with('message', 'Something Error!');
        }
    }

    public function getDetail($id)
    {
        $user = Auth::user();
        $product = Product::findOrFail($id);

        return response()->json([
            'harga' => $product->harga,
            'stock' => $product->stock
        ]);
    }
}
