<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Category;
use App\Models\IngredientTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    { 
        $oneMonthAgo = Carbon::now()->subMonth();
        $oneDayAgo = Carbon::now();
        $data['pemasukanHariIni'] = Order::where('pembayaran', true)->whereDate('created_at', Carbon::today())->sum('total');
        $data['totalPemasukan'] = Order::where('pembayaran', true)->sum('total');
        $data['habis'] = Stock::where('jumlah_gr', '<=', 200)->get();
        $data['sedikit'] = Stock::where('jumlah_gr', '<=', 500)->get();
        $data['keuntunganHariIni'] = $data['pemasukanHariIni'] - IngredientTransaction::whereDate('created_at', Carbon::today())->sum('modal');
        $data['keuntungan'] = $data['totalPemasukan'] - IngredientTransaction::all()->sum('modal');
        $data['totalUser'] = User::whereHas('roles', function($query) {
            $query->whereNotIn('name', ['admin']);
        })->count();

        // dd($data);
        $user = Auth::user();
        return view('admin.dashboard', $data);
    }

    private function getSalesData($product_id, $range_month)
    {
        for ($i = 0; $i < $range_month; $i++) {
            // Ambil tanggal  bulan ke-i dari sekarang
            $date = Carbon::now()->subMonths($i);
            
            // Query untuk menghitung total penjualan di bulan tersebut
            $totalSales = Cart::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->where('product_id', $product_id)
                ->where('pembayaran', true)
                ->sum('jumlah');

            // Masukkan data ke array, misalnya dengan format nama bulan dan total penjualan
            $data['penjualanInMonth'][] = [
                'month' => $date->format('F Y'),
                'total' => $totalSales
            ];

            // dd($data);

            // $data['penjualanInMonth'][] = $totalSales;
        }

        $data['totalPenjualan'] = array_sum(array_column($data['penjualanInMonth'], 'total'));

        return $data;
    }

    public function getPrediction(Request $request) 
    {
        $data = Product::with('stocks')->get();
        $user = Auth::user();

        $products = Product::all();
        foreach ($products as $i => $product) {
            $prediction[] = $this->generatePredict(5, $product->id);
        }

        // dd($prediction);

        // dd($prediction);
        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('name', function($data) {
            return $data->name;
        })
        ->addColumn('prediction', function($data) use ($prediction) {
            foreach ($prediction as $p) {
                if ($p['product_id'] == $data->id) {
                    if($p['prediction'] < 1) return false;
                    return $p['prediction'];
                }
            }
            return null; // atau nilai default jika tidak ada yang cocok
        })
        ->addColumn('bahan', function($data) use ($prediction) {
            foreach ($prediction as $p) {
                if ($p['product_id'] == $data->id) {
                    $strBahan = '';
                    foreach ($data->stocks as $stock) {
                        $strBahan .= '- ' . $stock->name . ' : ' . number_format(($stock->pivot->gram_ml * $p['prediction'])) . ' - ' . number_format($stock->jumlah_gr) . ' (gudang)' . '<br>';
                    }
                    return $strBahan;
                }
            }
        })
        // ->filter(function ($query) use ($request) {
        //     if ($request->has('search') && $request->input('search.value')) {
        //         $search = $request->input('search.value');
        //         $query->where(function ($query) use ($search) {
        //             $query->where('jumlah_gr', 'like', "%{$search}%")
        //             ->orWhere('name', 'like', "%{$search}%");
        //         });
        //     }
        // })
        ->rawColumns(['bahan'])
        ->toJson();
    }

    private function generatePredict($n, $product_id) {
        $result['dataSales'] = $this->getSalesData($product_id, $n);

        // Membalikkan urutan array
        $result['dataSales']['penjualanInMonth'] = array_reverse($result['dataSales']['penjualanInMonth']);

        // dd($result['dataSales']);

        if ($n % 2 === 0) {
            // Jika genap, misalnya 6:
            // Kita ambil setengah bagian untuk nilai negatif dan setengah untuk nilai positif
            $half = $n / 2;
            for ($i = -$half; $i < 0; $i++) {
                $result['x'][] = $i;
                $result['x2'][] = $i * $i;
            }
            for ($i = 1; $i <= $half; $i++) {
                $result['x'][] = $i;
                $result['x2'][] = $i * $i;
            }
        } else {
            // Jika ganjil, misalnya 7:
            // Hitung setengah dari (n-1) dan sertakan 0 di tengah
            $half = ($n - 1) / 2;
            for ($i = -$half; $i <= $half; $i++) {
                $result['x'][] = $i;
                $result['x2'][] = $i * $i;
            }
        }

        foreach ($result['x'] as $index => $value) {
            $result['xy'][] = $value * $result['dataSales']['penjualanInMonth'][$index]['total'];
        }

        $result['sumXY'] = array_sum($result['xy']);
        $result['sumX2'] = array_sum($result['x2']);

        $result['a'] = $result['dataSales']['totalPenjualan'] / $n;

        $result['b'] = array_sum($result['xy']) / array_sum($result['x2']);

        $result['y'] = $result['a'] + ($result['b'] * 3); 
        
        return [
            'totalPenjualan' => $result['dataSales']['totalPenjualan'],
            'prediction' => floor($result['y']),
            'product_id' => $product_id
        ];
    }

    public function printPrediction(Request $request)
    {
        $data = Product::with('stocks')->get();
        $user = Auth::user();
        $nextMonth = Carbon::now()->addMonth()->format('F Y');

        $products = Product::all();
        foreach ($products as $i => $product) {
            $prediction[] = $this->generatePredict(5, $product->id);
        }

        // dd($prediction);

        return view('admin.printPrediction', compact('data', 'prediction', 'user', 'nextMonth'));
    }
}
