<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CashierRequest;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
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
        $order = Order::where('status_id', 1)->first();
        if(!$order) {
            $order = Order::create([
                'kasir' => Auth::user()->name,
                'status_id' => 1,
            ]);
        }

        $data['products'] = Product::get();
        $data['order'] = Order::with(['carts.product', 'carts.discount'])->where('status_id', 1)->first();
        $data['disc'] = Cart::where('order_id', $data['order']->id)->get()->sum('total_diskon');
        $data['tables'] = Table::get();

        // dd($data);
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
            DB::transaction(function () use ($request) {
                $order = Order::where('status_id', 1)->first();

                if(!$order) {
                    $order = Order::create([
                        'kasir' => Auth::user()->name,
                        'status_id' => 1,
                    ]);
                }

                if($request->has('diskon_id')) {
                    $diskon = Discount::findOrFail($request->diskon_id);
                    $diskon = $request->total * ($diskon->percent / 100);
                } else $diskon = 0;

                Cart::create([
                    'product_id' => $request->menu,
                    'order_id' => $order->id,
                    'diskon_id' => $request->diskon_id,
                    'jumlah' => $request->jumlah,
                    'harga' => $request->harga,
                    'total_diskon' => $diskon,
                    'total' => $request->total - $diskon,
                    'status_id' => 1
                ]);

                $product = Product::findOrFail($request->menu);
                $product->update([
                    'jumlah' => $product->jumlah - $request->jumlah
                ]);

                $total = Cart::where('order_id', $order->id)->get()->sum('total');

                $order->update([
                    'total' => $total
                ]);

            });
            
            return redirect()->back()->with('alert', 'success')->with('message', 'Berhasil menambahkan cart');
        } catch (\Throwable $e) {
            return redirect()->back()->with('alert', 'error')->with('message', 'Something Error!');
        }
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
        //
    }

    public function getDetail($id)
    {
        $product = Product::with('discount')->find($id);

        $diskon = $product->discount;

        $diskonData = [];
        if ($product->discount->isNotEmpty()) {
            foreach ($product->discount as $diskon) {
                $diskonData[] = [
                    'id' => $diskon->id,
                    'name' => $diskon->name,
                    'percent' => $diskon->percent
                ];
            }
        }

        return response()->json([
            'harga' => $product->harga,
            'stock' => $product->jumlah,
            'diskon' => $diskonData
        ]);
    }
}
