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
        return redirect()->route('payment');
    }


    public function updateStatus(Request $request, string $id) {
        dd($request);
        $cart = Cart::with('product', 'order')->findOrFail($id);

        $order = Order::findOrFail($cart->order_id);

        $order->update([
            'kasir_id' => Auth::user()->id
        ]);

        $cart->update([
            'pembayaran' => true,
            'payment_method' => $request->paymentSingle,
            'update_payment_by' => Auth::user()->name
        ]);

        if($request->updateMejaSingle == 'true') {
            $table = Table::where('no_meja', $request->no_meja_single)->first();
            
            if($table) {
                $table->update([
                    'status' => 'kosong'
                ]);
            }
        }

        $orderIdsArr = [$cart->order_id];

        foreach ($orderIdsArr as $id) {
            $cekPesan = Cart::where('order_id', $id)->where(function ($query) {
                $query->where('status_id', '!=', 5)
                ->where('status_id', '!=', 1)
                ->orWhere('pembayaran', false);
            })->first();            

            if(!$cekPesan) {
                $cekTrash = Cart::onlyTrashed()->where('order_id', $id)->get();

                foreach ($cekTrash as $trash) {
                    $trash->forceDelete();
                }
            }
        }
        
        OrderService::checkStatusOrderArr($orderIdsArr); 

        return redirect()->back()->with('modal_alert', 'success')->with('message', 'Berhasil update Pembayaran');
    }

    public function billOrUpdate(Request $request) {
        if(empty($request->selectPesan)) return redirect()->back()->with('alert', 'info')->with('message', 'Tidak ada order yang terpilih');
        if ($request->action == 'printBill') {
            $data = $this->getPrintData($request);
            return view('admin.pembayaran.nota', $data->data);
        } else if ($request->action == 'updatePayment') {

            $carts = Cart::with('product', 'order')->whereIn('id', $request->selectPesan)->get();

            if($request->updateMeja == 'true') {
                $table = Table::where('no_meja', $request->no_meja)->first();

                if($table) {
                    $table->update([
                        'status' => 'kosong'
                    ]);
                }
            }

            foreach ($carts as $cart) {
                $cart->update([
                    'pembayaran' => true,
                    'payment_method' => $request->payment,
                    'update_payment_by' => Auth::user()->name
                ]);
            }

            $order_id = Cart::with('order')->distinct('order_id')->whereIn('id', $request->selectPesan)->get(['order_id']);
            $orderIdsArr = $order_id->pluck('order_id')->toArray();

            foreach ($orderIdsArr as $id) {
                $cekPesan = Cart::where('order_id', $id)->where(function ($query) {
                    $query->where('status_id', '!=', 5)
                    ->where('status_id', '!=', 1)
                    ->orWhere('pembayaran', false);
                })->first();

                // dd($cekPesan);
                if(!$cekPesan) {
                    $cekTrash = Cart::onlyTrashed()->where('order_id', $id)->get();

                    foreach ($cekTrash as $trash) {
                        $trash->forceDelete();
                    }
                }
                
                $order = Order::findOrFail($id);
                
                $order->update([    
                    'kasir_id' => Auth::user()->id
                ]);
            }

            // dd($orderIdsArr);
            OrderService::checkStatusOrderArr($orderIdsArr);

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
        $data = Order::with(['status', 'kasir'])
        ->where('status_id', '!=', 1)
        ->where('pembayaran', false)
        ->where('partner', false);

        if($user->hasRole('partner')) {
            $data = Order::with(['status', 'kasir'])
            ->where('user_id', $user->id)
            ->where('status_id', '!=', 1)
            ->where('partner', true)
            ->where('pembayaran', false);
        }

        if($user->hasRole('kasir')) {
            $data = Order::with(['status', 'kasir'])
            ->where('status_id', '!=', 1)
            ->where('pembayaran', false)
            ->where('partner', false)
            ->where(function ($query) use($user) {
                $query->where('kasir_id', $user->id)
                      ->orWhere('kasir_id', null);
            });
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
         ->addColumn('status', function($data) {
            return $data->status->desc;
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

    public function getPayment(Request $request, $no_meja) {
        $user = Auth::user();
        if($user->hasRole(['admin', 'kasir'])) {
            $data = Cart::with('status', 'order', 'product')
            ->where('pembayaran', false)
            ->whereHas('order', function ($query) use ($no_meja) {
                $query->where('no_meja', $no_meja)->where('partner', false);
            });
        } else if($user->hasRole('partner')) {
            $data = Cart::with('status', 'order', 'product')
            ->where('pembayaran', false)
            ->whereHas('order', function ($query) use ($no_meja) {
                $query->where('no_meja', $no_meja)->where('partner', true);
            });
        }

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
        // ->addColumn('action', function($data) use($user) {
        //     if($data->order->partner) $total = $data->partner_total;
        //     $total = $data->total;
        //     $hapus = '';
        //     $update = '';

        //     if($user->can('paymentAccess') && $data->pembayaran == false) $update = $update = '<a href="#" class="fa-solid fa-square-check text-success" onclick="modalUpdateStatus('. $data->id .', '. $total .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="updateStatus""></a>';

        //     return '<form id="formUpdate_'. $data->id .'" action="' . route('payment.updateStatus', $data->id) . '" method="POST" class="inline">
        //         ' . csrf_field() . '
        //         ' . method_field('PATCH') . '
        //     </form>' . $update . $hapus;
        // })
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
