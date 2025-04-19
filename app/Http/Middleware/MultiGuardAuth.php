<?php
// filepath: d:\WST\inventory-management-system\app\Http\Middleware\MultiGuardAuth.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\error;

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
    // Use the same approach that works in your test
    $isTenant = session('is_tenant', false);
    $isAdmin = session('is_admin', false);
    
    Log::debug('Auth check in middleware', [
        'path' => $request->path(),
        'is_tenant' => $isTenant,
        'is_admin' => $isAdmin,
        'session_id' => session()->getId()
    ]);

    
    
    if ($isTenant || $isAdmin) {
        return $next($request);
    }
    
    return redirect('/');
}
}