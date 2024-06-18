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
        $penjualan = [];
        $ratingProduk = [];

        foreach ($products as $product) {
            $salesData = [];
            $date = $startDate->copy();
            for ($i = 0; $i < 7; $i++) {
                $date = $date->addDay();
                $sales = Cart::where('product_id', $product->id)->where('pembayaran', true)->whereDate('created_at', $date)->sum('jumlah');
                $salesData[] = $sales? $sales: null;
            }

            $nonNullValues = array_filter($salesData, function($value) {
                return !is_null($value);
            });
            
            // Cek apakah array yang difilter kosong
            if (!empty($nonNullValues)) {
                $series[] = [
                    'name' => $product->name,
                    'data' => $salesData
                ];
            }

            $totaljual = Cart::where('product_id', $product->id)->where('pembayaran', true)->where('created_at', '>=', $startDate)->sum('jumlah');

            if($totaljual > 0) {
                $tes[] = ['name' => $product->name, 'penjualan' => $totaljual];
                $penjualan[] = $totaljual;
                $ratingProdukName[] = $product->name;
            }
        }

        $maxJual = max($penjualan);

        foreach ($tes as $key => $item) {
            $tes[$key]['rating'] = $this->calculateRating($item['penjualan'], $maxJual);
        }

        return $tes;
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDay(); // Tambahkan hari ke tanggal mulai
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

    private function calculateRating($total_penjualan, $maxPenjualan)
    {
        // Jika maxPenjualan adalah 0, atur rating ke 0 untuk menghindari pembagian oleh nol
        if ($maxPenjualan == 0) {
            return 0;
        }
        
        // Hitung persentase
        return ($total_penjualan / $maxPenjualan) * 100;
    }
}
