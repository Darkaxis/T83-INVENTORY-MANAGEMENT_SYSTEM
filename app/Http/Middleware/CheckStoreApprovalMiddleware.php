<?php
// filepath: d:\WST\inventory-management-system\app\Http\Middleware\CheckStoreApprovalMiddleware.php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckStoreApprovalMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $segments = explode('.', $host);
        
        // FIXED SUBDOMAIN DETECTION FOR VALET
        $subdomain = null;

        
    if (count($segments) === 3 && $segments[1] === 'inventory' && $segments[2] === 'test') {
        $subdomain = $segments[0];
        Log::info('Subdomain detected', ['host' => $host, 'subdomain' => $subdomain]);
    }
       
        
        // Only check store if it's a subdomain
        if ($subdomain) {
            // Find the store by subdomain/slug
            $store = Store::where('slug', $subdomain)->first();
            
            // Store doesn't exist
            if (!$store) {
                Log::warning('Attempted to access non-existent store subdomain', ['subdomain' => $subdomain]);
                return response(view('errors.404'), 404);
            }
            
            // Store exists but isn't approved
            if (!$store->approved) {
                Log::info('Attempted to access unapproved store subdomain', [
                    'store_id' => $store->id,
                    'subdomain' => $subdomain
                ]);
                return response(view('errors.store_pending'), 403);
            }
            
            // Store is inactive
            if ($store->status === 'inactive') {
                Log::info('Attempted to access inactive store subdomain', [
                    'store_id' => $store->id,
                    'subdomain' => $subdomain
                ]);
                return response(view('errors.store_inactive'), 403);
            }
            
            // Set the current store on the request for later use
            $request->merge(['current_store' => $store]);
        }
        
        return $next($request);
    }
}