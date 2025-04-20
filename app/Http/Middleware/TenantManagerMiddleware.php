<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Services\TenantDatabaseManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantManagerMiddleware
{
    protected $databaseManager;
    
    public function __construct(TenantDatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }
    
    public function handle(Request $request, Closure $next)
    {
        // Get tenant info from session
        if (!session('is_tenant') || !session('tenant_user_id') || !session('tenant_store_slug')) {
            return redirect()->route('login');
        }
        
        $userId = session('tenant_user_id');
        $slug = session('tenant_store_slug');
        
        // Get the store
        $store = Store::where('slug', $slug)->first();
        if (!$store) {
            return redirect()->route('login');
        }
        
        // Switch to tenant DB
        $this->databaseManager->switchToTenant($store);
        
        try {
            // Check if user is a manager
            $user = DB::connection('tenant')->table('users')->find($userId);
            

            // Switch back to main DB
            $this->databaseManager->switchToMain();
            
            if (!$user || $user->role !== 'manager') {
                Log::debug('User not found or not a manager', [
                    'user_id' => $userId,
                    'user_role' => $user->role,
                ]);
                return response('You do not have permission to access this area.', 403);
            }
            
            return $next($request);
        } catch (\Exception $e) {
            // Switch back to main DB
            $this->databaseManager->switchToMain();
            
            return redirect()->route('login');
        }
    }
}