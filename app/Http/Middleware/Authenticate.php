<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');

        // if (!$request->expectsJson()) {
        //     session()->flash('alert', 'info');
        //     session()->flash('message', 'Your login session has expired, please login!');
        //     return route('login');
        // }
    }
}
