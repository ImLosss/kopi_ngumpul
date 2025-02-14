<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.product.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = Category::all();
        return view('admin.product.create', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request);
        $data = $request->validate([
            'name' => 'required',
            'category_id' => 'required|exists:categories,id'
        ]);

        Product::create($data);

        return redirect()->route('product')->with('alert', 'success')->with('message', 'Produk berhasil ditambahkan');
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
        $data['categories'] = Category::all();
        $data['product'] = Product::findOrFail($id);

        return view('admin.product.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name' => 'required',
            'category_id' => 'required|exists:categories,id'
        ]);

        $product = Product::findOrFail($id);

        $product->update($data);

        return redirect()->route('product')->with('alert', 'success')->with('message', 'Produk berhasil diubah');
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
        $user = Auth::user();

        // dd($data);
        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('name', function($data) {
            return $data->name;
        })
        ->addColumn('category', function($data) {
            return $data->category->name;
        })
        ->addColumn('action', function($data) use ($user) {
            $update = '';
            $delete = '';
            if($user->can('productUpdate')) $update = '<a href="' . route('product.edit', $data->id) . '"><i class="fa-solid fa-pen-to-square text-secondary"></i></a>';
            if($user->can('productDelete')) $delete = '<button class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>';
            return $update . $delete . '
            <form id="form_'. $data->id .'" action="' . route('product.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';
        })
        // ->filter(function ($query) use ($request) {
        //     if ($request->has('search') && $request->input('search.value')) {
        //         $search = $request->input('search.value');
        //         $query->where(function ($query) use ($search) {
        //             $query->where('jumlah_gr', 'like', "%{$search}%")
        //             ->orWhere('name', 'like', "%{$search}%");
        //         });
        //     }
        // })
        ->rawColumns(['action'])
        ->toJson();
    }
}
