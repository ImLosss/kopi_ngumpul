<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

use function PHPUnit\Framework\isEmpty;

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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::findOrFail($id);
        return view('admin.pesanan.show', compact('order'));
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
                'no_meja' => $request->no_meja,
                'customer_name' => $request->name
            ]);

            Table::where('no_meja', $request->no_meja)->first()
            ->update([
                'status' => 'terpakai'
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
        ->where('partner', false)
        ->where(function ($query) {
            $query->where('status_id', '!=', 4)
                  ->orWhere('pembayaran', false);
        });
        
        if($user->hasRole('dapur')) {
            $data = Order::with('status')
            ->where('status_id', 2)
            ->orWhere('status_id', 3);
        }

        if($user->hasRole('pelayan')) {
            $data = Order::with('status')
            ->where('status_id', 3);
        }

        if($user->hasRole('partner')) {
            $data = Order::with('status')
            ->where('status_id', '!=', 1)
            ->where('partner', true)
            ->where(function ($query) {
                $query->where('status_id', '!=', 4)
                      ->orWhere('pembayaran', false);
            });
        }


        // dd($data);
        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('#', function($data) {
            return '<a href="' . route('order.show', $data->id) . '">Klik disini untuk lihat Pesanan</a>';
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
         ->addColumn('total', function($data) use($user) {
            if($data->partner) return $data->partner_total;
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
        } else if($user->hasRole('pelayan')) {
            $data = Cart::with('status', 'order', 'product')->where('order_id', $id)
            ->where('status_id', 3);
        }

        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('#', function($data) use ($user) {
            if($data->status_id < 4 && ($data->status_id != 2 || $user->hasRole('admin'))) {
                return '<div class="form-check">
                <input class="form-check-input" type="checkbox" value="' . $data->id . '" id="selectPesan[]" name="selectPesan[]">
                </div>';
            }
        })
        ->addColumn('menu', function($data) {
            return $data->menu;
        })
        ->addColumn('status_pembayaran', function($data) use($user) {
            if ($data->pembayaran) return 'Lunas';
            else {
                if($user->can('paymentAccess')) return '<a href="' . route('payment.show', $data->order_id) . '">Klik disini untuk selesaikan pembayaran</a>';
                return 'Belum Lunas';
            }
        })
        ->addColumn('note', function($data) {
            if($data->note == null) return 'None';
            $note = htmlspecialchars($data->note);
            return '<span title="'.$note.'">'.(strlen($note) > 50 ? substr($note, 0, 35) . '…' : $note).'</span>';
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
                $hapus = '<a class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')"></a>';
            }

            if(($data->status_id == 2 && $user->can('updateStatusTwo')) || ($data->status_id == 3 && $user->can('updateStatusThree'))) $update = '<a href="#" class="fa-solid fa-square-check text-success" style="margin-right: 10px;" onclick="modalUpdateStatus('. $data->id .')"></a>';

            return '
            <form id="formDelete_'. $data->id .'" action="' . route('pesanan.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>
            <form id="formUpdate_'. $data->id .'" action="' . route('pesanan.updateStatus', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('PATCH') . '
            </form>' . $update . $hapus;
        })
        ->rawColumns(['#', 'action', 'status_pembayaran', 'note'])
        ->toJson(); 
    }

    public function hapusPesanan($id) {
        try {
            $cart = Cart::with('product')->findOrFail($id);
            $order = Order::findOrFail($cart->order_id);

            $message = 'Berhasil menghapus ' . $cart->product->name;

            DB::transaction(function () use ($order, $cart) {
                $order->update([
                    'total' => $order->total - $cart->total,
                    'profit' => $order->profit - $cart->profit,
                    'partner_total' => $order->partner_total - $cart->partner_total,
                    'partner_profit' => $order->partner_profit - $cart->partner_profit
                ]);

                $product = Product::findOrFail($cart->product_id);
                $product->update([
                    'jumlah' => $product->jumlah + $cart->jumlah
                ]);

                $cart->delete();
            });

            $cek = Cart::where('order_id', $order->id)->first();

            if(!$cek) {
                $order->delete();
                return redirect()->route('order.index')->with('alert', 'success')->with('message', $message);
            }

            return redirect()->back()->with('alert', 'success')->with('message', $message);
        } catch (\Throwable $e) {
            return redirect()->back()->with('alert', 'error')->with('message', 'Something Error!');
        }
    }

    public function updateStatus($id) {
        $data = Cart::with('order')->findOrFail($id);
        $user = Auth::user();

        if($data->status_id < 4) {
            $data->update([
                'status_id' => $data->status_id + 1
            ]);
        }

        OrderService::checkStatusOrder($data->order->id); 

        $cekPesan = Cart::where('order_id', $data->order_id)->where('status_id', '!=', 4)->first();
        if($user->hasRole('dapur')) {
            $cekPesan = Cart::with('status')
            ->where('order_id', $data->order_id)
            ->where('status_id', 2)->first();
        }
        
        if($cekPesan) return redirect()->back()->with('modal_alert', 'success')->with('message', 'Berhasil update status');

        return redirect()->route('order.index')->with('modal_alert', 'success')->with('message', 'Berhasil update status');
    }

    public function updateOrDelete(Request $request) {
        // dd($request);
        if($request->action == 'update') {
            foreach ($request->selectPesan as $id) {
                $data = Cart::with('order')->findOrFail($id);
                $user = Auth::user();

                if($data->status_id < 4) {
                    $data->update([
                        'status_id' => $data->status_id + 1
                    ]);
                }

                OrderService::checkStatusOrder($data->order->id); 

                $cekPesan = Cart::where('order_id', $data->order_id)->where('status_id', '!=', 4)->first();
                if($user->hasRole('dapur')) {
                    $cekPesan = Cart::with('status')
                    ->where('order_id', $data->order_id)
                    ->where('status_id', 2)->first();
                }
            }
            if($cekPesan) return redirect()->back()->with('modal_alert', 'success')->with('message', 'Berhasil update status');

            return redirect()->route('order.index')->with('modal_alert', 'success')->with('message', 'Berhasil update status');
        } else if($request->action == 'hapus') {
            try {
                foreach($request->selectPesan as $id) {
                    $cart = Cart::with('product')->findOrFail($id);
                    $order = Order::findOrFail($cart->order_id);
        
                    DB::transaction(function () use ($order, $cart) {
                        $order->update([
                            'total' => $order->total - $cart->total,
                            'profit' => $order->profit - $cart->profit,
                            'partner_total' => $order->partner_total - $cart->partner_total,
                            'partner_profit' => $order->partner_profit - $cart->partner_profit
                        ]);
        
                        $product = Product::findOrFail($cart->product_id);
                        $product->update([
                            'jumlah' => $product->jumlah + $cart->jumlah
                        ]);
        
                        $cart->delete();
                    });    
                    $cek = Cart::where('order_id', $order->id)->first();

                    if(!$cek) {
                        $order->delete();
                        return redirect()->route('order.index')->with('alert', 'success')->with('message', 'Berhasil hapus Order');
                    }
                }
                return redirect()->back()->with('alert', 'success')->with('message', 'Berhasil hapus Order');
            } catch (\Throwable $e) {
                return redirect()->back()->with('alert', 'error')->with('message', 'Something Error!');
            }
        } else {
            return redirect()->back()->with('alert', 'info')->with('message', 'Invalid action');
        }
    }
}
