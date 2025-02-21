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

        $data = Cart::with('order', 'product')
        ->where('pembayaran', false)
        ->where('order_id', $id)->first();

        if($data) return view('admin.pembayaran.show', compact('order'));
        return redirect()->route('payment');
    }

    public function billOrUpdate(Request $request) {
        // dd($request);
        if(empty($request->selectPesan)) return redirect()->back()->with('alert', 'info')->with('message', 'Tidak ada order yang terpilih');
        if ($request->action == 'printBill') {
            $data = $this->getPrintData($request);
            return view('admin.pembayaran.nota', $data->data);
        } else if ($request->action == 'updatePayment') {

            $carts = Cart::with('product', 'order')->whereIn('id', $request->selectPesan)->get();

            foreach ($carts as $cart) {
                $cart->update([
                    'pembayaran' => true,
                    'payment_method' => $request->payment,
                    'update_payment_by' => Auth::user()->name
                ]);
            }

            $order = Order::findOrFail($request->order_id);
                
            $order->update([    
                'kasir_id' => Auth::user()->id
            ]);

            // dd($orderIdsArr);
            // OrderService::checkStatusOrderArr($orderIdsArr);

            if($request->printNota == 'true') {
                $data = $this->getPrintData($request);

                if(!$data->status) return redirect()->back()->with('alert', 'info')->with('message', $data->message);
                return view('admin.pembayaran.nota', $data->data);
            } else {
                return redirect()->back()->with('modal_alert', 'success')->with('message', 'Berhasil update Pembayaran');
            }
        } else return redirect()->back()->with('alert', 'error')->with('message', 'Something Error!');
    }

    private function getPrintData($request) {
        $user = Auth::user();
        $order_id = Cart::with('order')->distinct('order_id')->whereIn('id', $request->selectPesan)->get(['order_id']);
        $orderIdsArr = $order_id->pluck('order_id')->toArray();

        $data['cart'] = Cart::with('product', 'order')->whereIn('id', $request->selectPesan)->get();

        $total = $data['cart']->sum('total');
        if($user->hasRole('partner')) $total = $data['cart']->sum('partner_total');
        if($total > $request->uangCust && $request->payment == 'Tunai') {
            return (object) [
                'status' => false,
                'message' => 'Cash kurang ' . number_format($total - $request->uangCust),
            ];
        };

        $data['order_id'] = $order_id->pluck('order_id')->implode(', ');
        $data['kasir'] = $data['cart']->pluck('order.kasir')->unique()->implode(', ');
        $data['order'] = Order::whereIn('id', $orderIdsArr)->get();
        $data['total'] = $data['cart']->sum('total');
        if($user->hasRole('partner')) $data['total'] = $data['cart']->sum('partner_total');
        $data['diskon'] = $data['cart']->sum('total_diskon');
        $data['payment'] = $request->payment;

        if($request->payment == "Tunai") {
            $data['change'] = $request->uangCust - $total;
            $data['cash'] = $request->uangCust;
        }

        return (object) [
            'status' => true,
            'data' => $data,
        ];
    }

    public function getAllOrder(Request $request)
    {
        $user = Auth::user();
        $data = Order::with(['kasir'])
        ->where('status', 'selesai')
        ->where('pembayaran', false);

        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('#', function($data) {
            return '<a href="' . route('payment.show', $data->id) . '">Klik disini untuk lihat Pesanan</a>';
         })
         ->addColumn('customer_name', function($data) {
            if (!$data->customer_name) return 'none';
            return $data->customer_name;
         })
         ->addColumn('kasir', function($data) {
            if (!$data->kasir_id) return 'none';
            return $data->kasir->name;
         })
         ->addColumn('total', function($data) {
            if($data->partner) return 'Rp' . number_format($data->partner_total);
            return 'Rp' . number_format($data->total);
         })
         ->addColumn('status_pembayaran', function($data) {
            if ($data->pembayaran) return '<span class="badge badge-sm bg-gradient-primary">Lunas</span>';
            else {
                return '<span class="badge badge-sm bg-gradient-warning">Belum Lunas</span>';
            }
         })
         ->addColumn('waktu_pesan', function($data) {
            return $data->created_at;
         })
         ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->input('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($query) use ($search) {
                    $query->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('no_meja', 'like', "%{$search}%")
                    ->orWhere('created_at', 'like', "%{$search}%")
                    ->orWhereHas('kasir', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
                    
                    if (strtolower($search) === 'lunas') {
                        $query->orWhere('pembayaran', true);
                    } elseif (strtolower($search) === 'belum lunas') {
                        $query->orWhere('pembayaran', false);
                    }
                });
            }
        })
        ->rawColumns(['#', 'action', 'status_pembayaran'])
        ->toJson(); 
    }

    public function getPayment(Request $request, $id) {
        $user = Auth::user();
        $data = Cart::with('order', 'product')
        ->where('pembayaran', false)
        ->where('order_id', $id);

        // dd($data);

        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('#', function($data) {
            $total = $data->total;
            if($data->order->partner) $total = $data->partner_total;

            return '<div class="form-check">
            <input class="form-check-input" type="checkbox" value="' . $data->id . '" id="selectPesan[]" name="selectPesan[]" data-total="' . $total .'">
            </div>';
        })
        ->addColumn('customer_name', function($data) {
            if (!$data->order->customer_name) return 'none';
            return $data->order->customer_name;
         })
        ->addColumn('menu', function($data) {
            return $data->menu;
        })
        ->addColumn('status_pembayaran', function($data) {
            if ($data->pembayaran) return '<span class="badge badge-sm bg-gradient-primary">Lunas</span>';
            else {
                return '<span class="badge badge-sm bg-gradient-warning">Belum Lunas</span>';
            }
        })
        ->addColumn('waktu_pesan', function($data) {
            return $data->created_at;
        })
        ->addColumn('total', function($data) {
            if($data->order->partner) return 'Rp' . number_format($data->partner_total);
            return 'Rp' . number_format($data->total);
        })
        ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->input('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($query) use ($search) {
                    $query->where('menu', 'like', "%{$search}%");

                    if (strtolower($search) === 'lunas') {
                        $query->orWhere('pembayaran', true);
                    } elseif (strtolower($search) === 'belum lunas' || strtolower($search) === 'belum') {
                        $query->orWhere('pembayaran', false);
                    }
                });
            }
        })
        ->rawColumns(['#', 'status_pembayaran'])
        ->toJson(); 
    }
}
