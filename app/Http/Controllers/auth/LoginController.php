<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function authenticate(Request $request) {
        $credentials = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        if(Auth::attempt($credentials, $request->filled('remember_token'))) {
            $user = Auth::user();
            
            // cek apakah akun user aktif/nonaktif
            if($user->status == 'tidak aktif') {
                Auth::logout();

                request()->session()->invalidate();
                
                request()->session()->regenerateToken();

                return back()->with('alert', 'info')->with('message', 'Akun anda telah dinonaktifkan, silahkan hubungi admin untuk info lebih lanjut!');
            }

            $request->session()->regenerate();
            $message = 'Welcome ' . $user->name;

            if($user->hasRole('admin')) return redirect()->route('home')->with('alert', 'success')->with('message', $message);
            if($user->hasRole('dapur')) return redirect()->route('order.index')->with('alert', 'success')->with('message', $message);
            if($user->hasRole(['kasir', 'partner', 'pelayan'])) return redirect()->route('cashier')->with('alert', 'success')->with('message', $message);
        } else {
            return back()->withInput()->withErrors([
                'email' => 'Email/password salah!',
            ]);
        }
    }
}
