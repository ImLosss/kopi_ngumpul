<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
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

        // Untuk melihat semua data dalam request
        // Log::info('Request Data: ', $request->all());
        if ($request->filled('category_id')) {
            $data->with('category')->where('category_id', $request->category_id);
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
         ->rawColumns(['rate', 'action'])
        ->toJson();
    }
}
