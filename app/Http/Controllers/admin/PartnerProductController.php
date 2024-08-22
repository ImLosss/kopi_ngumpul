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
        $data['categories'] = Category::get();
        // dd($data);
        return view('admin.product.partner.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductPartnerRequest $request)
    {   
        // dd($request);
        if (empty($request->selectedProducts)) return redirect()->back()->with('alert', 'info')->with('message', 'Pilih menu terlebih dahulu');

        foreach ($request->selectedProducts as $productId) {
            PartnerProduct::create([
                'user_id' => Auth::user()->id,
                'product_id' => $productId,
                'up_price' => $request->upHarga
            ]);
        }

        return redirect()->route('partnerProduct')->with('alert', 'success')->with('message', 'Berhasil menambahkan produk');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // return 'tess';
        $productIdArr = PartnerProduct::get(['product_id'])->pluck('product_id')->toArray();
        // dd($productIdArr);
        $data['products'] = Product::whereNotIn('id', $productIdArr)->get();
        $data['category'] = Category::get();
        $data['data'] = PartnerProduct::with('product')->findOrFail($id);
        // dd($data);
        return view('admin.product.partner.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = PartnerProduct::findOrFail($id);

        $data->update([
            'up_price' => $request->upHarga
        ]);

        return redirect()->route('partnerProduct')->with('alert', 'success')->with('message', 'Berhasil update Produk');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = PartnerProduct::findOrFail($id);

        $data->delete();

        return redirect()->back()->with('alert', 'success')->with('message', 'Produk berhasil dihapus');
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
            return 'Rp' . number_format($data->modal);
         })
         ->addColumn('harga', function($data) {
            return 'Rp' . number_format($data->product->harga + $data->up_price);
         })
         ->addColumn('action', function($data) {
            return '
            <a href="' . route('partnerProduct.edit', $data->id) . '">
                <i class="fa-solid fa-pen-to-square text-secondary"></i>
            </a>
            <button class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>
            <form id="form_'. $data->id .'" action="' . route('product.partner.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';
         })
         ->rawColumns(['action'])
        ->toJson(); 
    }

    public function getProductByCategory($id) {
        
        $productIds = Product::whereHas('partnerProduct')->where('category_id', $id)->pluck('id');

        $data = Product::where('category_id', $id)->whereNotIn('id', $productIds)->get();

        $productList = [];
        if ($data->isNotEmpty()) {
            foreach ($data as $product) {
                $productList[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'harga' => $product->harga,
                    'modal' => $product->modal,
                    'stock' => $product->jumlah
                ];
            }
        } else return false;
        
        return response()->json([
            'productList' => $productList
        ]);
    }
}
