<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        
        foreach ($users as $user) {
            foreach ($user->roles as $role){
                if ($role->name == 'admin' ) {
                    $adminId = $user->id;
                }
            }
        }
        // dd($user);
        $users = User::whereNotIn('id', [$adminId])->get();

        return view('admin.users.index', compact('users'));
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

    public function getUser() {

        // foreach ($users as $user) {
        //     foreach ($user->roles as $role){
        //         if ($role->name == 'SuperAdmin' ) {
        //             $adminId = $user->id;
        //         }
        //     }
        // }

        // $users = User::whereNotIn('id', [$adminId])->get();

        // return view('admin.users.index', compact('users'));

        $data = User::with('roles');
        return DataTables::of($data)
        ->addColumn('role', function($data) {
           return $data->getRoleNames()->implode(', ');
        })
        ->addColumn('since', function($data) {
            return $data->updated_at;
        })
        ->addColumn('status', function($data) {
            return 'Inactive';
        })
        ->addColumn('action', function ($data) {
            // $user = auth()->user();
            // $editHidden = !$user->role('admin') ? 'hidden' : '';
            // $deleteHidden = !$user->role('admin') ? 'hidden' : '';

            return '
            <a href="' . route('user.edit', $data->id) . '" data-bs-toggle="tooltip" data-bs-original-title="Edit user">
                <i class="fas fa-user-edit text-secondary"></i>
            </a>
            <button class="cursor-pointer fas fa-trash text-danger" onclick="submit('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>
            <form id="form_'. $data->id .'" action="' . route('user.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';
        })
        ->filterColumn('role', function($data, $keyword) {
            // Meng-handle search untuk kolom role
            $data->whereHas('roles', function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            });
        })
        ->rawColumns(['action'])
        ->toJson(); 
    }
}
