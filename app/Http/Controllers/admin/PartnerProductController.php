<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductPartnerRequest;
use App\Models\Category;
use App\Models\PartnerProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class PartnerProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['categories'] = Category::get();

        // dd($data);
        return view('admin.product.partner.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $productIdArr = PartnerProduct::get(['product_id'])->pluck('product_id')->toArray();
        // dd($productIdArr);
        $data['products'] = Product::whereNotIn('id', $productIdArr)->get();
        $data['category'] = Category::get();
        // dd($data);
        return view('admin.product.partner.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductPartnerRequest $request)
    {
        // dd($request);

        PartnerProduct::create([
            'user_id' => Auth::user()->id,
            'product_id' => $request->product_id,
            'up_price' => $request->upHarga
        ]);

        return redirect()->route('partnerProduct')->with('alert', 'success')->with('message', 'Berhasil menambahkan product');
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = PartnerProduct::findOrFail($id);

        $data->delete();

        return redirect()->back()->with('alert', 'success')->with('message', 'Product berhasil dihapus');
    }

    public function getPartnerProduct(Request $request) {
        $data = PartnerProduct::with('product.category')->where('user_id', Auth::user()->id);

        // Untuk melihat semua data dalam request
        // Log::info('Request Data: ', $request->all());
        if ($request->filled('category_id')) {
            $data->whereHas('product', function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            });
        }


        // dd($data);
        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('name', function($data) {
            return $data->product->name;
         })
         ->addColumn('category', function($data) {
            return $data->product->category->name;
         })
         ->addColumn('jumlah', function($data) {
            return $data->product->jumlah;
         })
         ->addColumn('modal', function($data) {
            return $data->modal;
         })
         ->addColumn('harga', function($data) {
            return 'Rp' . number_format($data->product->harga + $data->up_price);
         })
         ->addColumn('action', function($data) {
            return '
            <a href="' . route('product.partner.edit', $data->id) . '">
                <i class="fa-solid fa-pen-to-square text-secondary"></i>
            </a>
            <button class="cursor-pointer fas fa-trash text-danger" onclick="submit('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>
            <form id="form_'. $data->id .'" action="' . route('product.partner.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';
         })
         ->rawColumns(['action'])
        ->toJson(); 
    }
}
