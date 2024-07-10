<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:productAccess');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['categories'] = Category::get();

        // dd($data);
        return view('admin.product.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = Category::get();
        // dd($data);
        return view('admin.product.create', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        // dd($request);
        Product::create([
            'name' => $request->name,
            'jumlah' => $request->stock,
            'modal' => $request->modal,
            'harga' => $request->harga,
            'category_id' => $request->kategori
        ]);

        return redirect()->route('product')->with('alert', 'success')->with('message', 'Data stored Succesfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data['product'] = Product::findOrFail($id);
        $data['categories'] = Category::get();
        // dd($data);
        return view('admin.product.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, string $id)
    {
        try {
            $data = Product::findOrFail($id);
            $data->update([
                'name' => $request->name,
                'jumlah' => $request->stock,
                'modal' => $request->modal,
                'harga' => $request->harga,
                'category_id' => $request->kategori
            ]);

            return redirect()->route('product')->with('alert', 'success')->with('message', 'Data berhasil diubah');
        } catch (\Throwable $e) {
            return redirect()->route('product')->with('alert', 'error')->with('message', 'Something Error');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getProduct(Request $request) {
        $data = Product::with('category');
        $productRating = Product::with('category')->get();
        $date = Carbon::now()->subWeek();
        $penjualan = [];
        $maxJual = 0;
        // Untuk melihat semua data dalam request
        // Log::info('Request Data: ', $request->all());
        if ($request->filled('category_id')) {
            $data->with('category')->where('category_id', $request->category_id);
            $productRating = Product::with('category')->where('category_id', $request->category_id)->get();
        }

        foreach ($productRating as $product) {
            $totaljual = Cart::where('product_id', $product->id)->where('pembayaran', true)->where('created_at', '>=', $date)->sum('jumlah');

            if($totaljual > 0) {
                $ratingChart[] = ['name' => $product->name, 'penjualan' => $totaljual];
                $penjualan[] = $totaljual;
            }
        }

        rsort($penjualan, SORT_NUMERIC);

        try {
            $maxJual = max($penjualan);
        } catch(\Throwable $e) {

        }


        // dd($data);
        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('name', function($data) {
            return $data->name;
        })
        ->addColumn('category', function($data) {
            return $data->category->name;
        })
        ->addColumn('jumlah', function($data) {
            return $data->jumlah;
        })
        ->addColumn('modal', function($data) {
            return $data->modal;
        })
        ->addColumn('harga', function($data) {
            return $data->harga;
        })
        ->addColumn('rate', function($data) use($maxJual, $date) {
            $totaljual = Cart::where('product_id', $data->id)->where('pembayaran', true)->where('created_at', '>=', $date)->sum('jumlah');
            $rating = $this->calculateRating($totaljual, $maxJual);

            $color = 'bg-gradient-success';
            if($rating < 30) $color = 'bg-gradient-danger';
            else if($rating < 60) $color = 'bg-gradient-warning';
            else if($rating < 80) $color = 'bg-gradient-info';

            return '<div class="d-flex align-items-center">
                <span class="me-2 text-xs font-weight-bold">' . $rating . '%</span>
                <div>
                    <div class="progress">
                        <div class="progress-bar ' . $color . '" role="progressbar" aria-valuenow="' . $rating . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $rating . '%;"></div>
                    </div>
                </div>
            </div>';
        })
        ->addColumn('action', function($data) {
            return '
            <a href="' . route('product.edit', $data->id) . '">
                <i class="fa-solid fa-pen-to-square text-secondary"></i>
            </a>
            <button class="cursor-pointer fas fa-trash text-danger" onclick="submit('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>
            <form id="form_'. $data->id .'" action="' . route('product.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';
        })
        ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->input('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($query) use ($search) {
                    $query->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('harga', 'like', "%{$search}%")
                    ->orWhere('rate', 'like', "%{$search}%")
                    ->orWhere('modal', 'like', "%{$search}%");
                });
            }
        })
         ->rawColumns(['rate', 'action'])
        ->toJson();
    }

    private function calculateRating($total_penjualan, $maxPenjualan)
    {
        // Jika maxPenjualan adalah 0, atur rating ke 0 untuk menghindari pembagian oleh nol
        if ($maxPenjualan == 0) {
            return 0;
        }
        
        // Hitung persentase
        $rating = ($total_penjualan / $maxPenjualan) * 100;

        $rounded = round($rating, 0);
        // Jika hasil pembulatan adalah bilangan bulat, kembalikan sebagai integer
        return ($rounded == intval($rounded)) ? intval($rounded) : $rounded;
    }
}
