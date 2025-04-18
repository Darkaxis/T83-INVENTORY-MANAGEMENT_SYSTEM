<?php
// filepath: d:\WST\inventory-management-system\app\Http\Middleware\MultiGuardAuth.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class MultiGuardAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check session-based auth flags first (fastest)
        if (session('is_admin') || session('is_tenant')) {
            return $next($request);
        }
        
        // Check if authenticated with any guard
        if (Auth::guard('web')->check() || 
            Auth::guard('admin')->check() || 
            Auth::guard('tenant')->check()) {
            return $next($request);
        }
        
        // Not authenticated with any method, redirect to login
        return redirect()->route('login');
    }
}