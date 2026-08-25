<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class StockController extends Controller
{
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
        $categories = Category::all();
        return view('admin.stock.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:stocks,name',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|integer|min:1000',
            'selling_price' => 'required|integer|min:1000|gt:price',
            'unit' => 'required|string',
        ]);
        Stock::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'selling_price' => $request->selling_price,
            'unit' => $request->unit,
        ]);

        return redirect()->route('stock.index')->with('alert', 'success')->with('message', 'Stock added Succesfully');
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
        $data = Stock::findOrFail($id);
        $categories = Category::all();

        return view('admin.stock.edit', compact('data', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|unique:stocks,name,'.$id,
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|integer|min:1000',
            'selling_price' => 'required|integer|min:1000|gt:price',
            'unit' => 'required|string',
        ]);
        $data = Stock::findOrFail($id);
        $data->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'selling_price' => $request->selling_price,
            'unit' => $request->unit
        ]);

        return redirect()->route('stock.index')->with('alert', 'success')->with('message', 'Stock updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data = Stock::findOrFail($id);

            $data->delete();

            return redirect()->route('stock.index')->with('alert', 'success')->with('message', 'Produk berhasil dihapus');
        } catch (\Throwable $e) {
            return redirect()->route('stock.index')->with('alert', 'error')->with('message', 'Terjadi kesalahan');
        }
    }

    public function print(Request $request)
    {
        // Ambil semua barang beserta rincian batch-nya
        $query = Stock::with(['stockIns' => function($q) {
            $q->where('qty_remaining', '>', 0)->orderBy('created_at', 'asc');
        }]);

        // Jika ada filter pencarian (opsional)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $stocks = $query->get();
        $search = $request->search ?? null;

        // Arahkan ke file blade print-stock.blade.php
        return view('admin.stock.print', compact('stocks', 'search'));
    }

    public function getStock(Request $request) {
        // 1. Ambil data dengan relasi stockIns (Hanya ambil yang sisa stoknya > 0)
        $data = Stock::with(['category', 'stockIns' => function($query) {
            $query->where('qty_remaining', '>', 0)->orderBy('created_at', 'asc');
        }])->select('stocks.*');

        $user = Auth::user();

        return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('name', function($data) {
            return $data->name;
        })
        ->addColumn('category', function($data) {
            return $data->category ? $data->category->name : 'Kategori tidak ditemukan';
        })
        ->addColumn('unit', function($data) {
            return $data->unit;
        })

        // -- PERUBAHAN PADA KOLOM BATCHES --
        ->addColumn('batches', function($data) {
            if ($data->stockIns->isEmpty()) {
                return '<span class="badge bg-secondary text-xxs">Kosong</span>';
            }

            // Buat array kosong untuk menampung pengelompokan batch
            $groupedBatches = [];

            // Looping semua batch dan gabungkan qty jika kode batch-nya sama
            foreach ($data->stockIns as $batch) {
                if ($batch->qty_remaining > 0) { // Pastikan lagi yang 0 tidak ikut
                    $batchCode = $batch->batch_code ?? 'Tanpa Kode';

                    // Jika batch code belum ada di array, buat baru. Jika sudah ada, tambahkan qty-nya.
                    if (!isset($groupedBatches[$batchCode])) {
                        $groupedBatches[$batchCode] = 0;
                    }
                    $groupedBatches[$batchCode] += $batch->qty_remaining;
                }
            }

            // Jika setelah difilter ternyata kosong
            if (empty($groupedBatches)) {
                return '<span class="badge bg-secondary text-xxs">Kosong</span>';
            }

            // Render HTML Badge dari data yang sudah dikelompokkan
            $html = '';
            foreach ($groupedBatches as $code => $totalQty) {
                $html .= '<span class="badge bg-info text-xxs mb-1" style="display:inline-block;">'
                    . $code . ' (Sisa: ' . $totalQty . ')'
                    . '</span><br>';
            }

            return $html;
        })
        // ----------------------------------

        ->addColumn('action', function($data) use ($user) {
            $hiddenInput = '<input type="hidden" name="ids[]" value="' . $data->id . '">';
            $update = '';
            $delete = '';
            if($user->can('stockUpdate')) $update = '<a href="' . route('stock.edit', $data->id) . '"><i class="fa-solid fa-pen-to-square text-secondary"></i></a>';
            if($user->can('stockDelete')) $delete = '<button class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>';

            return  $hiddenInput . $update . $delete . '
            <form id="form_'. $data->id .'" action="' . route('stock.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';
        })
        ->addColumn('price', function($data) {
            return 'Rp' . number_format($data->price, 0, ',', '.');
        })
        ->addColumn('selling_price', function($data) {
            return 'Rp' . number_format($data->selling_price, 0, ',', '.');
        })
        ->rawColumns(['action', 'batches'])
        ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->input('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                    ->orWhere('qty', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
                });
            }
        })
        ->toJson();
    }
}
