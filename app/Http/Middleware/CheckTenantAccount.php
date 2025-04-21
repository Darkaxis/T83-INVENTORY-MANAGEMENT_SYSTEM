<?php
// filepath: d:\WST\inventory-management-system\app\Http\Middleware\MultiGuardAuth.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\error;

class CheckTenantAccount
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
   
    $isTenant = session('is_tenant', false);   
    $isAdmin = session('is_admin', false); 
    if ($isAdmin && $isTenant) {
       return redirect('/');
    }
    if ($isTenant ) {
        return $next($request);
    }
    
    return redirect('/');
}
}