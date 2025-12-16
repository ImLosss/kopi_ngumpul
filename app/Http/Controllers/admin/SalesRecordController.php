<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\StockOut;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class SalesRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.record.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Stock::all();
        return view('admin.record.create', compact('products'));
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

        $stock->decrement('qty', $request->qty);

        // Simpan record penjualan
        StockOut::create([
            'stock_id' => $request->name,
            'qty' => $request->qty,
            'total_price' => $stock->price * $request->qty,
        ]);

        return redirect()->route('sales-record.index')->with('alert', 'success')->with('message', 'Sales record added successfully.');
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
        $record = StockOut::with('stock')->findOrFail($id);
        $products = Stock::all();
        return view('admin.record.edit', compact('record', 'products'));
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
        $record = StockOut::findOrFail($id);

        if($request->qty > ($stock->qty + $record->qty)) {
            return redirect()->back()->with('alert', 'info')->with('message', 'Stok tidak mencukupi untuk penjualan ini.');
        }
        $stock->increment('qty', $record->qty);
        $stock->decrement('qty', $request->qty);

        $record->update([
            'stock_id' => $request->name,
            'qty' => $request->qty,
            'date' => $request->date,
            'total_price' => $stock->price * $request->qty,
        ]);

        return redirect()->route('sales-record.index')->with('alert', 'success')->with('message', 'Sales record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $record = StockOut::findOrFail($id);
        $record->delete();
        $stock = Stock::findOrFail($record->stock_id);
        $stock->increment('qty', $record->qty);

        return redirect()->route('sales-record.index')->with('alert', 'success')->with('message', 'Sales record deleted successfully.');
    }

    public function getSalesRecord(Request $request)
    {
        Log::info('Request Data: ', $request->all());

        $user = auth()->user();

        // Default: today
        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        if ($request->filled('dateRange')) {
            $dateRange = $request->dateRange;

            // Split berdasarkan separator " - "
            $dates = explode(' - ', $dateRange);

            if (count($dates) === 2) {
                try {
                    // Parse tanggal dari format DD/MM/YYYY
                    $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
                    $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
                } catch (\Exception $e) {
                    Log::error('Error parsing date range: ' . $e->getMessage());
                    // Fallback ke default (hari ini)
                    $startDate = Carbon::today();
                    $endDate = Carbon::today()->endOfDay();
                }
            }
        }

        $data = StockOut::with('stock')->whereBetween('created_at', [$startDate, $endDate])->orderBy('created_at', 'desc');

        return DataTables::of($data)
        ->addColumn('product', function($data) {
            return $data->stock ? $data->stock->name : 'Produk tidak ditemukan';
         })
        ->addColumn('qty', function($data) {
            return $data->qty;
        })
        ->addColumn('total_price', function($data) {
            return 'Rp ' . number_format($data->total_price, 0, ',', '.');
        })
        ->addColumn('date', function($data) {
            return $data->created_at;
        })
        ->addColumn('action', function($data) use ($user) {
            $hiddenInput = '<input type="hidden" name="ids[]" value="' . $data->id . '">';
            $update = '';
            $delete = '';
            if($user->can('salesRecordUpdate')) $update = '<a href="' . route('sales-record.edit', $data->id) . '"><i class="fa-solid fa-pen-to-square text-secondary"></i></a>';
            if($user->can('salesRecordDelete')) $delete = '<button class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>';
            return  $hiddenInput . $update . $delete . '
            <form id="form_'. $data->id .'" action="' . route('sales-record.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';
        })
        ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->input('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($query) use ($search) {
                    $query->where('qty', 'like', "%{$search}%")
                    // ->orWhere('created_at', 'like', "%{$search}%")
                    ->orWhereHas('stock', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
                });
            }
        })
        ->rawColumns(['action'])
        ->toJson();
    }

    public function print(Request $request)
    {
        // Default: today
        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        $dateRangeLabel = $request->input('dateRange');
        if ($request->filled('dateRange')) {
            $dates = explode(' - ', (string) $request->input('dateRange'));

            if (count($dates) === 2) {
                try {
                    $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
                    $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
                } catch (\Exception $e) {
                    Log::error('Error parsing date range (print): ' . $e->getMessage());
                }
            }
        }

        $search = trim((string) $request->input('search', ''));

        $query = StockOut::with('stock')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('qty', 'like', "%{$search}%")
                    ->orWhereHas('stock', function ($stockQuery) use ($search) {
                        $stockQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $records = $query->get();

        return view('admin.record.print', [
            'records' => $records,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dateRangeLabel' => $dateRangeLabel,
            'search' => $search,
        ]);
    }
}
