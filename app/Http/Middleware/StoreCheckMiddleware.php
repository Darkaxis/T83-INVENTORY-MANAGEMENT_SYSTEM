<?php
// filepath: d:\WST\inventory-management-system\app\Http\Middleware\StoreCheckMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Services\TenantDatabaseManager;
use Illuminate\Support\Facades\Log;

class StoreCheckMiddleware
{
    /**
     * The database manager instance.
     */
    protected $databaseManager;

    /**
     * Create a new middleware instance.
     *
     * @param  TenantDatabaseManager  $databaseManager
     * @return void
     */
    public function __construct(TenantDatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        
        // Check if we're on a subdomain
        if (count($parts) > 2 || (count($parts) > 1 && $parts[0] !== 'www')) {
            $subdomain = $parts[0];
            
            // Debug log
            Log::info('Processing subdomain', ['subdomain' => $subdomain]);
            
            // Switch to main database first to find the store
            $this->databaseManager->switchToMain();
            
            $store = Store::where('slug', $subdomain)->first();
            
            if (!$store) {
                Log::error('Store not found for subdomain', ['subdomain' => $subdomain]);
                abort(404, 'Store not found');
            }
            
            // Switch to the tenant database
            $this->databaseManager->switchToTenant($store);
            
            // Set store in the request
            $request->store = $store;
            Log::info('Store found', ['id' => $store->id, 'name' => $store->name]);
            
            return $next($request);
        }
        
        // Not a subdomain request
        Log::warning('Not on a subdomain but StoreCheckMiddleware was called', ['host' => $host]);
        abort(404, 'Store not found');
    }
}