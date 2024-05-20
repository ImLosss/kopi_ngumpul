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
            $request->session()->regenerate();
            $message = 'Welcome ' . Auth::user()->name;
            return redirect()->route('home')->with('alert', 'success')->with('message', $message);
        } else {
            return back()->withInput()->withErrors([
                'email' => 'Email/password salah!',
            ]);
        }
    }
}
