<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Table;
use App\Services\OrderService;
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
        $no_meja = $order->no_meja;
        $user = Auth::user();

        if($user->hasRole(['admin', 'kasir'])) {
            $data = Cart::with('status', 'order', 'product')
            ->where('pembayaran', false)
            ->whereHas('order', function ($query) use ($no_meja) {
                $query->where('no_meja', $no_meja)->where('partner', false);
            })->first();
        } else if($user->hasRole('partner')) {
            $data = Cart::with('status', 'order', 'product')
            ->where('pembayaran', false)
            ->whereHas('order', function ($query) use ($no_meja) {
                $query->where('no_meja', $no_meja)->where('partner', true);
            })->first();
        }

        if($data) return view('admin.pembayaran.show', compact('order'));
        return view('admin.pembayaran.index');
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


    public function updateStatus(Request $request, string $id) {
        // dd($request);
        $cart = Cart::with('product', 'order')->findOrFail($id);

        $cart->update([
            'pembayaran' => true,
            'payment_method' => $request->paymentSingle
        ]);

        if($request->updateMejaSingle == 'true') {
            $table = Table::where('no_meja', $request->no_meja_single)->first();

            $table->update([
                'status' => 'kosong'
            ]);
        }

        $orderIdsArr = $cart->pluck('order_id')->toArray();

        OrderService::checkStatusOrderArr($orderIdsArr); 

        return redirect()->back()->with('modal_alert', 'success')->with('message', 'Berhasil update Pembayaran');
    }

    public function billOrUpdate(Request $request) {
        if ($request->action == 'printBill') {
            $user = Auth::user();
            $order_id = Cart::with('order')->distinct('order_id')->whereIn('id', $request->selectPesan)->get(['order_id']);
            $orderIdsArr = $order_id->pluck('order_id')->toArray();

            $data['cart'] = Cart::with('product', 'order')->whereIn('id', $request->selectPesan)->get();
            $data['order_id'] = $order_id->pluck('order_id')->implode(', ');
            $data['kasir'] = $data['cart']->pluck('order.kasir')->unique()->implode(', ');
            $data['order'] = Order::whereIn('id', $orderIdsArr)->get();
            $data['total'] = $data['cart']->sum('total');
            if($user->hasRole('partner')) $data['total'] = $data['cart']->sum('partner_total');
            $data['diskon'] = $data['cart']->sum('total_diskon');
            
            return view('admin.pembayaran.nota', $data);
        } else if ($request->action == 'updatePayment') {
            // dd($request);
            $carts = Cart::with('product', 'order')->whereIn('id', $request->selectPesan)->get();

            if($request->updateMeja == 'true') {
                $table = Table::where('no_meja', $request->no_meja)->first();

                $table->update([
                    'status' => 'kosong'
                ]);
            }

            foreach ($carts as $cart) {
                $cart->update([
                    'pembayaran' => true,
                    'payment_method' => $request->payment
                ]);
            }

            $order_id = Cart::with('order')->distinct('order_id')->whereIn('id', $request->selectPesan)->get(['order_id']);
            $orderIdsArr = $order_id->pluck('order_id')->toArray();

            OrderService::checkStatusOrderArr($orderIdsArr);

            return redirect()->back()->with('modal_alert', 'success')->with('message', 'Berhasil update Pembayaran');
        } else return redirect()->back()->with('alert', 'error')->with('message', 'Something Error!');
    }

    public function getAllOrder()
    {
        $user = Auth::user();
        $data = Order::with('status')
        ->where('status_id', '!=', 1)
        ->where('pembayaran', false)
        ->where('partner', false);

        if($user->hasRole('partner')) {
            $data = Order::with('status')
            ->where('status_id', '!=', 1)
            ->where('partner', true)
            ->where('pembayaran', false);
        }
        // dd($data);
        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('#', function($data) {
            return '<a href="' . route('payment.show', $data->id) . '">Klik disini untuk lihat Pesanan</a>';
         })
         ->addColumn('customer_name', function($data) {
            if (!$data->customer_name) return 'none';
            return $data->customer_name;
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
            if($data->partner) return 'Rp' . number_format($data->partner_total);
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
            ->where('pembayaran', false)
            ->whereHas('order', function ($query) use ($no_meja) {
                $query->where('no_meja', $no_meja)->where('partner', false);
            })->get();
        } else if($user->hasRole('partner')) {
            $data = Cart::with('status', 'order', 'product')
            ->where('pembayaran', false)
            ->whereHas('order', function ($query) use ($no_meja) {
                $query->where('no_meja', $no_meja)->where('partner', true);
            })->get();
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
            return $data->menu;
        })
        ->addColumn('status_pembayaran', function($data) {
            if ($data->pembayaran) return 'Lunas';
            else return 'Belum Lunas';
        })
        ->addColumn('waktu_pesan', function($data) {
            return $data->created_at;
        })
        ->addColumn('total', function($data) {
            if($data->order->partner) return 'Rp' . number_format($data->partner_total);
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
