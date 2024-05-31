<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

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
        $data = Product::get();

        // dd($data);
        return view('admin.cashier.index', compact('data'));
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
