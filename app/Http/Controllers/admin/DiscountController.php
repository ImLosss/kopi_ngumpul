<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscountRequest;
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
        $data = Product::get();

        return view('admin.discount.create', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DiscountRequest $request)
    {
        try {
            Discount::create([
                'name' => $request->disc_name,
                'percent' => $request->discount,
                'product_id' => $request->menu
            ]);

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
        $data = Discount::findOrFail($id);

        $data->delete();

        return redirect()->route('discount')->with('alert', 'success')->with('message', 'Data berhasil dihapus');
    }

    public function getDiscount() {
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
            <button class="cursor-pointer fas fa-trash text-danger" onclick="submit('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>
            <form id="form_'. $data->id .'" action="' . route('discount.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';
        })
        ->filterColumn('menu', function($data, $keyword) {
            // Meng-handle search untuk kolom role
            $data->whereHas('product', function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            });
        })
        ->rawColumns(['rate', 'action'])
        ->toJson(); 
    }
}
