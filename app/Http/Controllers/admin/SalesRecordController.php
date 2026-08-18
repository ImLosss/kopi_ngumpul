<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\StockOut;
use App\Models\StockIn;
use App\Models\StockOutDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Auth;

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
        // 1. Validasi inputan (Tambahkan customer_name & invoice)
        $request->validate([
            'name'          => 'required|exists:stocks,id', // 'name' di sini adalah stock_id
            'qty'           => 'required|integer|min:1',
            'date'          => 'required|date',
            'customer_name' => 'required|string|max:255',
            'invoice'       => 'nullable|string|max:255', // Nullable agar bisa dibuat otomatis oleh sistem
        ]);

        try {
            DB::transaction(function () use ($request) {
                $stock = Stock::findOrFail($request->name);

                // 2. Cek ketersediaan stok
                if ($request->qty > $stock->qty) {
                    throw new Exception('Stok tidak mencukupi untuk penjualan ini.');
                }

                // 3. Buat nomor invoice otomatis jika dikosongkan (Contoh: INV-20260818-ABC)
                $invoice = $request->invoice ?? 'INV-' . date('Ymd', strtotime($request->date)) . '-' . strtoupper(Str::random(3));

                // 4. Simpan Header Transaksi Penjualan (Tabel stock_outs)
                $stockOut = StockOut::create([
                    'stock_id'      => $request->name,
                    'qty'           => $request->qty,
                    'total_price'   => $stock->selling_price * $request->qty, // Gunakan HARGA JUAL (selling_price)
                    'customer_name' => $request->customer_name,
                    'invoice'       => $invoice,
                    'created_at'    => $request->date,
                    'updated_at'    => $request->date,
                ]);

                // 5. LOGIKA FIFO: Ambil batch yang masih ada sisa stok
                $batches = StockIn::where('stock_id', $request->name)
                    ->where('qty_remaining', '>', 0)
                    ->orderBy('created_at', 'asc') // Ambil barang yang paling lama masuk dulu
                    ->lockForUpdate()
                    ->get();

                $remainingQtyToDeduct = $request->qty;

                // 6. Looping pemotongan per batch
                foreach ($batches as $batch) {
                    if ($remainingQtyToDeduct <= 0) break; // Hentikan jika kebutuhan sudah terpenuhi

                    $qtyTaken = 0;

                    if ($batch->qty_remaining >= $remainingQtyToDeduct) {
                        $qtyTaken = $remainingQtyToDeduct;
                        $batch->qty_remaining -= $remainingQtyToDeduct;
                        $remainingQtyToDeduct = 0;
                    } else {
                        $qtyTaken = $batch->qty_remaining;
                        $remainingQtyToDeduct -= $batch->qty_remaining;
                        $batch->qty_remaining = 0;
                    }
                    $batch->save();

                    // 7. Catat detail pemotongan batch ke tabel stock_out_details
                    StockOutDetail::create([
                        'stock_out_id' => $stockOut->id,
                        'stock_in_id'  => $batch->id,
                        'qty_taken'    => $qtyTaken
                    ]);
                }

                // 8. Update stok total di tabel master
                $stock->decrement('qty', $request->qty);
            });

            return redirect()->route('sales-record.index')
                            ->with('alert', 'success')
                            ->with('message', 'Sales record added successfully.');

        } catch (Exception $e) {
            // Kembali ke form jika error (contoh: stok kurang)
            return redirect()->back()
                            ->withInput() // Mengembalikan isian form sebelumnya
                            ->with('alert', 'error') // Ganti info menjadi error
                            ->with('message', $e->getMessage());
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
        try {
            DB::transaction(function () use ($id) {
                // 1. Cari data transaksi penjualan beserta detail batch yang terpotong
                $record = StockOut::with('details')->findOrFail($id);

                // 2. Kembalikan stok sisa (qty_remaining) ke masing-masing batch asalnya
                foreach ($record->details as $detail) {
                    $batchIn = \App\Models\StockIn::find($detail->stock_in_id);
                    if ($batchIn) {
                        // Tambahkan kembali qty yang dulu sempat diambil
                        $batchIn->qty_remaining += $detail->qty_taken;
                        $batchIn->save();
                    }
                }

                // 3. Hapus data rincian di tabel stock_out_details
                $record->details()->delete();

                // 4. Kembalikan total qty ke tabel master stocks
                $stock = Stock::findOrFail($record->stock_id);
                $stock->increment('qty', $record->qty);

                // 5. Hapus data utama penjualan di stock_outs
                $record->delete();
            });

            return redirect()->route('sales-record.index')->with('alert', 'success')->with('message', 'Sales record deleted successfully and stock restored.');

        } catch (Exception $e) {
            return redirect()->back()->with('alert', 'error')->with('message', 'Gagal menghapus record: ' . $e->getMessage());
        }
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

        $data = StockOut::with(['stock', 'details.stockIn'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc');

        $user = Auth::user();

        return DataTables::of($data)
        ->addColumn('invoice', function($data) {
            return $data->invoice;
        })
        ->addColumn('customer_name', function($data) {
            return $data->customer_name;
        })
        ->addColumn('product', function($data) {
            return $data->stock ? $data->stock->name : 'Produk tidak ditemukan';
        })
        ->addColumn('qty', function($data) {
            return $data->qty . ($data->stock ? ' ' . $data->stock->unit : ''); // Tambah satuan
        })
        // 2. KOLOM BARU: RINCIAN BATCH
        ->addColumn('batch_details', function($data) {
            if ($data->details->isEmpty()) {
                return '<span class="text-xs text-muted">Tidak ada rincian</span>';
            }

            $html = '';
            foreach ($data->details as $detail) {
                $batchCode = $detail->stockIn->batch_code ?? 'Tanpa Kode';
                // Menampilkan badge misal: "BATCH-A (Diambil: 5)"
                $html .= '<span class="badge bg-info text-xxs mb-1" style="display:inline-block;">'
                    . $batchCode . ' (Diambil: ' . $detail->qty_taken . ')'
                    . '</span><br>';
            }
            return $html;
        })
        ->addColumn('total_price', function($data) {
            return 'Rp ' . number_format($data->total_price, 0, ',', '.');
        })
        ->addColumn('date', function($data) {
            // Format tanggal agar lebih mudah dibaca (Misal: 18 Aug 2026, 14:30)
            return $data->created_at->format('d M Y, H:i');
        })
        ->addColumn('action', function($data) use ($user) {
            $hiddenInput = '<input type="hidden" name="ids[]" value="' . $data->id . '">';
            $delete = '';

            if($user->can('salesRecordDelete')) $delete = '<button class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete Record"></button>';

            return  $hiddenInput . $delete . '
            <form id="form_'. $data->id .'" action="' . route('sales-record.destroy', $data->id) . '" method="POST" style="display:none;">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';
        })
        ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->input('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($query) use ($search) {
                    // 3. PERBAIKAN PENCARIAN (Tambahkan Invoice & Customer)
                    $query->where('invoice', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('qty', 'like', "%{$search}%")
                        ->orWhereHas('stock', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            }
        })
        // 4. Masukkan 'batch_details' ke rawColumns agar HTML badge dirender
        ->rawColumns(['action', 'batch_details'])
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
