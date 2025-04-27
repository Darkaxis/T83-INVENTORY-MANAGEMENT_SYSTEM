<?php

// filepath: d:\WST\inventory-management-system\app\Http\Controllers\ProfileController.php
namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\TenantDatabaseManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    protected $databaseManager;
    
    public function __construct(TenantDatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
        $this->middleware('tenant');
    }
    
    /**
     * Get the current store from request subdomain
     */
    protected function getCurrentStore(Request $request)
    {
        $host = $request->getHost();
        $segments = explode('.', $host);
        $subdomain = $segments[0] ?? null;
        
        return Store::where('slug', $subdomain)->firstOrFail();
    }

    /**
     * Show the form to change password
     */
    public function showChangePasswordForm(Request $request)
    {
        $store = $this->getCurrentStore($request);
        return view('profile.password', compact('store'));
    }

    /**
     * Change the user's password
     */
    public function changePassword(Request $request)
    {
        $store = $this->getCurrentStore($request);
        
        // Validate request
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        try {
            // Get the current user
            $user = DB::connection('tenant')->table('users')
                ->where('id', session('tenant_user_id'))
                ->first();
            
            if (!$user) {
                $this->databaseManager->switchToMain();
                return redirect()->back()
                    ->with('error', 'User not found.');
            }
            
            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                $this->databaseManager->switchToMain();
                return redirect()->back()
                    ->with('error', 'The current password is incorrect.');
            }
            
            // Update password
            DB::connection('tenant')->table('users')
                ->where('id', session('tenant_user_id'))
                
                ->update([
                    'password' => Hash::make($request->password),
                    'updated_at' => now(),
                ]);
            
            $this->databaseManager->switchToMain();
            
            return redirect()->back()
                ->with('success', 'Password changed successfully!');
        } catch (\Exception $e) {
            Log::error("Error changing password", ['error' => $e->getMessage()]);
            $this->databaseManager->switchToMain();
            
            return redirect()->back()
                ->with('error', 'Error changing password. Please try again.');
        }
    }
}