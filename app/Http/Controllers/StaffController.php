<?php

// filepath: d:\WST\inventory-management-system\app\Http\Controllers\StaffController.php
namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Services\TenantDatabaseManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Mail\WelcomeStaffMail;

class StaffController extends Controller
{
    protected $databaseManager;
    
    public function __construct(TenantDatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
       
        $this->middleware('tenant.manager');
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
     * Display a listing of staff.
     */
    public function index(Request $request)
    {
        $store = $this->getCurrentStore($request);
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        try {
            // Get staff with pagination
            $staff = DB::connection('tenant')->table('users')
                ->select('id', 'name', 'email', 'role', 'is_active', 'created_at')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
                
            $this->databaseManager->switchToMain();
            
            return view('staff.index', compact('staff', 'store'));
        } catch (\Exception $e) {
            Log::error("Error fetching staff", ['error' => $e->getMessage()]);
            $this->databaseManager->switchToMain();
            
            return redirect()->back()->with('error', 'Error fetching staff.');
        }
    }

    /**
     * Show the form for creating a new staff member.
     */
    public function create(Request $request)
    {
        $store = $this->getCurrentStore($request);
        
        // Check if store can add more users based on plan limit
        $userLimit = $store->pricingTier->user_limit ?? 0;
        $unlimited = $userLimit === null || $userLimit === -1;
        
        $this->databaseManager->switchToTenant($store);
        $currentCount = DB::connection('tenant')->table('users')->count();
        $this->databaseManager->switchToMain();
        
        if (!$unlimited && $currentCount >= $userLimit) {
            return redirect()->route('staff.index', ['subdomain' => $store->slug])
                ->with('error', 'You have reached the staff limit for your current plan. Please upgrade to add more staff.');
        }
        
        return view('staff.create', compact('store'));
    }

    /**
     * Store a newly created staff member in storage.
     */
    public function store(StoreStaffRequest $request)
    {
        $store = $this->getCurrentStore($request);
        
        // Validation is handled by the form request
        $validated = $request->validated();
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        try {
            // Generate a password
            $password = Str::random(10);
            
            // Create new user
            $userId = DB::connection('tenant')->table('users')->insertGetId([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'role' => $validated['role'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->databaseManager->switchToMain();
            
            // Send welcome email
            Mail::to($validated['email'])->send(new WelcomeStaffMail($store, $password));
            
            return redirect()->route('staff.index', ['subdomain' => $store->slug])
                ->with('success', "Staff added successfully! Temporary password: {$password}");
        } catch (\Exception $e) {
            Log::error("Error adding staff member", ['error' => $e->getMessage()]);
            $this->databaseManager->switchToMain();
            
            return redirect()->back()
                ->with('error', 'Error adding staff member. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified staff member.
     */
    public function edit(Request $request, $subdomain, $staff_id)
    {
        $store = $this->getCurrentStore($request);
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        try {
            $staff = DB::connection('tenant')->table('users')->find($staff_id);
            
            if (!$staff) {
                $this->databaseManager->switchToMain();
                return redirect()->route('staff.index', ['subdomain' => $store->slug])
                    ->with('error', 'Staff member not found.');
            }
            
            $this->databaseManager->switchToMain();
            
            return view('staff.edit', compact('staff', 'store'));
        } catch (\Exception $e) {
            Log::error("Error fetching staff for edit", ['error' => $e->getMessage()]);
            $this->databaseManager->switchToMain();
            
            return redirect()->route('staff.index', ['subdomain' => $store->slug])
                ->with('error', 'Error loading staff data.');
        }
    }

    /**
     * Update the specified staff member in storage.
     */
    public function update(UpdateStaffRequest $request, $subdomain, $staff_id)
    {
        $store = $this->getCurrentStore($request);
        
        // Validation is handled by the form request
        $validated = $request->validated();
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        try {
            // Update user
            DB::connection('tenant')->table('users')
                ->where('id', $staff_id)
                ->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'role' => $validated['role'],
                    'is_active' => $request->$validated['is_active'] ,
                    'updated_at' => now(),
                ]);
            
            $this->databaseManager->switchToMain();
            
            return redirect()->route('staff.index', ['subdomain' => $store->slug])
                ->with('success', 'Staff member updated successfully!');
        } catch (\Exception $e) {
            Log::error("Error updating staff member", ['error' => $e->getMessage()]);
            $this->databaseManager->switchToMain();
            
            return redirect()->back()
                ->with('error', 'Error updating staff member. Please try again.')
                ->withInput();
        }
    }

    /**
     * Reset a staff member's password.
     */
    public function resetPassword(Request $request, $subdomain, $staff_id)
    {
        $store = $this->getCurrentStore($request);
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        try {
            $staff = DB::connection('tenant')->table('users')->find($staff_id);
            
            if (!$staff) {
                $this->databaseManager->switchToMain();
                return redirect()->route('staff.index', ['subdomain' => $store->slug])
                    ->with('error', 'Staff member not found.');
            }
            
            // Generate new password
            $newPassword = Str::random(10);
            
            // Update password
            DB::connection('tenant')->table('users')
                ->where('id', $staff_id)
                ->update([
                    'password' => Hash::make($newPassword),
                    'updated_at' => now(),
                ]);
            
            $this->databaseManager->switchToMain();
            
            // Here you would send an email with the new password
            // Mail::to($staff->email)->send(new PasswordResetMail($newPassword));
            
            return redirect()->route('staff.index', ['subdomain' => $store->slug])
                ->with('success', "Password reset successfully! New temporary password: {$newPassword}");
        } catch (\Exception $e) {
            Log::error("Error resetting password", ['error' => $e->getMessage()]);
            $this->databaseManager->switchToMain();
            
            return redirect()->route('staff.index', ['subdomain' => $store->slug])
                ->with('error', 'Error resetting password. Please try again.');
        }
    }

    /**
     * Remove the specified staff member from storage.
     */
    public function destroy(Request $request, $subdomain, $staff_id)
    {
        $store = $this->getCurrentStore($request);
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        try {
            $staff = DB::connection('tenant')->table('users')->find($staff_id);
            
            if (!$staff) {
                $this->databaseManager->switchToMain();
                return redirect()->route('staff.index', ['subdomain' => $store->slug])
                    ->with('error', 'Staff member not found.');
            }
            
            // Prevent deleting last manager
            $managerCount = DB::connection('tenant')->table('users')
                ->where('role', 'manager')
                ->count();
                
            if ($staff->role === 'manager' && $managerCount <= 1) {
                $this->databaseManager->switchToMain();
                return redirect()->route('staff.index', ['subdomain' => $store->slug])
                    ->with('error', 'Cannot delete the last manager.');
            }
            
            // Delete user
            DB::connection('tenant')->table('users')->delete($staff_id);
            
            $this->databaseManager->switchToMain();
            
            return redirect()->route('staff.index', ['subdomain' => $store->slug])
                ->with('success', 'Staff member deleted successfully!');
        } catch (\Exception $e) {
            Log::error("Error deleting staff member", ['error' => $e->getMessage()]);
            $this->databaseManager->switchToMain();
            
            return redirect()->route('staff.index', ['subdomain' => $store->slug])
                ->with('error', 'Error deleting staff member. Please try again.');
        }
    }

    /**
     * Toggle active status for a staff member.
     */
    public function toggleStatus(Request $request, $subdomain, $staff_id)
    {
        $store = $this->getCurrentStore($request);
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        try {
            $staff = DB::connection('tenant')->table('users')->find($staff_id);
            
            if (!$staff) {
                $this->databaseManager->switchToMain();
                return redirect()->route('staff.index', ['subdomain' => $store->slug])
                    ->with('error', 'Staff member not found.');
            }
            
            // Prevent deactivating the last active manager
            if ($staff->role === 'manager' && !$staff->is_active) {
                $activeManagerCount = DB::connection('tenant')->table('users')
                    ->where('role', 'manager')
                    ->where('is_active', true)
                    ->count();
                    
                if ($activeManagerCount <= 1) {
                    $this->databaseManager->switchToMain();
                    return redirect()->route('staff.index', ['subdomain' => $store->slug])
                        ->with('error', 'Cannot deactivate the last active manager.');
                }
            }
            
            // Toggle status
            $newStatus = !$staff->is_active;
            
            DB::connection('tenant')->table('users')
                ->where('id', $staff_id)
                ->update([
                    'is_active' => $newStatus,
                    'updated_at' => now(),
                ]);
            
            $this->databaseManager->switchToMain();
            
            $statusText = $newStatus ? 'activated' : 'deactivated';
            return redirect()->route('staff.index', ['subdomain' => $store->slug])
                ->with('success', "Staff member {$statusText} successfully.");
        } catch (\Exception $e) {
            Log::error("Error toggling staff status", ['error' => $e->getMessage()]);
            $this->databaseManager->switchToMain();
            
            return redirect()->route('staff.index', ['subdomain' => $store->slug])
                ->with('error', 'Error changing staff status. Please try again.');
        }
    }
}