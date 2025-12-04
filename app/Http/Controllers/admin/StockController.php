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
        ]);
        Stock::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'price' => $request->price
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

        return view('admin.stock.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|unique:stocks,name,'.$id,
            'qty' => 'required|integer|min:0',
            'price' => 'required|integer|min:1000',
        ]);
        $data = Stock::findOrFail($id);
        $data->update([
            'name' => $request->name,
            'qty' => $request->qty,
            'price' => $request->price
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

    public function getStock(Request $request) {
        $data = Stock::query();
        $user = Auth::user();

        // dd($data);
        return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('name', function($data) {
            return $data->name;
        })
        ->addColumn('category', function($data) {
            return $data->category ? $data->category->name : 'Kategori tidak ditemukan';
        })
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
        ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->input('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($query) use ($search) {
                    $query->where('qty', 'like', "%{$search}%")
                    ->orWhere('created_at', 'like', "%{$search}%")
                    ->orWhereHas('stock', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
                });
            }
        })
        ->toJson();
    }
}
