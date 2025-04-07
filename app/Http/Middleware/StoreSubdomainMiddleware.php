<?php


namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreSubdomainMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];
        
        // Skip middleware for the main domain
        if ($subdomain === 'localhost' || $subdomain === 'www') {
            return $next($request);
        }
        
        // Find store by subdomain
        $store = Store::where('slug', $subdomain)->first();
        
        if (!$store) {
            abort(404, 'Store not found');
        }
        
        // Add store to the request so controllers can access it
        $request->merge(['store' => $store]);
        $request->store = $store;
        
        return $next($request);
    }
}