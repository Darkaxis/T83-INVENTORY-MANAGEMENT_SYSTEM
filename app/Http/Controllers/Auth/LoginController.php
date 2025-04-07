<?php
// filepath: d:\WST\inventory-management-system\app\Http\Controllers\Auth\LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Services\TenantDatabaseManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/admin/dashboard';
    protected $databaseManager;

    public function __construct(TenantDatabaseManager $databaseManager)
    {
        
        $this->databaseManager = $databaseManager;
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // Check if we're on a subdomain
        $host = $request->getHost();
        $parts = explode('.', $host);
        $isSubdomain = count($parts) > 2 || ($parts[0] !== 'www' && $parts[0] !== 'localhost');
        
        if ($isSubdomain) {
            // For subdomain login, check tenant database
            $subdomain = $parts[0];
            
            // Switch to main database to find the store
            $this->databaseManager->switchToMain();
            
            $store = Store::where('slug', $subdomain)->first();
            
            if (!$store) {
                return $this->sendFailedLoginResponse($request, 'Store not found');
            }
            
            // Switch to tenant database for authentication
            $this->databaseManager->switchToTenant($store);
            
            // Attempt to log in (using tenant database)
            if ($this->attemptLogin($request)) {
                return $this->sendLoginResponse($request);
            }
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return $this->sendFailedLoginResponse($request);
        } else {
            // For main domain login, check central database
            // We're using admin_users table for system admins
            if (Auth::guard('admin')->attempt(
                $request->only('email', 'password'),
                $request->filled('remember')
            )) {
                return redirect()->intended(route('admin.dashboard'));
            }
            
            return $this->sendFailedLoginResponse($request);
        }
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Get host to determine if we're on a subdomain
        $host = $request->getHost();
        $parts = explode('.', $host);
        $isSubdomain = count($parts) > 2 || ($parts[0] !== 'www' && $parts[0] !== 'localhost');
        
        if ($isSubdomain) {
            // For store staff, redirect to store dashboard
            return redirect()->route('store.dashboard');
        } else {
            // For admin users, redirect to admin dashboard
            return redirect()->route('admin.dashboard');
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Check if we're on a subdomain
        $host = $request->getHost();
        $parts = explode('.', $host);
        $isSubdomain = count($parts) > 2 || ($parts[0] !== 'www' && $parts[0] !== 'localhost');
        
        if ($isSubdomain) {
            Auth::guard('web')->logout();
        } else {
            Auth::guard('admin')->logout();
        }
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
    
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        // Check if we're on a subdomain
        $host = request()->getHost();
        $parts = explode('.', $host);
        $isSubdomain = count($parts) > 2 || ($parts[0] !== 'www' && $parts[0] !== 'localhost');
        
        // Store in session whether we're on a subdomain login
        session(['is_subdomain_login' => $isSubdomain]);
        
        if ($isSubdomain) {
            // Store the subdomain in session for callback
            session(['login_subdomain' => $parts[0]]);
        }
        
        return Socialite::driver('google')->redirect();
    }
    
    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if we're handling a subdomain login
            $isSubdomain = session('is_subdomain_login', false);
            
            if ($isSubdomain) {
                $subdomain = session('login_subdomain');
                
                // Switch to main database first to get the store
                $this->databaseManager->switchToMain();
                
                $store = Store::where('slug', $subdomain)->first();
                
                if (!$store) {
                    return redirect()->route('login')
                        ->with('error', 'Store not found for this subdomain.');
                }
                
                // Switch to tenant database
                $this->databaseManager->switchToTenant($store);
                
                // Check if a user with this Google ID exists
                $user = User::where('email', $googleUser->email)->first();
                
                if (!$user) {
                    // Create the user if they don't exist
                    $user = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'password' => Hash::make(Str::random(16)), // Random password
                        'store_id' => $store->id,
                        'role' => 'staff', // Default role
                        'google_id' => $googleUser->id,
                    ]);
                } else {
                    // Update Google ID if it's not set
                    if (empty($user->google_id)) {
                        $user->google_id = $googleUser->id;
                        $user->save();
                    }
                }
                
                // Log the user in
                Auth::guard('web')->login($user);
                
                // Switch back to main database
                $this->databaseManager->switchToMain();
                
                return redirect()->route('store.dashboard');
            } else {
                // We're handling a main domain login (admin)
                // Check if admin user exists
                $adminUser = AdminUser::where('email', $googleUser->email)->first();
                
                if (!$adminUser) {
                    // Only allow admins to log in - don't auto-create
                    return redirect()->route('login')
                        ->with('error', 'No administrator account found with this email.');
                }
                
                // Update Google ID if it's not set
                if (empty($adminUser->google_id)) {
                    $adminUser->google_id = $googleUser->id;
                    $adminUser->save();
                }
                
                // Log the admin in
                Auth::guard('admin')->login($adminUser);
                
                return redirect()->route('admin.dashboard');
            }
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Google authentication failed: ' . $e->getMessage());
        }
    }
}