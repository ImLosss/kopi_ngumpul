<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TableRequest;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class TableController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:tableAccess');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.table.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.table.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TableRequest $request)
    {
        try {
            Table::create([
                'no_meja' => $request->no_meja,
                'status' => $request->status
            ]);

            return redirect()->route('table')->with('alert', 'success')->with('message', 'Data berhasil ditambahkan');
        } catch (\Throwable $e) {
            return redirect()->route('table')->with('alert', 'error')->with('message', 'Something Error');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = Table::findOrFail($id);

        return view('admin.table.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TableRequest $request, string $id)
    {
        try{
            $table = Table::findOrFail($id);

            $table->update([
                'no_meja' => $request->no_meja,
                'status' => $request->status
            ]);

            return redirect()->route('table')->with('alert', 'success')->with('message', 'Data berhasil diubah');
        } catch (\Throwable $e) {
            return redirect()->route('table')->with('alert', 'error')->with('message', 'Something Error');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $table = Table::findOrFail($id);

            $table->delete();

            return redirect()->back()->with('alert', 'success')->with('message', 'Berhasil menghapus data');
        } catch (\Throwable $e) {
            return redirect()->back()->with('alert', 'error')->with('message', 'Something Error!');
        }
    }

    public function getTables(Request $request) 
    {
        $data = Table::query();
        $user = Auth::user();

        // dd($data);
        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('menu', function($data) {
            return $data->name;
        })
        ->addColumn('action', function($data) use($user) {
            $hapus = '';

            if($user->can('updateTable')) $hapus = '<button class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>';
            return '
            <a href="' . route('table.edit', $data->id) . '">
                <i class="fa-solid fa-pen-to-square text-secondary"></i>
            </a>
            ' . $hapus . '
            <form id="form_'. $data->id .'" action="' . route('table.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';
        })
        ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->input('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($query) use ($search) {
                    $query->where('status', 'like', "%{$search}%");
                });
            }
        })
        ->rawColumns(['action'])
        ->toJson(); 
    }
}
