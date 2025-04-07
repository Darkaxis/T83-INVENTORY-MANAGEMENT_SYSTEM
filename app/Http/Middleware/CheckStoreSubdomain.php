<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckStoreSubdomain
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        
        // Skip this middleware for the main domain
        if (count($parts) <= 2 || $parts[0] === 'www') {
            return $next($request);
        }
        
        $subdomain = $parts[0];
        $store = Store::where('slug', $subdomain)->first();
        
        if (!$store) {
            abort(404, 'Store not found');
        }
        
        // Make the store available in the request
        $request->merge(['store' => $store]);
        $request->store = $store;
        
        return $next($request);
    }
}