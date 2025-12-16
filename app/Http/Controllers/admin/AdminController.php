<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\StockOut;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['staff'] = User::whereDoesntHave('roles', function($query) {
            $query->where('name', 'admin');
        })->count();

        $data['penjualanHariIni'] = StockOut::whereDate('created_at', Carbon::today())->sum('qty');
        $data['totalStock'] = Stock::count();
        $data['pemasukan'] = StockOut::sum('total_price');
        $data['pemasukanHariIni'] = StockOut::whereDate('created_at', Carbon::today())->sum('total_price');

        $monthlySales = StockOut::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(qty) as total_qty, SUM(total_price) as total_revenue")
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $data['monthlySalesLabels'] = $monthlySales->map(fn ($row) => Carbon::createFromFormat('Y-m', $row->month)->isoFormat('MMM YY'));
        $data['monthlySalesValues'] = $monthlySales->pluck('total_qty');
        $data['monthlyRevenueValues'] = $monthlySales->pluck('total_revenue');

        return view('admin.dashboard', $data);
    }

     public function getPrediction(Request $request)
    {
        $data = Stock::all();

        $predictions = $this->generatePredictHybrid();

        return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('name', function($data) {
            return $data->name;
        })
        ->addColumn('trendLeast', function($data) use ($predictions) {
            foreach ($predictions as $p) {
                if ($p['product_id'] == $data->id) {
                    if($p['trendLeast'] < 1) return '-';
                    return $p['trendLeast'];
                }
            }
            return '-'; // atau nilai default jika tidak ada yang cocok
        })
        ->addColumn('holt', function($data) use ($predictions) {
            foreach ($predictions as $p) {
                if ($p['product_id'] == $data->id) {
                    if($p['holt'] < 1) return '-';
                    return $p['holt'];
                }
            }
            return '-'; // atau nilai default jika tidak ada yang cocok
        })
        ->addColumn('hybrid', function($data) use ($predictions) {
            foreach ($predictions as $p) {
                if ($p['product_id'] == $data->id) {
                    if($p['prediction'] < 1) return '-';
                    return $p['prediction'];
                }
            }
            return '-'; // atau nilai default jika tidak ada yang cocok
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
        // ->rawColumns(['bahan'])
        ->toJson();
    }

    private function getSalesData($stock_id, $range_month)
    {
        $data['penjualanInMonth'] = [];

        for ($i = 0; $i < $range_month; $i++) {
            $date = Carbon::now()->startOfMonth()->subMonths($i);

            // Query untuk menghitung total penjualan di bulan tersebut
            $totalSales = StockOut::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->where('stock_id', $stock_id)
                ->sum('qty');

            // Masukkan data ke array
            $data['penjualanInMonth'][] = [
                'month' => $date->format('F Y'),
                'total' => (int)$totalSales
            ];
        }

        $data['totalPenjualan'] = array_sum(array_column($data['penjualanInMonth'], 'total'));

        return $data;
    }

    private function generatePredictTls($dataSales, $n, $product_id) {
        $result['dataSales'] = $dataSales;

        // Membalikkan urutan array
        $result['dataSales']['penjualanInMonth'] = array_reverse($result['dataSales']['penjualanInMonth']);
        // dd($result['dataSales']);

        if ($n % 2 === 0) {
            // Jika genap, misalnya 6:
            // x akan bernilai -3, -2, -1, 1, 2, 3
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
            // x akan bernilai -3, -2, -1, 0, 1, 2, 3
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

        // dd($result);

        return [
            'totalPenjualan' => $result['dataSales']['totalPenjualan'],
            'prediction' => ceil($result['y']),
            'product_id' => $product_id,
            'metode' => "TREND LEAST"
        ];
    }

    private function generatePredictHolt(array $saleDataItems) {

        if (count($saleDataItems) < 1) return false;
        // Reverse untuk urutan dari terlama ke terbaru
        $payload = collect($saleDataItems)->map(function ($sales, $productId) {
            return [
                'product_id' => $productId,
                'sales' => array_values($sales),
            ];
        })->values()->all();

        // dd($salesData);

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->asJson()
                ->post('http://localhost:33/predict', ['items' => $payload]);

            if ($response->successful() && ($resultPredict = $response->json()) && ($resultPredict['success'] ?? false)) {
                return $resultPredict['data']; // sesuaikan dengan struktur balasan Python
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('Exception in generatePredictHolt: ' . $e->getMessage());
            return false;
        }
    }

    private function generatePredictHybrid() {
        $Stocks = Stock::all();
        $saleDataItems = [];
        $trendPredictions = [];
        foreach ($Stocks as $i => $Stock) {
            $result['dataSales'] = $this->getSalesData($Stock->id, 12);

            // Ambil semua total penjualan
            $totals = array_column($result['dataSales']['penjualanInMonth'], 'total');

            // Ambil 8 data pertama (terbaru/paling penting)
            $first5 = array_slice($totals, 0, 5);

            // Cek apakah ada 0 di 5 data pertama
            if (in_array(0, $first5)) {

                continue;
            }

            // Ambil 7 data berikutnya (data ke 6-12)
            $last7 = array_slice($totals, 5);

            // dd($last7, $first5);

            // Hapus data yang bernilai 0 dari data ke 6-12
            $last7Filtered = array_filter($last7, function($value) {
                return $value != 0;
            });

            // Gabungkan data 1-8 dengan data 9-12 yang sudah difilter
            $salesData = array_merge($first5, $last7Filtered);

            if(count($salesData) >= 8) $saleDataItems[$Stock->id] = array_reverse($salesData);

            if (count($salesData) < 5) return false;

            // $holtResult = $this->generatePredictHolt($salesData);
            $trendLeastResult = $this->generatePredictTls($result['dataSales'], count($salesData), $Stock->id);

            $trendPredictions[] = [
                'product_id' => $Stock->id,
                'trendLeast' => $trendLeastResult['prediction'],
            ];
        }

        // dd($trendPredictions);

        $holtResult = $this->generatePredictHolt($saleDataItems);

        $hybridPredictions = [];
        foreach ($trendPredictions as $trend) {
            $productId = $trend['product_id'];
            $trendValue = $trend['trendLeast'];

            $holtItem = collect($holtResult)->firstWhere('product_id', $productId);
            $holtValue = $holtItem['forecast'] ?? null;

            $hybridValue = $holtValue !== null
                ? ((0.6 * $holtValue) + (0.4 * $trendValue))
                : false;

            $hybridPredictions[] = [
                'product_id' => $productId,
                'trendLeast' => $trendValue,
                'holt' => $holtValue,
                'prediction' => ceil($hybridValue),
            ];
        }

        // dd($hybridPredictions, $holtResult);

        return $hybridPredictions;
    }
}
