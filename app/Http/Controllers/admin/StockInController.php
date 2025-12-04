<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\StockIn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class StockInController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.stockIn.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Stock::all();
        return view('admin.stockIn.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|exists:stocks,id',
            'qty' => 'required|integer|min:1',
            'date' => 'required|date',
        ]);

        $stock = Stock::findOrFail($request->name);
        if($request->qty > $stock->qty) return redirect()->back()->with('alert', 'info')->with('message', 'Stok tidak mencukupi untuk penjualan ini.');

        $stock->increment('qty', $request->qty);

        // Simpan record penjualan
        StockIn::create([
            'stock_id' => $request->name,
            'qty' => $request->qty,
            'created_at' => $request->date,
        ]);

        return redirect()->route('stock-in.index')->with('alert', 'success')->with('message', 'Stock In added successfully.');
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
        $record = StockIn::with('stock')->findOrFail($id);
        $products = Stock::all();
        return view('admin.stockIn.edit', compact('record', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|exists:stocks,id',
            'qty' => 'required|integer|min:1',
            'date' => 'required|date',
        ]);

        $stock = Stock::findOrFail($request->name);
        $stockIn = StockIn::findOrFail($id);

        if(($stock->qty + $request->qty) < $stockIn->qty) {
            return redirect()->back()->with('alert', 'info')->with('message', 'Stok tidak mencukupi untuk penyesuaian penambahan stock ini.');
        }
        $stock->decrement('qty', $stockIn->qty);
        $stock->increment('qty', $request->qty);

        $stockIn->update([
            'stock_id' => $request->name,
            'qty' => $request->qty,
            'date' => $request->date,
        ]);

        return redirect()->route('stock-in.index')->with('alert', 'success')->with('message', 'Stock In updated successfully.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $record = StockIn::findOrFail($id);
        $record->delete();
        $stock = Stock::findOrFail($record->stock_id);

        if($stock->qty < $record->qty) return redirect()->route('stock-in.index')->with('alert', 'info')->with('message', 'Stok tidak mencukupi untuk penyesuaian stock ini.');
        $stock->decrement('qty', $record->qty);

        return redirect()->route('stock-in.index')->with('alert', 'success')->with('message', 'Stock In deleted successfully.');

    }

    public function getStockIn(Request $request)
    {
        Log::info('Request Data: ', $request->all());

        $user = auth()->user();

        // if ($request->filled('dateRange')) {
        //     $dateRange = $request->dateRange;

        //     // Split berdasarkan separator " - "
        //     $dates = explode(' - ', $dateRange);

        //     if (count($dates) === 2) {
        //         try {
        //             // Parse tanggal dari format DD/MM/YYYY
        //             $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
        //             $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
        //         } catch (\Exception $e) {
        //             Log::error('Error parsing date range: ' . $e->getMessage());
        //             // Fallback ke default (hari ini)
        //             $startDate = Carbon::today();
        //             $endDate = Carbon::today()->endOfDay();
        //         }
        //     }
        // }

        // $data = StockIn::with('stock')->whereBetween('created_at', [$startDate, $endDate])->orderBy('created_at', 'desc');
        $data = StockIn::with('stock')->orderBy('created_at', 'desc');

        return DataTables::of($data)
        ->addColumn('product', function($data) {
            return $data->stock ? $data->stock->name : 'Produk tidak ditemukan';
         })
        ->addColumn('qty', function($data) {
            return $data->qty;
        })
        ->addColumn('date', function($data) {
            return $data->created_at;
        })
        ->addColumn('action', function($data) use ($user) {
            $hiddenInput = '<input type="hidden" name="ids[]" value="' . $data->id . '">';
            $update = '';
            $delete = '';
            if($user->can('stockInUpdate')) $update = '<a href="' . route('stock-in.edit', $data->id) . '"><i class="fa-solid fa-pen-to-square text-secondary"></i></a>';
            if($user->can('stockInDelete')) $delete = '<button class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>';
            return  $hiddenInput . $update . $delete . '
            <form id="form_'. $data->id .'" action="' . route('stock-in.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';
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
        ->rawColumns(['action'])
        ->toJson();
    }
}
