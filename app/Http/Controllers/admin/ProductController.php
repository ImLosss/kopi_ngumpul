<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Product::with('category')->get();

        // dd($data);
        return view('admin.product.index', compact('data'));
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
        //
    }

    public function getProduct() {
        $data = Product::with('category');

        // dd($data);
        return DataTables::of($data)
        ->addColumn('no', function($data) {
           return '12';
        })
        ->addColumn('name', function($data) {
            return $data->name;
         })
         ->addColumn('category', function($data) {
            return $data->category->name;
         })
         ->addColumn('jumlah', function($data) {
            return $data->jumlah;
         })
         ->addColumn('rate', function($data) {
            return '50';
         })
         ->addColumn('action', function($data) {
            return '50';
         })
        
        ->toJson(); 
    }
}
