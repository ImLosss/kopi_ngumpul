<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:dailyReportAccess');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.report.index');
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
        $order = Order::findOrFail($id);
        return view('admin.report.show', compact('order'));
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

    public function getAllReport()
    {
        $user = Auth::user();
        $oneMonthAgo = Carbon::now()->subMonth();
        $oneDayAgo = Carbon::now()->subDay();
        $data = Order::with('status')->where('status_id', 4)->where('pembayaran', true)->whereDate('created_at', '>=', $oneMonthAgo);

        // dd($data);
        return DataTables::of($data)
        ->addColumn('#', function($data) {
            return '<a href="' . route('report.show', $data->id) . '">Klik disini untuk lihat Pesanan</a>';
        })
        ->addColumn('no_meja', function($data) {
        $no_meja = 'kosong';
        if($data->no_meja) $no_meja = $data->no_meja;
            return $no_meja;
        })
        ->addColumn('kasir', function($data) {
            return $data->kasir;
        })
        ->addColumn('total', function($data) {
            return 'Rp' . number_format($data->total);
        })
        ->addColumn('profit', function($data) {
            return 'Rp' . number_format($data->profit);
        })
        ->addColumn('waktu_pesan', function($data) {
            return $data->created_at;
        })
        ->rawColumns(['#', 'action'])
        ->toJson(); 
    }

    public function getReport($id) 
    {
        $user = Auth::user();
        $data = Cart::with('status', 'order', 'product')->where('order_id', $id);

        return DataTables::of($data)
        ->addColumn('menu', function($data) {
            return $data->menu;
        })
        ->addColumn('jumlah', function($data) {
            return $data->jumlah;
        })
        ->addColumn('harga', function($data) use($user) {
            return 'Rp' . number_format($data->harga);
        })
        ->addColumn('diskon', function($data) {
            if($data->total_diskon == 0) return 'None';
            return 'Rp' . number_format($data->total_diskon);
        })
        ->addColumn('total', function($data) {
            return 'Rp' . number_format($data->total);
        })
        ->addColumn('profit', function($data) use($user) {
            return 'Rp' . number_format($data->profit);
        })
        ->toJson(); 
    }
}
