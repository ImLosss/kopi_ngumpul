<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\StockIn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;

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
        // Tambahkan validasi untuk batch_code (nullable karena bisa jadi sistem yang buat otomatis)
        $request->validate([
            'name'       => 'required|exists:stocks,id',
            'qty'        => 'required|integer|min:1',
            'date'       => 'required|date',
            'batch_code' => 'required|string'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $stock = Stock::findOrFail($request->name);

                // 1. Tambahkan total qty ke master
                $stock->increment('qty', $request->qty);

                // 2. Tentukan Kode Batch
                // Jika request->batch_code diisi, gunakan itu.
                // Jika kosong, generate otomatis (Contoh: BATCH-20260818-ABCD)
                $batchCode = $request->batch_code
                            ?? 'BATCH-' . date('Ymd', strtotime($request->date)) . '-' . strtoupper(Str::random(4));

                // 3. Simpan data masuk dengan batch_code
                StockIn::create([
                    'batch_code'    => $batchCode, // <-- Masukkan ke database
                    'stock_id'      => $request->name,
                    'qty'           => $request->qty,
                    'qty_remaining' => $request->qty,
                    'created_at'    => $request->date,
                    'updated_at'    => $request->date,
                ]);
            });

            return redirect()->route('stock-in.index')
                            ->with('alert', 'success')
                            ->with('message', 'Stock In added successfully.');

        } catch (Exception $e) {
            return redirect()->back()
                            ->with('alert', 'error')
                            ->with('message', 'Terjadi kesalahan: ' . $e->getMessage());
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
        $record = StockIn::with('stock')->findOrFail($id);
        $products = Stock::all();
        return view('admin.stockIn.edit', compact('record', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Catatan: Saya asumsikan 'name' dari input form Anda berisi ID barang.
        // Akan lebih baik jika di HTML-nya name="stock_id" agar tidak membingungkan.
        $request->validate([
            'name' => 'required|exists:stocks,id',
            'qty'  => 'required|integer|min:1',
            'date' => 'required|date',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $stockIn = StockIn::findOrFail($id);
                $stock = Stock::findOrFail($request->name);

                // 1. Hitung selisih antara inputan baru dengan stok awal masuk
                // Jika awalnya 10, diupdate jadi 15 -> selisih = +5
                // Jika awalnya 20, diupdate jadi 15 -> selisih = -5
                $selisihQty = $request->qty - $stockIn->qty;

                // 2. Validasi: Jangan sampai perubahan membuat sisa batch ini jadi minus
                if (($stockIn->qty_remaining + $selisihQty) < 0) {
                    throw new Exception('Gagal. Barang dari batch ini sudah keluar lebih banyak dari jumlah yang Anda inputkan.');
                }

                // 3. Update master barang (Tabel stocks)
                // Jika selisihnya +, stok nambah. Jika -, stok berkurang otomatis
                $stock->qty += $selisihQty;
                $stock->save();

                // 4. Update riwayat batch (Tabel stock_ins)
                $stockIn->stock_id = $request->name;
                $stockIn->qty = $request->qty;
                $stockIn->qty_remaining += $selisihQty;
                $stockIn->created_at = $request->date; // Gunakan ini jika kolom Anda bernama created_at
                $stockIn->save();
            });

            return redirect()->route('stock-in.index')
                            ->with('alert', 'success')
                            ->with('message', 'Data barang masuk berhasil diperbarui.');

        } catch (Exception $e) {
            // Jika validasi gagal atau terjadi error, kembalikan user dengan pesan error
            return redirect()->back()
                            ->with('alert', 'error')
                            ->with('message', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $stockIn = StockIn::findOrFail($id);

                // 1. Validasi Utama: Cek apakah batch ini sudah ada yang terjual
                // Jika sisa stok (qty_remaining) kurang dari total awal masuk (qty), berarti sudah terpakai
                if ($stockIn->qty_remaining < $stockIn->qty) {
                    throw new Exception('Data tidak dapat dihapus karena sebagian atau seluruh barang dari batch ini sudah terjual/keluar.');
                }

                // 2. Kurangi total qty di tabel master barang
                $stock = Stock::findOrFail($stockIn->stock_id);

                // Validasi tambahan untuk berjaga-jaga
                if ($stock->qty < $stockIn->qty) {
                    throw new Exception('Total stok gudang tidak mencukupi untuk membatalkan transaksi ini.');
                }

                $stock->decrement('qty', $stockIn->qty);

                // 3. Hapus data batch (Stock In)
                // Lakukan penghapusan PALING TERAKHIR setelah semua validasi aman
                $stockIn->delete();
            });

            return redirect()->route('stock-in.index')
                            ->with('alert', 'success')
                            ->with('message', 'Data Stock In berhasil dihapus.');

        } catch (Exception $e) {
            // Jika gagal, kembalikan dengan pesan error (tidak ada data yang terhapus)
            return redirect()->back()
                            ->with('alert', 'error')
                            ->with('message', $e->getMessage());
        }
    }

    public function getStockIn(Request $request)
    {
        Log::info('Request Data: ', $request->all());

        $user = auth()->user();

        // Default: today
        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        if ($request->filled('dateRange')) {
            $dateRange = $request->dateRange;

            $dates = explode(' - ', $dateRange);
            if (count($dates) === 2) {
                try {
                    $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
                    $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
                } catch (\Exception $e) {
                    Log::error('Error parsing date range: ' . $e->getMessage());
                }
            }
        }

        $data = StockIn::with('stock')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc');

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
            // $update = '';
            $delete = '';
            // if($user->can('stockInUpdate')) $update = '<a href="' . route('stock-in.edit', $data->id) . '"><i class="fa-solid fa-pen-to-square text-secondary"></i></a>';
            if($user->can('stockInDelete')) $delete = '<button class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>';
            return  $hiddenInput . $delete . '
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
                    Log::error('Error parsing date range (stock-in print): ' . $e->getMessage());
                }
            }
        }

        $search = trim((string) $request->input('search', ''));

        $query = StockIn::with('stock')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('qty', 'like', "%{$search}%")
                    ->orWhere('created_at', 'like', "%{$search}%")
                    ->orWhereHas('stock', function ($stockQuery) use ($search) {
                        $stockQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $records = $query->get();

        return view('admin.stockIn.print', [
            'records' => $records,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dateRangeLabel' => $dateRangeLabel,
            'search' => $search,
        ]);
    }
}
