<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:orderAccess');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.pesanan.index');
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
        return view('admin.pesanan.show', compact('order'));
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
    public function update(OrderRequest $request, string $id)
    {
        DB::transaction(function () use ($request, $id) {
            $order = Order::with(['carts' => function($query) {
                $query->where('status_id', 1);
            }])->findOrFail($id);

            foreach($order->carts as $cart) {
                $cart->update([
                    'status_id' => 2
                ]);
            }

            $order->update([
                'no_meja' => $request->no_meja
            ]);

            OrderService::checkStatusOrder($id);
        });

        return redirect()->route('order.index')->with('alert', 'success')->with('message', 'Berhasil checkout');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getOrder() 
    {
        // $data = Order::with('status')->where('status_id', '!=', 1)->where('status_id', '!=', 4);

        // Untuk melihat semua data dalam request
        // Log::info('Request Data: ', $request->all());
        // if ($request->filled('category_id')) {
        //     $data->with('category')->where('category_id', $request->category_id);
        // }

        $user = Auth::user();
        $data = Order::with('status')
        ->where('status_id', '!=', 1)
        ->where(function ($query) {
            $query->where('status_id', '!=', 4)
                  ->orWhere('pembayaran', false);
        });
        
        if($user->hasRole('dapur')) {
            $data = Order::with('status')
            ->where('status_id', 2)
            ->orWhere('status_id', 3);
        }


        // dd($data);
        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('#', function($data) {
            return '<a href="' . route('order.show', $data->id) . '">Klik disini untuk lihat Pesanan</a>';
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

    public function getPesanan($id) 
    {
        $user = Auth::user();
        if($user->hasRole(['admin', 'kasir', 'partner'])) {
            $data = Cart::with('status', 'order', 'product')->where('order_id', $id)
            ->where(function ($query) {
                $query->where('status_id', '!=', 1)
                    ->where('status_id', '!=', 4)
                    ->orWhere('pembayaran', false);
            });
        } else if($user->hasRole('dapur')) {
            $data = Cart::with('status', 'order', 'product')->where('order_id', $id)
            ->where(function ($query) {
                $query->where('status_id', 2)
                      ->orWhere('status_id', 3);
            });
        }

        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('#', function($data) {
            return '<div class="form-check">
            <input class="form-check-input" type="checkbox" value="' . $data->id . '" id="selectPesan" name="selectPesan[]">
            </div>';
        })
        ->addColumn('menu', function($data) {
            return $data->product->name;
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
        ->addColumn('action', function($data) use($user) {
            $hapus = '';
            $update = '';

            if($user->hasRole(['admin', 'kasir', 'partner']) && $data->status_id < 4) {
                $hapus = '<button class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="deletePesan"></button>';
            }

            if(($data->status_id == 2 && $user->can('updateStatusTwo')) || ($data->status_id == 3 && $user->can('updateStatusThree'))) $update = '<button class="fa-solid fa-square-check text-success" onclick="modalUpdateStatus('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="updateStatus"></button>';

            return '
            <form id="form_'. $data->id .'" action="' . route('pesanan.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>
            <form id="formUpdate_'. $data->id .'" action="' . route('pesanan.updateStatus', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('PATCH') . '
            </form>' . $update . $hapus;
        })
        ->rawColumns(['#', 'action'])
        ->toJson(); 
    }

    public function hapusPesanan($id) {
        dd($id);
    }

    public function updateStatus($id) {
        $data = Cart::with('order')->findOrFail($id);
        $user = Auth::user();

        $data->update([
            'status_id' => $data->status_id + 1
        ]);

        OrderService::checkStatusOrder($data->order->id); 

        $cekPesan = Cart::where('order_id', $data->order_id)->where('status_id', '!=', 4)->first();
        if($user->hasRole('dapur')) {
            $cekPesan = Cart::with('status')
            ->where('order_id', $id)
            ->where('status_id', 2)->first();
        }
        
        if($cekPesan) return redirect()->back()->with('modal_alert', 'success')->with('message', 'Berhasil update status');

        return redirect()->route('order.index')->with('modal_alert', 'success')->with('message', 'Berhasil update status');
    }
}
