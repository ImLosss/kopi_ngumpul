<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:userAccess');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.user.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $data = Role::whereNotIn('name', ['admin'])->get();

        return view('admin.user.create', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'notelp' => 'required|string|max:15',
            'status' => 'required',
            'role' => 'required|string|exists:roles,name'
        ]);

        User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'phone' => $validatedData['notelp'],
            'status' => $validatedData['status']
        ])->assignRole($validatedData['role']);

        return redirect()->route('user.index')->with('alert', 'success')->with('message', 'User berhasil ditambahkan');
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
        try {
            $data['role'] = Role::whereNotIn('name', ['admin'])->get();
            $data['user'] = User::with('roles')->findOrFail($id);

            // dd($data['user']);

            return view('admin.user.edit', $data);
        } catch (\Throwable $e) {
            return redirect()->route('user.index')->with('alert', 'error')->with('message', 'Something Error!');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'password' => 'nullable|string|min:8',
            'notelp' => 'required|string|max:15',
            'status' => 'required',
            'role' => 'required|string|exists:roles,name'
        ]);

        $user = User::findOrFail($id);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->notelp,
            'status' => $request->status
        ]);

        if($request->password) {
            $user->update([
                'password' => Hash::make($request->password)
            ]);
        }

        $role = Role::where('name', $request->role)->first();

        $user->syncRoles([$role]);

        return redirect()->route('user.index')->with('alert', 'success')->with('message', 'User berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        $user->delete();

        return redirect()->route('user.index')->with('alert', 'success')->with('message', 'User berhasil dihapus');

    }

    public function getUsers(Request $request) {

        // foreach ($users as $user) {
        //     foreach ($user->roles as $role){
        //         if ($role->name == 'SuperAdmin' ) {
        //             $adminId = $user->id;
        //         }
        //     }
        // }

        // $users = User::whereNotIn('id', [$adminId])->get();

        // return view('admin.users.index', compact('users'));

        $data = User::whereDoesntHave('roles', function($query) {
            $query->where('name', 'admin');
        });

        return DataTables::of($data)
        ->addcolumn('name', function($data) {
            return '<strong>' . $data->name . '</strong>';
        })
        ->addcolumn('email', function($data) {
            return $data->email;
        })
        ->addcolumn('phone', function($data) {
            return $data->phone;
        })
        ->addColumn('role', function($data) {
           return $data->getRoleNames()->implode(', ');
        })
        ->addcolumn('status', function($data) {
            if($data->status == 'aktif') {
                return '<span class="badge bg-success">Active</span>';
            } else {
                return '<span class="badge bg-danger">Inactive</span>';
            }
        })
        ->addColumn('action', function ($data) {
            // $user = auth()->user();
            // $editHidden = !$user->role('admin') ? 'hidden' : '';
            // $deleteHidden = !$user->role('admin') ? 'hidden' : '';

            return '
            <a href="' . route('user.edit', $data->id) . '" data-bs-toggle="tooltip" data-bs-original-title="Edit user">
                <i class="fas fa-user-edit text-secondary"></i>
            </a>
            <button class="cursor-pointer fas fa-trash text-danger" onclick="modalHapus('. $data->id .')" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete User"></button>
            <form id="form_'. $data->id .'" action="' . route('user.destroy', $data->id) . '" method="POST" class="inline">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';
        })
        ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->input('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('roles', function($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
                });
            }
        })
        ->rawColumns(['action', 'status', 'name'])
        ->toJson();
    }
}
