<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscountRequest;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DiscountController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:discountAccess');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.discount.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['categories'] = Category::with('product')->get();

        return view('admin.discount.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DiscountRequest $request)
    {
        try {
            if(empty($request->selectedProducts)) return redirect()->back()->with('alert', 'info')->with('message', 'Tidak ada menu yang terpilih!');

            foreach ($request->selectedProducts as $id) {
                Discount::create([
                    'name' => $request->disc_name,
                    'percent' => $request->discount,
                    'product_id' => $id,
                    'status' => $request->status
                ]);
            }

            return redirect()->route('discount')->with('alert', 'success')->with('message', 'Data berhasil ditambahkan');
        } catch (\Throwable $e) {
            return redirect()->route('discount')->with('alert', 'error')->with('message', 'Something error!');
        }
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
        $data['categories'] = Category::with('product')->get();
        $data['diskon'] = Discount::findOrFail($id);

        return view('admin.discount.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $data = Discount::findOrFail($id);

            $data->update([
                'product_id' => $request->menu,
                'name' => $request->disc_name,
                'percent' => $request->discount,
                'status' => $request->status
            ]);

            return redirect()->route('discount')->with('alert', 'success')->with('message', 'Data berhasil diubah');
        } catch (\Throwable $e) {
            return redirect()->route('discount')->with('alert', 'error')->with('message', 'Something Error!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Discount::findOrFail($id);

        $data->delete();

        return redirect()->route('discount')->with('alert', 'success')->with('message', 'Data berhasil dihapus');
    }

    public function getDiscount(Request $request) {
        $data = Discount::with('product');

        // dd($data);
        return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('menu', function($data) {
            return $data->product->name;
        })
        ->addColumn('diskon', function($data) {
            return $data->percent;
        })
        ->addColumn('action', function($data) {
            return '
            <a href="' . route('discount.edit', $data->id) . '">
                <i class="fa-solid fa-pen-to-square text-secondary"></i>
            </a>
            <button class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>
            <form id="form_'. $data->id .'" action="' . route('discount.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';
        })
        ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->input('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
                    
                });
            }
        })
        ->rawColumns(['rate', 'action'])
        ->toJson(); 
    }

    public function getMenu($id) {
        $data = Product::where('category_id', $id)->get();

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
