<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.category.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        try {
            Category::create([
                'name' => $request->category
            ]);
            return redirect()->route('category')->with('alert', 'success')->with('message', 'Data berhasil ditambahkan');
        } catch(\Throwable $e) {
            return redirect()->route('category')->with('alert', 'error')->with('message', 'Terjadi kesalahan!');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $data = Category::findOrFail($id);

            return view('admin.category.edit', compact('data'));
        } catch (\Throwable $e) {
            return redirect()->route('category')->with('alert', 'error')->with('message', 'Terjadi kesalahan!');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, string $id)
    {
        try {
            $data = Category::findOrFail($id);

            $data->update([
                'name' => $request->category
            ]);

            return redirect()->route('category')->with('alert', 'success')->with('message', 'Data berhasil di update');
        } catch (\Throwable $e) {
            return redirect()->route('category')->with('alert', 'error')->with('message', 'Terjadi kesalahan!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data = Category::findOrFail($id);

            $data->delete();

            return redirect()->route('category')->with('alert', 'success')->with('message', 'Data berhasil dihapus');
        } catch (\Throwable $e) {
            return redirect()->route('category')->with('alert', 'error')->with('message', 'Terjadi kesalahan!');
        }
    }

    public function getCategories() 
    {
        $data = Category::all();

        // dd($data);
        return DataTables::of($data)
        ->addIndexColumn() 
        ->addColumn('menu', function($data) {
            return $data->name;
        })
        ->addColumn('action', function($data) {
            return '
            <a href="' . route('category.edit', $data->id) . '">
                <i class="fa-solid fa-pen-to-square text-secondary"></i>
            </a>
            <button class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>
            <form id="form_'. $data->id .'" action="' . route('category.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';
        })
        ->rawColumns(['action'])
        ->toJson(); 
    }
}
