<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserEditRequest;
use App\Http\Requests\UserRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
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
        return view('admin.users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $data = Role::whereNotIn('name', ['admin'])->get();

        return view('admin.users.create', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->notelp,
                'status' => $request->status
            ])->assignRole($request->role);

            return redirect()->route('user')->with('alert', 'success')->with('message', 'User berhasil ditambahkan');
        } catch (\Throwable $e) {
            return redirect()->route('user')->with('alert', 'error')->with('message', 'Something Error!');
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
        try {
            $data['role'] = Role::whereNotIn('name', ['admin'])->get();
            $data['user'] = User::with('roles')->findOrFail($id);

            // dd($data['user']);

            return view('admin.users.edit', $data);
        } catch (\Throwable $e) {
            return redirect()->route('user')->with('alert', 'error')->with('message', 'Something Error!');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserEditRequest $request, string $id)
    {
        try {
            // dd($request);
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

            return redirect()->route('user')->with('alert', 'success')->with('message', 'User berhasil diubah');
        } catch (\Throwable $e) {
            return $e;
            return redirect()->route('user')->with('alert', 'error')->with('message', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail($id);
            
            $user->delete();

            return redirect()->route('user')->with('alert', 'success')->with('message', 'User berhasil dihapus');
        } catch (\Throwable $e) {
            return $e;
            return redirect()->route('user')->with('alert', 'error')->with('message', $e);
        }
    }

    public function getUser(Request $request) {

        // foreach ($users as $user) {
        //     foreach ($user->roles as $role){
        //         if ($role->name == 'SuperAdmin' ) {
        //             $adminId = $user->id;
        //         }
        //     }
        // }

        // $users = User::whereNotIn('id', [$adminId])->get();

        // return view('admin.users.index', compact('users'));

        $data = User::with(['roles' => function($query) {
            $query->whereNotIn('name', ['admin']);
        }])->get();

        foreach ($data as $user) {
            foreach ($user->roles as $role){
                if ($role->name == 'admin' ) {
                    $adminId = $user->id;
                }
            }
        }
        
        $data = User::whereNotIn('id', [$adminId]);

        return DataTables::of($data)
        ->addColumn('role', function($data) {
           return $data->getRoleNames()->implode(', ');
        })
        ->addColumn('since', function($data) {
            return $data->updated_at;
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
        ->rawColumns(['action'])
        ->toJson(); 
    }
}
