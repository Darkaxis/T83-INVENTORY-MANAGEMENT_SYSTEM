<?php
// filepath: d:\WST\inventory-management-system\app\Http\Middleware\MultiGuardAuth.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\error;

class AdminMiddleware
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
    
    $isAdmin = session('is_admin', false);
        
   
    if ($isAdmin) {
        return $next($request);
    }
   
    return redirect('/')
        ->with('error', 'You do not have admin access.');
}
}