<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Facades\Log;

class EnsureTenantSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        
        // Check if we're on a subdomain
        $isSubdomain = count($parts) > 2 || (count($parts) > 1 && $parts[0] !== 'www' && $parts[0] !== 'localhost');
        
        if ($isSubdomain) {
            $subdomain = $parts[0];
            $store = Store::where('slug', $subdomain)->first();
            
            if (!$store) {
                Log::warning("Tenant middleware: Store not found for subdomain {$subdomain}");
                abort(404, 'Store not found');
            }
            
            // Set tenant information in session
            session(['tenant_store' => $store->id]);
            session(['tenant_slug' => $store->slug]);
            session(['is_tenant' => true]);
            
            // Also make the store available in the request for controllers
            $request->merge(['store' => $store]);
            
            Log::info("Tenant session established", [
                'store_id' => $store->id,
                'slug' => $store->slug,
                'session_id' => session()->getId()
            ]);
        } else {
            // Clear tenant session data if we're on the main domain
            session()->forget(['tenant_store', 'tenant_slug', 'is_tenant']);
        }
        
        return $next($request);
    }
}