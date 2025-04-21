<?php
// filepath: d:\WST\inventory-management-system\app\Http\Controllers\Auth\LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\TenantDatabaseManager;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\error;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';
    
    /**
     * The tenant database manager instance.
     *
     * @var \App\Services\TenantDatabaseManager
     */
    protected $databaseManager;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\TenantDatabaseManager  $databaseManager
     * @return void
     */
    public function __construct(TenantDatabaseManager $databaseManager)
    {
        $this->middleware('guest')->except('logout');
        $this->databaseManager = $databaseManager;
        
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
        public function login(Request $request)
    {
        //wipe session data
    
        session()->flush();
        $this->validateLogin($request);
    
        // Debug the login attempt
        Log::info('Login attempt', [
            'email' => $request->email,
            'host' => $request->getHost()
        ]);
    
        // Check for too many login attempts
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }
    
        // Determine if we're on a subdomain
        $host = $request->getHost();
        $subdomain = null;
        $isSubdomain = false;
        $segments = explode('.', $host);
        if (count($segments) === 3 && $segments[1] === 'inventory') {
            $subdomain = $segments[0]; // Get the subdomain part
            
            $isSubdomain = true;
            Log::info('Subdomain detected', ['host' => $host, 'subdomain' => $subdomain]);
        }

        $success = false;
    
        if ($isSubdomain) { 
            
            
            $store = Store::where('slug', $subdomain)->first();
            
            if (!$store) {
                return $this->sendFailedLoginResponse($request);
            }
            
            // Switch to tenant database
            $this->databaseManager->switchToTenant($store);
            
            
            $success = $this->guard('tenant')->attempt(
                $this->credentials($request), $request->filled('remember')
            );
            
                
        if ($success) {
            // First regenerate the session BEFORE setting variables
            $request->session()->regenerate();
            
            if ($isSubdomain) {
                $user = $this->guard('tenant')->user();
                if (!$user->is_active) {
                    return back()->withErrors([
                        'email' => 'Your account has been deactivated. Please contact your manager.',
                    ]);
                }
                // Set session variables
                $request->session()->put([
                    'is_tenant' => true,
                    'tenant_user_id' => $user->id,
                    'tenant_user_role' => $user->role,
                    'tenant_store_id' => $store->id,
                    'tenant_store_slug' => $store->slug
                ]);
                
                // Force save the session
                $request->session()->save();
                
                Log::info("Tenant login successful with session data", [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'session_id' => session()->getId(),
                    'is_tenant_set' => session('is_tenant', false)
                ]);
                
                // Return redirect directly instead of using sendLoginResponse
                return redirect('/products');
            } else {
                // Similar approach for admin login
                $user = $this->guard('admin')->user();
                
                $request->session()->put('is_admin', true);
                $request->session()->save();
                
                Log::info("Admin login successful with session data", [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'session_id' => session()->getId(),
                    'is_admin_set' => session('is_admin', false)
                ]);
                
               
            }
        }
              
            // Switch back to main database
            $this->databaseManager->switchToMain();
        } else {
            // Admin login - use the admin guard, not web
            session()->forget(['is_tenant', 'tenant_store_id', 'tenant_store_slug']);
            
            // IMPORTANT: Use admin guard instead of web guard
            $success = $this->guard('admin')->attempt(
                $this->credentials($request), $request->filled('remember')
            );
            
            if ($success) {
                $user = $this->guard('admin')->user();
                session(['is_admin' => true]);
                
                Log::info('Admin login successful', [
                    'email' => $user->email,
                    'id' => $user->id
                    
                ]);
                
            } else {
                Log::warning('Admin login failed', [
                    'email' => $request->email,
                    'reason' => 'Credentials do not match'
                ]);
            }
        }
    
        if ($success) {
            return $this->sendLoginResponse($request);
        }
    
        // Increment login attempts
        $this->incrementLoginAttempts($request);
    
        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard($name = null)
    {
        return Auth::guard($name);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // Determine which guard to use for logout
        if (session('is_tenant')) {
            $this->guard('tenant')->logout();
        } else {
            $this->guard()->logout();
        }

        // Clear all session data
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Get the post login redirect path.
     *
     * @return string
     */
    protected function redirectTo()
    {
        if (session('is_admin')) {
        
            return '/dashboard';
        } else if (session('is_tenant')) {
        
            return '/products';
        }
        
        return $this->redirectTo;
    }
        
    public function testLogin(Request $request)
    {
        // Set some session data
        session(['is_tenant' => true]);
        session(['tenant_store_id' => 1]);
        session(['login_time' => now()->toString()]);
        
    
        session()->save();
    
        return redirect('/session-test-page')->with('message', 'Test login completed');
    }
}