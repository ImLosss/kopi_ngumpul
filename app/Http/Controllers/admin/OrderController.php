<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        //
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

        return redirect()->route('pesanan.order-list')->with('alert', 'success')->with('message', 'Berhasil checkout');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function orderList() 
    {
        return view('admin.pesanan.index');
    }

    public function getOrder() 
    {
        $data = Order::with('status');

        // Untuk melihat semua data dalam request
        // Log::info('Request Data: ', $request->all());
        // if ($request->filled('category_id')) {
        //     $data->with('category')->where('category_id', $request->category_id);
        // }


        // dd($data);
        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('#', function($data) {
            return '<a href="">Klik disini untuk lihat Pesanan</a>';
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
        //  ->addColumn('action', function($data) {
        //     return '
        //     <a href="' . route('product.edit', $data->id) . '">
        //         <i class="fa-solid fa-pen-to-square text-secondary"></i>
        //     </a>
        //     <button class="cursor-pointer fas fa-trash text-danger" onclick="submit('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>
        //     <form id="form_'. $data->id .'" action="' . route('user.destroy', $data->id) . '" method="POST" class="inline">
        //         ' . csrf_field() . '
        //         ' . method_field('DELETE') . '
        //     </form>';
        //  })
        ->rawColumns(['#', 'action'])
        ->toJson(); 
    }
}
