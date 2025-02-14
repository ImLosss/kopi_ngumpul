<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\IngredientTransaction;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class IngredientTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.ingredient.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = Stock::all();
        return view('admin.ingredient.create', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // dd($request);
            $data = $request->validate([
                'stock_id' => 'required|exists:stocks,id',
                'gram_ml' => 'required|numeric',
                'modal' => 'required|numeric'
            ]);

            $stock = Stock::findOrFail($data['stock_id']);

            $data['name'] = $stock->name;
            $data['type'] = 'masuk';

            $stock->update([
                'jumlah_gr' => $stock->jumlah_gr + $data['gram_ml']
            ]);

            IngredientTransaction::create($data);

            return redirect()->route('ingredient')->with('alert', 'success')->with('message', 'Bahan berhasil ditambahkan');
        } catch (\Throwable $e) {
            return redirect()->route('ingredient')->with('alert', 'error')->with('message', 'Terjadi kesalahan');
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
        $data = IngredientTransaction::findOrFail($id);
        if($data->stock_id == null) return redirect()->route('ingredient')->with('alert', 'info')->with('message', 'Data tidak bisa dihapus');

        $stock = Stock::findOrFail($data->stock_id);

        if($stock->jumlah_gr < $data->gram_ml) return redirect()->route('ingredient')->with('alert', 'info')->with('message', "Jumlah Gram $stock->name kurang");

        $stock->update([
            'jumlah_gr' => $stock->jumlah_gr - $data->gram_ml
        ]);
        $data->delete();

        return redirect()->route('ingredient')->with('alert', 'success')->with('message', 'Bahan berhasil dihapus');
    }

    public function getIngredientTransaction(Request $request) {
        $data = IngredientTransaction::query();
        $user = Auth::user();

        // dd($data);
        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('name', function($data) {
            return $data->stock?->name ?? $data->name;
        })
        ->addColumn('gram_ml', function($data) {
            return $data->gram_ml;
        })
        ->addColumn('modal', function($data) {
            return $data->modal;
        })
        ->addColumn('type', function($data) {
            return $data->type;
        })
        ->addColumn('created_at', function($data) {
            return $data->created_at;
        })
        ->addColumn('action', function($data) use ($user) {
            $delete = '';
            if($user->can('ingredientTransactionDelete')) $delete = '<button class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>';
            return $delete . '
            <form id="form_'. $data->id .'" action="' . route('ingredient.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';
        })
        ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->input('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                    ->orWhere('gram_ml', 'like', "%{$search}%")
                    ->orWhere('modal', 'like', "%{$search}%")
                    ->orWhere('created_at', 'like', "%{$search}%");
                });
            }
        })
        ->rawColumns(['action'])
        ->toJson();
    }
}
