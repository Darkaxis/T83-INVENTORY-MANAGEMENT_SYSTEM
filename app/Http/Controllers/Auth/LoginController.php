<?php
// filepath: d:\WST\inventory-management-system\app\Http\Controllers\Auth\LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use App\Services\TenantDatabaseManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/admin/dashboard';
    protected $databaseManager;
    
    public function __construct(TenantDatabaseManager $databaseManager)
    {
        $this->middleware('guest')->except('logout');
        $this->databaseManager = $databaseManager;
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // Check if we're trying to login on a tenant subdomain
        $tenantStoreId = session('tenant_store');
        $tenantSlug = session('tenant_slug');
       
        // If tenant store ID and slug are set, attempt tenant login
        if ($tenantStoreId && $tenantSlug) {
            return $this->attemptTenantLogin($request, $tenantStoreId, $tenantSlug);
        }

        // Regular login for main domain
        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        return $this->sendFailedLoginResponse($request);
    }
    
    /**
     * Attempt to log the user into the tenant application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $storeId
     * @param  string  $slug
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function attemptTenantLogin(Request $request, $storeId, $slug)
    {
        $store = Store::findOrFail($storeId);
        
        try {
            // Switch to tenant database
            $this->databaseManager->switchToTenant($store);
            
            // Get user from tenant database
            $user = DB::connection('tenant')
                    ->table('users')
                    ->where('email', $request->email)
                    ->first();
                    
            if ($user && Hash::check($request->password, $user->password)) {
                // Convert stdClass to array
                $userData = (array) $user;
                
                // Create a custom guard for tenant users
                Auth::guard('tenant')->loginUsingId($user->id);
                
                // Store tenant information in session
                session(['is_tenant' => true]);
                session(['tenant_user' => $userData]);
                session(['tenant_store_id' => $store->id]);
                session(['tenant_store_slug' => $slug]);
                
                // Switch back to main database
                $this->databaseManager->switchToMain();
                
                // Log successful login
                Log::info("Tenant user logged in", [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'store_id' => $store->id,
                    'store_slug' => $slug
                ]);
                
                return redirect()->intended('/products');
            }
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return back()->withErrors([
                'email' => [trans('auth.failed')],
            ])->withInput($request->only('email', 'remember'));
            
        } catch (\Exception $e) {
            // Switch back to main database in case of error
            $this->databaseManager->switchToMain();
            
            Log::error("Tenant login error: " . $e->getMessage(), [
                'store_id' => $store->id,
                'email' => $request->email,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors([
                'email' => ['An error occurred during login. Please try again.'],
            ]);
        }
    }
    
    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // Check if this is a tenant user logout
        if (session('is_tenant')) {
            Auth::guard('tenant')->logout();
            
            session()->forget([
                'is_tenant', 
                'tenant_user', 
                'tenant_store_id', 
                'tenant_store_slug', 
                'tenant_store'
            ]);
        } else {
            Auth::logout();
        }
        
        return redirect('/');
    }

    // Rest of your controller methods...
}