<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Allow both 'admin' role and legacy is_admin=true users
        if (auth()->check() && auth()->user()->canAccessAdmin()) {
            return $next($request);
        }

        auth()->logout();
        return redirect()->route('login')
            ->withErrors(['email' => 'Halaman ini hanya untuk Administrator.']);
    }
}
