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
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:cashierAccess|cashierPartnerAccess');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        if($user->can('cashierAccess')){
            $order = Order::where('status_id', 1)->where('partner', false)->first();
            if(!$order) {
                $order = Order::create([
                    'kasir' => Auth::user()->name,
                    'status_id' => 1,
                ]);
            }

            $data['categories'] = Category::with('product')->get();
            $data['order'] = Order::with(['carts.product', 'carts.discount'])->where('status_id', 1)->where('partner', false)->first();
            $data['disc'] = Cart::where('order_id', $data['order']->id)->get()->sum('total_diskon');
            $data['tables'] = Table::get();

            // dd($data);
            return view('admin.cashier.index', $data);
        } else {
            // return 'tess';
            $order = Order::where('status_id', 1)->where('partner', true)->first();
            if(!$order) {
                $order = Order::create([
                    'kasir' => Auth::user()->name,
                    'status_id' => 1,
                    'partner' => true
                ]);
            }

            $data['products'] = PartnerProduct::with('product')->where('user_id', $user->id)->get();
            $data['order'] = Order::with(['carts.product', 'carts.discount'])->where('status_id', 1)->where('partner', true)->first();
            $data['disc'] = Cart::where('order_id', $data['order']->id)->get()->sum('total_diskon');
            $data['tables'] = Table::get();

            // dd($data);
            return view('admin.cashier.partner.index', $data);
        }
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
            if($user->can('cashierAccess')) {
                DB::transaction(function () use ($request) {
                    $order = Order::where('status_id', 1)->where('partner', false)->first();
                    $menu = Product::findOrFail($request->menu);

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
                        'menu' => $menu->name,
                        'product_id' => $request->menu,
                        'order_id' => $order->id,
                        'diskon_id' => $request->diskon_id,
                        'jumlah' => $request->jumlah,
                        'harga' => $request->harga,
                        'total_diskon' => $diskon,
                        'total' => $request->total - $diskon,
                        'profit' => ($menu->harga - $menu->modal) * $request->jumlah - $diskon,
                        'status_id' => 1,
                        'note' => $request->note
                    ]);

                    $product = Product::findOrFail($request->menu);
                    $product->update([
                        'jumlah' => $product->jumlah - $request->jumlah
                    ]);

                    $total = Cart::where('order_id', $order->id)->get()->sum('total');
                    $profit = Cart::where('order_id', $order->id)->get()->sum('profit');

                    $order->update([
                        'total' => $total,
                        'profit' => $profit
                    ]);

                });
            } else if($user->can('cashierPartnerAccess')) {
                // dd($request);
                DB::transaction(function () use ($request) {
                    $order = Order::where('status_id', 1)->where('partner', true)->first();
                    $menu = Product::findOrFail($request->menu);

                    if(!$order) {
                        $order = Order::create([
                            'kasir' => Auth::user()->name,
                            'status_id' => 1,
                            'partner' => true
                        ]);
                    }

                    Cart::create([
                        'menu' => $menu->name,
                        'product_id' => $request->menu,
                        'order_id' => $order->id,
                        'jumlah' => $request->jumlah,
                        'harga' => $request->real_price,
                        'total_diskon' => 0,
                        'total' => $request->real_price * $request->jumlah,
                        'profit' => ($menu->harga - $menu->modal) * $request->jumlah,
                        'partner_price' => $request->harga,
                        'partner_profit' => ($request->harga - $request->real_price) * $request->jumlah,
                        'partner_total' => $request->total,
                        'status_id' => 1,
                        'note' => $request->note
                    ]);

                    $product = Product::findOrFail($request->menu);
                    $product->update([
                        'jumlah' => $product->jumlah - $request->jumlah
                    ]);

                    $total = Cart::where('order_id', $order->id)->get()->sum('total');
                    $profit = Cart::where('order_id', $order->id)->get()->sum('profit');
                    $partner_profit = Cart::where('order_id', $order->id)->get()->sum('partner_profit');
                    $partner_price = Cart::where('order_id', $order->id)->get()->sum('partner_price');
                    $partner_total = Cart::where('order_id', $order->id)->get()->sum('partner_total');

                    $order->update([
                        'total' => $total,
                        'profit' => $profit,
                        'partner_profit' => $partner_profit,
                        'partner_total' => $partner_total,
                    ]);

                });
            }
            
            return redirect()->back()->with('alert', 'success')->with('message', 'Berhasil menambahkan cart');
        } catch (\Throwable $e) {
            // dd($e);
            return redirect()->back()->with('alert', 'error')->with('message', 'Something Error!');
        }
    }

    public function getDetail($id)
    {
        $user = Auth::user();
        if(!$user->hasRole('partner')) {
            $product = Product::with('discount')->find($id);

            $diskon = $product->discount;

            $diskonData = [];
            if ($product->discount->isNotEmpty()) {
                foreach ($product->discount as $diskon) {
                    if($diskon->status == 'Aktif') {
                        $diskonData[] = [
                            'id' => $diskon->id,
                            'name' => $diskon->name,
                            'percent' => $diskon->percent
                        ];
                    }
                }
            }

            return response()->json([
                'harga' => $product->harga,
                'stock' => $product->jumlah,
                'diskon' => $diskonData
            ]);
        } else {
            $product = PartnerProduct::with('product')->where('product_id', $id)->where('user_id', Auth::user()->id)->first();


            // dd($product);
            return response()->json([
                'harga' => $product->up_price + $product->product->harga,
                'real_price' => $product->product->harga,
                'stock' => $product->product->jumlah,
            ]);
        }

    }
}
