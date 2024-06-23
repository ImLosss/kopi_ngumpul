<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        try {
            $table = Table::findOrFail($id);

            $table->delete();

            return redirect()->back()->with('alert', 'success')->with('message', 'Berhasil menghapus data');
        } catch (\Throwable $e) {
            return redirect()->back()->with('alert', 'error')->with('message', 'Something Error!');
        }
    }

    public function getTables() 
    {
        $data = Table::all();

        // dd($data);
        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('menu', function($data) {
            return $data->name;
        })
        ->addColumn('action', function($data) {
            return '
            <a href="' . route('table.edit', $data->id) . '">
                <i class="fa-solid fa-pen-to-square text-secondary"></i>
            </a>
            <button class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>
            <form id="form_'. $data->id .'" action="' . route('table.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';
        })
        ->rawColumns(['action'])
        ->toJson(); 
    }
}
