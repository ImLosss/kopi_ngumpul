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
    // Ambil data prediksi dan indeks-kan berdasarkan product_id agar pencarian nilainya instan
    $rawPredictions = $this->generatePredictHybrid();
    $predictions = collect($rawPredictions)->keyBy('product_id')->toArray();

    $data = Stock::query();

    return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('name', function($data) {
            return $data->name;
        })
        ->addColumn('trendLeast', function($data) use ($predictions) {
            $p = $predictions[$data->id] ?? null;
            if (!$p || $p['trendLeast'] < 1) return '-';
            return $p['trendLeast'];
        })
        ->addColumn('holt', function($data) use ($predictions) {
            $p = $predictions[$data->id] ?? null;
            if (!$p || $p['holt'] === '-' || $p['holt'] < 1) return '-';
            return $p['holt'];
        })
        ->addColumn('hybrid', function($data) use ($predictions) {
            $p = $predictions[$data->id] ?? null;
            if (!$p || $p['prediction'] < 1) return '-';
            return $p['prediction'];
        })
        ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->input('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%"); // Pencarian berdasarkan nama atau kode barang
                });
            }
        })
        ->toJson();
}

    private function getSalesData($stockId, $rangeMonths, $allSalesGrouped)
    {
        $data['penjualanInMonth'] = [];

        for ($i = 1; $i <= $rangeMonths; $i++) {
            $date = Carbon::now()->startOfMonth()->subMonths($i);
            $yearMonth = $date->format('Y-m');

            // Ambil dari hasil grouping yang sudah ditarik sekali di awal (sangat cepat)
            $totalSales = $allSalesGrouped[$stockId][$yearMonth] ?? 0;

            $data['penjualanInMonth'][] = [
                'month' => $date->format('F Y'),
                'total' => (int)$totalSales
            ];
        }

        $data['totalPenjualan'] = array_sum(array_column($data['penjualanInMonth'], 'total'));

        return $data;
    }

    private function generatePredictTls($dataSales, $n, $product_id)
    {
        $result['dataSales'] = $dataSales;

        // 1. BALIK URUTAN ARRAY DI AWAL
        // Sekarang urutan indeks 0 adalah bulan terlama (Desember 2025)
        // dan indeks ke-($n-1) adalah bulan terbaru (Juli 2026).
        $result['dataSales']['penjualanInMonth'] = array_reverse($result['dataSales']['penjualanInMonth']);

        // Batasi data sesuai dengan nilai $n yang dikirimkan ($n = count($salesData))
        // Ini penting agar jika data yang lolos filter berjumlah 8, array-nya dipotong jadi 8 saja.
        $result['dataSales']['penjualanInMonth'] = array_slice($result['dataSales']['penjualanInMonth'], -$n);

        $result['x'] = [];
        $result['x2'] = [];
        $result['xy'] = [];

        // 2. Tentukan Nilai X dan X kuadrat
        for ($i = 1; $i <= $n; $i++) {
            if ($n % 2 === 0) {
                $xVal = (2 * $i) - $n - 1;
            } else {
                $xVal = $i - (($n + 1) / 2);
            }

            $result['x'][] = $xVal;
            $result['x2'][] = $xVal * $xVal;
        }

        // 3. Hitung XY (Sekarang indeks $index pada $result['x'] sudah sinkron dengan bulan penjualan)
        foreach ($result['x'] as $index => $value) {
            $result['xy'][] = $value * $result['dataSales']['penjualanInMonth'][$index]['total'];
        }

        $result['sumXY'] = array_sum($result['xy']);
        $result['sumX2'] = array_sum($result['x2']);

        // 4. Hitung a dan b
        $totalPenjualanAktif = array_sum(array_column($result['dataSales']['penjualanInMonth'], 'total'));
        $result['a'] = $totalPenjualanAktif / $n;
        $result['b'] = $result['sumXY'] / $result['sumX2'];

        // 5. MENCARI X PREDIKSI (Bulan Depan)
        $lastX = end($result['x']);
        $interval = ($n % 2 === 0) ? 2 : 1;
        $nextX = $lastX + $interval;

        // 6. Rumus Final Prediksi: Y = a + bX
        $result['y'] = $result['a'] + ($result['b'] * $nextX);

        // if ($product_id === 116) {
        //     dd($result);
        // }

        return [
            'totalPenjualan' => $totalPenjualanAktif,
            'prediction' => max(0, ceil($result['y'])), // Mencegah hasil minus
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

        // OPTIMASI UTAMA: Ambil seluruh data penjualan 12 bulan terakhir dalam 1 Kali Query Saja!
        $startDate = Carbon::now()->startOfMonth()->subMonths(12)->startOfDay();
        $endDate = Carbon::now()->startOfMonth()->subDays(1)->endOfDay();

        $allSales = StockOut::selectRaw("stock_id, DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(qty) as total")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('stock_id', 'ym')
            ->get()
            ->groupBy('stock_id');

        // Ubah struktur collection agar gampang dipetakan oleh getSalesData
        $allSalesGrouped = [];
        foreach ($allSales as $stockId => $rows) {
            $allSalesGrouped[$stockId] = $rows->pluck('total', 'ym')->toArray();
        }

        $saleDataItems = [];
        $trendPredictions = [];

        foreach ($Stocks as $Stock) {
            // Kirim data cache global ke getSalesData
            $result['dataSales'] = $this->getSalesData($Stock->id, 12, $allSalesGrouped);

            $totals = array_column($result['dataSales']['penjualanInMonth'], 'total');
            $first5 = array_slice($totals, 0, 5);

            if (in_array(0, $first5)) {
                continue;
            }

            $last7 = array_slice($totals, 5);
            $last7Filtered = array_filter($last7, function($value) {
                return $value != 0;
            });

            $salesData = array_merge($first5, $last7Filtered);

            if (count($salesData) >= 8) {
                $saleDataItems[$Stock->id] = array_reverse($salesData);
            }

            if (count($salesData) < 5) continue;

            $trendLeastResult = $this->generatePredictTls($result['dataSales'], count($salesData), $Stock->id);

            $trendPredictions[] = [
                'product_id' => $Stock->id,
                'trendLeast' => $trendLeastResult['prediction'],
            ];
        }

        $holtResult = $this->generatePredictHolt($saleDataItems);

        $hybridPredictions = [];
        foreach ($trendPredictions as $trend) {
            $productId = $trend['product_id'];
            $trendValue = $trend['trendLeast'];

            $holtItem = is_array($holtResult) ? collect($holtResult)->firstWhere('product_id', $productId) : null;
            $holtValue = $holtItem['forecast'] ?? null;

            $hybridValue = null;
            if ($holtValue !== null && is_numeric($holtValue)) {
                $hybridValue = (0.6 * $holtValue) + (0.4 * $trendValue);
            }

            $hybridPredictions[] = [
                'product_id' => $productId,
                'trendLeast' => $trendValue,
                'holt' => $holtValue ?? '-',
                'prediction' => $hybridValue !== null ? ceil($hybridValue) : null,
            ];
        }

        return $hybridPredictions;
    }
}
