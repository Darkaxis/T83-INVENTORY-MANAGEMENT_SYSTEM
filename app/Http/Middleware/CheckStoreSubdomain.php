<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckStoreSubdomain
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $segments = explode('.', $host);
        
        if (count($segments) === 3 && $segments[1] === 'inventory' && $segments[2] === 'test') {
            $subdomain = $segments[0];
            Log::info('Subdomain detected', ['host' => $host, 'subdomain' => $subdomain]);
        }
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