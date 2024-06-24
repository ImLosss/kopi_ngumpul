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
                    'status_id' => 2,
                    'update_status_by' => Auth::user()->name
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

    public function getOrder(Request $request) 
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
            $query->where('status_id', '!=', 5)
                  ->orWhere('pembayaran', false);
        });
        
        if($user->hasRole(['dapur', 'pelayan'])) {
            $data = Order::with('status')
            ->where('status_id', '!=', 1)
            ->where('status_id', 2)
            ->orWhere('status_id', 3)
            ->orWhere('status_id', 4);
        }

        if($user->hasRole('partner')) {
            $data = Order::with('status')
            ->where('status_id', '!=', 1)
            ->where('partner', true)
            ->where(function ($query) {
                $query->where('status_id', '!=', 5)
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
            if($data->partner) return 'Rp' . number_format($data->partner_total);
            return 'Rp' . number_format($data->total);
         })
         ->addColumn('status_pembayaran', function($data) {
            if ($data->pembayaran) return '<span class="badge badge-sm bg-gradient-primary">Lunas</span>';
            else return '<span class="badge badge-sm bg-gradient-warning">Belum Lunas</span>';
         })
         ->addColumn('status', function($data) {
            $color = 'secondary';

            if($data->status_id == 4) $color = 'success';
            if($data->status_id == 5) $color = 'primary';

            return '<span class="badge badge-sm bg-gradient-' . $color . '">'. $data->status->desc .'</span>';
         })
         ->addColumn('waktu_pesan', function($data) {
            return $data->created_at;
         })
         ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->input('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($query) use ($search) {
                    $query->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('kasir', 'like', "%{$search}%")
                    ->orWhere('no_meja', 'like', "%{$search}%")
                    ->orWhere('created_at', 'like', "%{$search}%")
                    ->orWhereHas('status', function ($query) use ($search) {
                        $query->where('desc', 'like', "%{$search}%");
                    });
                    
                    if (strtolower($search) === 'lunas') {
                        $query->orWhere('pembayaran', true);
                    } elseif (strtolower($search) === 'belum lunas') {
                        $query->orWhere('pembayaran', false);
                    }
                });
            }
        })
        ->rawColumns(['#', 'action', 'status', 'status_pembayaran'])
        ->toJson(); 
    }

    public function getPesanan(Request $request, $id) 
    {
        $user = Auth::user();
        if($user->hasRole(['admin', 'kasir', 'partner'])) {
            $data = Cart::withTrashed()->with('status', 'order', 'product')->where('order_id', $id)
            ->where(function ($query) {
                $query->where('status_id', '!=', 1)
                    ->where('status_id', '!=', 5)
                    ->orWhere('pembayaran', false);
            });
        } else if($user->hasRole(['dapur', 'pelayan'])) {
            $data = Cart::withTrashed()->with('status', 'order', 'product')->where('order_id', $id)
            ->where(function ($query) {
                $query->where('status_id', 2)
                      ->orWhere('status_id', 3)
                      ->orWhere('status_id', 4);
            });
        }

        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('#', function($data) use ($user) {
            $canDelete = 'data-hapus="false"';
            if($user->can('deleteStatusTwo') && $data->status_id == 2 && !$data->pembayaran) {
                $canDelete = '';
            } else if($user->can('deleteStatusThree') && $data->status_id == 3 && !$data->pembayaran) {
                $canDelete = '';
            } else if($user->can('deleteStatusFourth') && $data->status_id == 4 && !$data->pembayaran) {
                $canDelete = '';
            }
            if((($user->can('deleteStatusTwo') && !$data->pembayaran) || $user->can('updateStatusTwo')) && $data->status_id == 2) {
                if($data->pembayaran) {
                    return '<div class="form-check">
                    <input class="form-check-input selectPesan" type="checkbox" value="' . $data->id . '"' . $canDelete . ' data-payment="true" data-status="two" id="selectPesan[]" name="selectPesan[]">
                    </div>';
                } else {
                    return '<div class="form-check">
                    <input class="form-check-input selectPesan" type="checkbox" value="' . $data->id . '" ' . $canDelete . 'data-status="two" id="selectPesan[]" name="selectPesan[]">
                    </div>';
                }
            } else if((($user->can('deleteStatusThree') && !$data->pembayaran) || $user->can('updateStatusThree')) && $data->status_id == 3) return '<div class="form-check">
            <input class="form-check-input selectPesan" type="checkbox" value="' . $data->id . '" ' . $canDelete . 'data-status="three" id="selectPesan[]" name="selectPesan[]">
            </div>';
            else if((($user->can('deleteStatusFourth') && !$data->pembayaran) || $user->can('updateStatusFourth')) && $data->status_id == 4) return '<div class="form-check">
            <input class="form-check-input selectPesan" type="checkbox" value="' . $data->id . '" ' . $canDelete . 'data-status="fourth" id="selectPesan[]" name="selectPesan[]">
            </div>';
        })
        ->addColumn('menu', function($data) {
            return $data->menu;
        })
        ->addColumn('colspan', function($data) {
            // Tentukan kondisi untuk colspan
            if($data->trashed()) return 7;
        })
        ->addColumn('status_pembayaran', function($data) use($user) {
            if ($data->pembayaran) return '<span class="badge badge-sm bg-gradient-primary">Lunas</span>';
            else {
                if($user->can('paymentAccess')) return '<a href="' . route('payment.show', $data->order_id) . '">Klik disini untuk selesaikan pembayaran</a>';
                return '<span class="badge badge-sm bg-gradient-warning">Belum Lunas</span>';
            }
        })
        ->addColumn('note', function($data) {
            if($data->note == null) return 'None';
            $note = htmlspecialchars($data->note);
            return '<span title="'.$note.'">'.(strlen($note) > 50 ? substr($note, 0, 35) . '…' : $note).'</span>';
        })
        ->addColumn('status', function($data) {
            $color = 'secondary';

            if($data->status_id == 4) $color = 'success';
            if($data->status_id == 5) $color = 'primary';

            return '<span class="badge badge-sm bg-gradient-' . $color . '">'. $data->status->desc .'</span>';
        })
        ->addColumn('waktu_pesan', function($data) {
            return $data->created_at;
        })
        ->addColumn('action', function($data) use($user) {
            $hapus = '';
            $update = '';

            if($user->can('deleteStatusTwo') && $data->status_id == 2 && !$data->pembayaran) {
                $hapus = '<a class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')"></a>';
            } else if($user->can('deleteStatusThree') && $data->status_id == 3 && !$data->pembayaran) {
                $hapus = '<a class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')"></a>';
            } else if($user->can('deleteStatusFourth') && $data->status_id == 4 && !$data->pembayaran) {
                $hapus = '<a class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')"></a>';
            }

            // if($user->hasRole(['admin', 'kasir', 'partner', 'dapur']) && $data->status_id < 3 && !$data->pembayaran) {
            //     $hapus = '<a class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')"></a>';
            // }
            // if($user->hasRole(['dapur']) && $data->status_id < 4 && !$data->pembayaran) {
            //     $hapus = '<a class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')"></a>';
            // }

            if(($data->status_id == 2 && $user->can('updateStatusTwo')) || ($data->status_id == 3 && $user->can('updateStatusThree')) || ($data->status_id == 4 && $user->can('updateStatusFourth'))) $update = '<a href="#" class="fa-solid fa-square-check text-success" style="margin-right: 10px;" onclick="modalUpdateStatus('. $data->id .')"></a>';

            if($data->trashed()) {
                $hapus = '';
                $update = '';
            }
            
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
        ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->input('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($query) use ($search) {
                    $query->where('menu', 'like', "%{$search}%")
                    ->orWhere('update_status_by', 'like', "%{$search}%")
                    ->orWhere('jumlah', 'like', "%{$search}%")
                    ->orWhereHas('status', function ($query) use ($search) {
                        $query->where('desc', 'like', "%{$search}%");
                    });
                    
                    if (strtolower($search) === 'lunas') {
                        $query->orWhere('pembayaran', true);
                    } elseif (strtolower($search) === 'belum lunas') {
                        $query->orWhere('pembayaran', false);
                    }
                });
            }
        })
        ->rawColumns(['#', 'action', 'status_pembayaran', 'note', 'status'])
        ->toJson(); 
    }

    public function hapusPesanan($id) {
        try {
            $cart = Cart::with('product')->findOrFail($id);
            $order = Order::findOrFail($cart->order_id);

            if($cart->pembayaran) return redirect()->back()->with('alert', 'info')->with('message', 'Tidak bisa menghapus pesanan yang sudah Lunas');

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

        if($data->status_id < 5) {
            $data->update([
                'status_id' => $data->status_id + 1,
                'update_status_by' => $user->name
            ]);
        }

        OrderService::checkStatusOrder($data->order->id); 

        $cekPesan = Cart::where('order_id', $data->order_id)->where('status_id', '!=', 5)->where('status_id', '!=', 1)->first();

        if($user->hasRole('dapur')) {
            $cekPesan = Cart::with('status')
            ->where('order_id', $data->order_id)
            ->where(function ($query) {
                $query->where('status_id', 2)
                      ->orWhere('status_id', 3);
            })->first();
        } else if($user->hasRole('pelayan')) {
            $cekPesan = Cart::with('status')
            ->where('order_id', $data->order_id)
            ->where('status_id', 4)->first();
        }
        
        if($cekPesan) return redirect()->back()->with('modal_alert', 'success')->with('message', 'Berhasil update status');

        $cekTrash = Cart::where('order_id', $data->order_id)->where(function ($query) {
            $query->where('status_id', '!=', 5)
            ->where('status_id', '!=', 1)
            ->orWhere('pembayaran', false);
        })->first();        
        if(!$cekTrash) {
            $cekTrash = Cart::onlyTrashed()->where('order_id', $data->order_id)->get();

            foreach ($cekTrash as $trash) {
                $trash->forceDelete();
            }
        }

        return redirect()->route('order.index')->with('modal_alert', 'success')->with('message', 'Berhasil update status');
    }

    public function updateOrDelete(Request $request) {
        // dd($request);
        if(!$request->selectPesan) return redirect()->back()->with('alert', 'info')->with('message', 'Tidak ada pesanan yang terpilih');
        if($request->action == 'update') {
            foreach ($request->selectPesan as $id) {
                $data = Cart::with('order')->findOrFail($id);
                $user = Auth::user();

                if($data->status_id < 5) {
                    $data->update([
                        'status_id' => $data->status_id + 1
                    ]);
                }

                OrderService::checkStatusOrder($data->order->id); 

                $cekPesan = Cart::where('order_id', $data->order_id)->where('status_id', '!=', 5)->where('status_id', '!=', 1)->first();

                if($user->hasRole('dapur')) {
                    $cekPesan = Cart::with('status')
                    ->where('order_id', $data->order_id)
                    ->where(function ($query) {
                        $query->where('status_id', 2)
                              ->orWhere('status_id', 3);
                    })->first();
                } else if($user->hasRole('pelayan')) {
                    $cekPesan = Cart::with('status')
                    ->where('order_id', $data->order_id)
                    ->where('status_id', 4)->first();
                }
            }
            if($cekPesan) return redirect()->back()->with('modal_alert', 'success')->with('message', 'Berhasil update status');

            $cekTrash = Cart::where('order_id', $data->order_id)->where(function ($query) {
                $query->where('status_id', '!=', 5)
                ->where('status_id', '!=', 1)
                ->orWhere('pembayaran', false);
            })->first();

            if(!$cekTrash) {
                $cekTrash = Cart::onlyTrashed()->where('order_id', $data->order_id)->get();
                
                foreach ($cekTrash as $trash) {
                    $trash->forceDelete();
                }
            }

            return redirect()->route('order.index')->with('modal_alert', 'success')->with('message', 'Berhasil update status');
        } else if($request->action == 'hapus') {
            try {
                if($request->payment) return redirect()->back()->with('alert', 'info')->with('message', 'Tidak bisa menghapus pesanan yang sudah Lunas');
                foreach($request->selectPesan as $id) {
                    $cart = Cart::with('product')->findOrFail($id);
                    $order = Order::findOrFail($cart->order_id);

                    if($cart->pembayaran) return redirect()->back()->with('alert', 'info')->with('message', 'Tidak bisa menghapus pesanan yang sudah Lunas');

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
