<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\StockRequest;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class StockController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:stockAccess');
        $this->middleware('permission:stockAdd')->only(['create', 'store']);
        $this->middleware('permission:stockDelete')->only('destroy');
        $this->middleware('permission:stockUpdate')->only(['edit', 'update']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.stock.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.stock.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StockRequest $request)
    {
        // dd($request);
        Stock::create([
            'name' => $request->name
        ]);

        return redirect()->route('stock')->with('alert', 'success')->with('message', 'Bahan berhasil ditambahkan');
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
        $data = Stock::findOrFail($id);
        // dd($data);
        return view('admin.stock.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StockRequest $request, string $id)
    {
        try {
            $data = Stock::findOrFail($id);
            $data->update([
                'name' => $request->name,
                'jumlah_gr' => $request->jumlah_gr
            ]);

            return redirect()->route('stock')->with('alert', 'success')->with('message', 'Stock berhasil diubah');
        } catch (\Throwable $e) {
            return redirect()->route('stock')->with('alert', 'error')->with('message', 'Terjadi kesalahan');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data = Stock::findOrFail($id);

            $data->delete();

            return redirect()->route('stock')->with('alert', 'success')->with('message', 'Produk berhasil dihapus');
        } catch (\Throwable $e) {
            return redirect()->route('stock')->with('alert', 'error')->with('message', 'Terjadi kesalahan');
        }
    }

    public function getStock(Request $request) {
        $data = Stock::query();
        $user = Auth::user();

        // dd($data);
        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('name', function($data) {
            return $data->name;
        })
        ->addColumn('jumlah_gr', function($data) {
            if($data->jumlah_gr < 300) return "<div class='text-danger'>" . $data->jumlah_gr . "</div>";
            return $data->jumlah_gr;
        })
        ->addColumn('action', function($data) use ($user) {
            $update = '';
            $delete = '';
            if($user->can('stockUpdate')) $update = '<a href="' . route('stock.edit', $data->id) . '"><i class="fa-solid fa-pen-to-square text-secondary"></i></a>';
            if($user->can('stockDelete')) $delete = '<button class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>';
            return $update . $delete . '
            <form id="form_'. $data->id .'" action="' . route('stock.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';
        })
        ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->input('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($query) use ($search) {
                    $query->where('jumlah_gr', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
                });
            }
        })
        ->rawColumns(['action', 'jumlah_gr'])
        ->toJson();
    }
}
