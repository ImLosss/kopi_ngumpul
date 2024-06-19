<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

use function PHPUnit\Framework\isEmpty;

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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::findOrFail($id);
        return view('admin.report.show', compact('order'));
    }

    public function printReport(Request $request)
    {
        // dd($request);
        $user = Auth::user();

        if(!$request->has('id_order')) return redirect()->back()->with('alert', 'info')->with('message', 'Tidak ada History yang terpilih');
        $data['order'] = Order::with('carts')->withSum('carts', 'jumlah')->withSum('carts', 'harga')->withSum('carts', 'partner_price')->withSum('carts', 'total_diskon')->whereIn('id', $request->id_order)->get();
        $data['strDate'] = Carbon::now()->format('Y-m-d');
        $data['total'] = $data['order']->sum('total');
        $data['profit'] = $data['order']->sum('profit');
        $data['assignDate'] = Carbon::now()->locale('id_ID')->isoFormat('D MMMM YYYY');
        $data['signatory'] = $request->signatoryName;

        if($user->hasRole('partner')) {
            $data['total'] = $data['order']->sum('partner_total');
            $data['penyerahan_dana'] = $data['order']->sum('total');
            $data['profit'] = $data['order']->sum('partner_profit');
        }
        if($request->filled('startDate') && $request->filled('endDate')) $data['strDate'] = str_replace('-', '/', $request->startDate) . ' - ' . str_replace('-', '/', $request->endDate);
        // dd($request);
        return view('admin.report.print', $data);
    }

    public function getAllReport(Request $request)
    {
        // Log::info('Request Data: ', $request->all());

        $user = Auth::user();
        $oneMonthAgo = Carbon::now()->subMonth();
        $oneDayAgo = Carbon::now()->subDay();
        $data = Order::with('status')->where('pembayaran', true)->whereDate('created_at', '>=', $oneDayAgo);

        if($user->hasRole('partner')) $data = Order::with('status')->where('pembayaran', true)->where('kasir', $user->name)->where('partner', true)->whereDate('created_at', '>=', $oneDayAgo);

        if ($request->filled('startDate') && $request->filled('endDate')) {
            $data = Order::with('status')->where('pembayaran', true)->whereBetween('created_at', [$request->startDate, Carbon::parse($request->endDate)->addDay()]);
            if($user->hasRole('partner')) $data = Order::with('status')->where('pembayaran', true)->where('kasir', $user->name)->where('partner', true)->whereBetween('created_at', [$request->startDate, Carbon::parse($request->endDate)->addDay()]);
        }

        $totalPendapatan = $data->sum('total');
        $totalProfit = $data->sum('profit');

        if($user->hasRole('partner')) {
            $totalPendapatan = $data->sum('partner_total');
            $totalProfit = $data->sum('partner_profit');
        }

        return DataTables::of($data)
        ->addColumn('#', function($data) {
            return '<a href="' . route('report.show', $data->id) . '">Klik disini untuk lihat Pesanan</a><input type="text" name="id_order[]" value="' . $data->id . '" readonly hidden>';
        })
        ->addColumn('kasir', function($data) {
            return $data->kasir;
        })
        ->addColumn('total', function($data) use($user) {
            if($user->hasRole('partner')) return 'Rp' . number_format($data->partner_total);
            return 'Rp' . number_format($data->total);
        })
        ->addColumn('profit', function($data) use($user) {
            if($user->hasRole('partner')) return 'Rp' . number_format($data->partner_profit);
            return 'Rp' . number_format($data->profit);
        })
        ->addColumn('waktu_pesan', function($data) {
            return $data->created_at;
        })
        ->with('totalPendapatan', $totalPendapatan)
        ->with('totalProfit', $totalProfit)
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
            if($data->order->partner) return 'Rp' . number_format($data->partner_price);
            return 'Rp' . number_format($data->harga);
        })
        ->addColumn('diskon', function($data) {
            if($data->total_diskon == 0) return 'None';
            return 'Rp' . number_format($data->total_diskon);
        })
        ->addColumn('total', function($data) {
            if($data->order->partner) return 'Rp' . number_format($data->partner_total);
            return 'Rp' . number_format($data->total);
        })
        ->addColumn('profit', function($data) use($user) {
            if($data->order->partner) return 'Rp' . number_format($data->partner_profit);
            return 'Rp' . number_format($data->profit);
        })
        ->toJson(); 
    }
}
