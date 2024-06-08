<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:paymentAccess');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.pembayaran.index');
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
        return view('admin.pembayaran.show', compact('order'));
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

    public function cetakNota(Request $request) {
        $order_id = Cart::with('order')->distinct('order_id')->whereIn('id', $request->selectPesan)->get(['order_id']);
        $orderIdsArr = $order_id->pluck('order_id')->toArray();

        $data['cart'] = Cart::with('product', 'order')->whereIn('id', $request->selectPesan)->get();
        $data['order_id'] = $order_id->pluck('order_id')->implode(', ');
        $data['kasir'] = $data['cart']->pluck('order.kasir')->unique()->implode(', ');
        $data['order'] = Order::whereIn('id', $orderIdsArr)->get();
        $data['total'] = Order::whereIn('id', $orderIdsArr)->sum('total');
        $data['diskon'] = $data['cart']->sum('total_diskon');
        // dd($data);
        
        return view('admin.pembayaran.nota', $data);
    }

    public function getAllOrder()
    {
        $data = Order::with('status')
        ->where('status_id', '!=', 1)
        ->where('pembayaran', false);


        // dd($data);
        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('#', function($data) {
            return '<a href="' . route('payment.show', $data->id) . '">Klik disini untuk lihat Pesanan</a>';
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
            return $data->total;
         })
         ->addColumn('status_pembayaran', function($data) {
            if ($data->pembayaran) return 'Lunas';
            else return 'Belum Lunas';
         })
         ->addColumn('status', function($data) {
            return $data->status->desc;
         })
         ->addColumn('waktu_pesan', function($data) {
            return $data->created_at;
         })
        ->rawColumns(['#', 'action'])
        ->toJson(); 
    }

    public function getPayment($no_meja) {
        $user = Auth::user();
        if($user->hasRole(['admin', 'kasir'])) {
            $data = Cart::with('status', 'order', 'product')
            ->whereHas('order', function ($query) use ($no_meja) {
                $query->where('no_meja', $no_meja);
            })->get();
        } else if($user->hasRole('partner')) {
            $data = Cart::with('status', 'order', 'product')->where('order_id', 1)
            ->where(function ($query) {
                $query->where('status_id', 2)
                      ->orWhere('status_id', 3);
            });
        }

        // dd($data);

        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('#', function($data) {
            return '<div class="form-check">
            <input class="form-check-input" type="checkbox" value="' . $data->id . '" id="selectPesan[]" name="selectPesan[]">
            </div>';
        })
        ->addColumn('menu', function($data) {
            return $data->product->name;
        })
        ->addColumn('status_pembayaran', function($data) {
            if ($data->pembayaran) return 'Lunas';
            else return 'Belum Lunas';
        })
        ->addColumn('waktu_pesan', function($data) {
            return $data->created_at;
        })
        ->addColumn('total', function($data) {
            return 'Rp' . number_format($data->total);
        })
        ->addColumn('action', function($data) use($user) {
            $hapus = '';
            $update = '';

            if($user->can('paymentAccess') && $data->pembayaran == false) $update = $update = '<a href="#" class="fa-solid fa-square-check text-success" onclick="modalUpdateStatus('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="updateStatus""></a>';

            return '<form id="formUpdate_'. $data->id .'" action="' . route('payment.updateStatus', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('PATCH') . '
            </form>' . $update . $hapus;
        })
        ->rawColumns(['#', 'action'])
        ->toJson(); 
    }
}
