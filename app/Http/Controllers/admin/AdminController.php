<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Stock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        return view('admin.dashboard');
    }

    public function updateRatingChart()
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
    
    private function formatNumber($num) {
        $rounded = round($num, 1);
        // Jika hasil pembulatan adalah bilangan bulat, kembalikan sebagai integer
        return ($rounded == intval($rounded)) ? intval($rounded) : $rounded;
    }

    public function filterRating(Request $request) 
    {

        $products = Product::where('category_id', $request->option)->get();
        $startDate = Carbon::now()->subWeek();
        $penjualan = [];
        $ratingChart = [];
        $ratingJual = [];

        foreach ($products as $product) {
            $totaljual = Cart::where('product_id', $product->id)->where('pembayaran', true)->where('created_at', '>=', $startDate)->sum('jumlah');

            if($totaljual > 0) {
                $ratingChart[] = ['name' => $product->name, 'penjualan' => $totaljual];
                $penjualan[] = $totaljual;
            }
        }
        
        rsort($penjualan, SORT_NUMERIC);

        try {
            $maxJual = max($penjualan);
        } catch(\Throwable $e) {

        }

        foreach ($ratingChart as $key => $item) {
            $ratingChart[$key]['rating'] = $this->calculateRating($item['penjualan'], $maxJual);
        }

        usort($ratingChart, function($a, $b) {
            return $b['rating'] <=> $a['rating'];
        });
        
        foreach ($ratingChart as $item) {
            $data['ratingChart']['name'][] = $item['name'];
            $ratingJual[] = $item['rating'];
        }

        // memvbatasi dan menghapus decimal jika bilangan bulat
        $ratingJual = array_map(function($num) {
            $num = $this->formatNumber($num);
            return $num;
        }, $ratingJual);

        $data['ratingChart']['series'][] = ['name' => 'Rating', 'data' => $ratingJual];
        $data['ratingChart']['penjualan'] = $penjualan;

        if(empty($data['ratingChart']['name'])) {
            $data['ratingChart']['name'] = [];
        }


        // dd($data);
        return [
            'series' => $data['ratingChart']['series'],
            'penjualan' => $penjualan,
            'name' => $data['ratingChart']['name']
        ];
        // return($series);
        return view('admin.dashboard', $data);
    }
}
