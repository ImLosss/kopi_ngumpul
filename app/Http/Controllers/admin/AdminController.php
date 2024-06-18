<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        $startDate = Carbon::now()->subWeek();
        $datesArray = [];
        $series = [];

        foreach ($products as $product) {
            $salesData = [];
            $date = $startDate->copy();
            for ($i = 0; $i < 7; $i++) {
                $date = $date->addDay();
                $sales = Cart::where('product_id', $product->id)->whereDate('created_at', $date)->sum('jumlah');
                $salesData[] = $sales? $sales: null;
            }
            $series[] = [
                'name' => $product->name,
                'data' => $salesData
            ];
        }

        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->addDay(); // Tambahkan hari ke tanggal mulai
            $formattedDate = $date->format('d M'); // Format tanggal menjadi '12 Feb'
            $datesArray[] = $formattedDate;
        }

        $data['datesArr'] = $datesArray;
        $data['series'] = $series;
        $data['cekstok'] = Product::where('jumlah', 0)->get();
        $data['habis'] = Product::where('jumlah', '<=', 5)->get();

        // dd($data);
        // return($series);
        return view('admin.dashboard', $data);
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
}
