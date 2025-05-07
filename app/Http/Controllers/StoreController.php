<?php
// filepath: d:\WST\inventory-management-system\app\Http\Controllers\StoreController.php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Services\TenantDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\StoreApproved;
use App\Models\PricingTier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Requests\Store\StoreStoreRequest;
use App\Http\Requests\Store\PublicStoreCreateRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Http\Requests\Store\ApproveStoreRequest;
use App\Http\Requests\Store\UpdatePricingTierRequest;

class StoreController extends Controller
{
    protected $databaseManager;

    public function __construct(TenantDatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    public function index()
    { 
        $stores = Store::withCount(['users', 'products'])->get();
        return view('stores.index', compact('stores'));
    }

    public function create()
    {    
        $pricingTiers = PricingTier::where('is_active', true)->orderBy('sort_order')->get();
        return view('stores.create', compact('pricingTiers'));
    }

    public function store(StoreStoreRequest $request)
    {
        $validated = $request->validated();
        
        $now = now();
        $subscriptionEnd = $validated['billing_cycle'] === 'monthly' ? 
            $now->copy()->addMonth() : 
            $now->copy()->addYear();
        
        $store = Store::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'zip' => $validated['zip'] ?? null,
            'status' => 'inactive',
            'approved' => false,
            'pricing_tier_id' => $validated['pricing_tier_id'],
            'billing_cycle' => $validated['billing_cycle'],
            'subscription_start_date' => $now,
            'subscription_end_date' => $subscriptionEnd,
            'auto_renew' => true,
        ]);

        // Check if the store is approved
        if ($store->approved) {
            $this->createDatabase($store);
            Log::info("Store created and database created", ['store_id' => $store->id, 'slug' => $store->slug]);
        }
        
        return redirect()->route('stores.index')
            ->with('success', 'Store created successfully. ' . 
                   (!$store->approved ? 'It is pending admin approval.' : 'Database has been created.'));
    }

    public function publicStorecreate(PublicStoreCreateRequest $request)
    {
        $validated = $request->validated();

        // Set default pricing tier (Free tier) if not provided
        if (empty($validated['pricing_tier_id'])) {
            $freeTier = PricingTier::where('name', 'Free')->first();
            if ($freeTier) {
                $validated['pricing_tier_id'] = $freeTier->id;
            }
        }

        // Set default billing cycle if not provided
        if (empty($validated['billing_cycle'])) {
            $validated['billing_cycle'] = 'monthly';
        }

        // Set subscription dates
        $now = now();
        $subscriptionEnd = $validated['billing_cycle'] === 'monthly' ? 
            $now->copy()->addMonth() : 
            $now->copy()->addYear();

        // Create the store with all required fields
        $store = Store::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'zip' => $validated['zip'] ?? null,
            'status' => 'inactive',
            'approved' => false,
            'pricing_tier_id' => $validated['pricing_tier_id'] ?? null,
            'billing_cycle' => $validated['billing_cycle'] ?? 'monthly',
            'subscription_start_date' => $now,
            'subscription_end_date' => $subscriptionEnd,
            'auto_renew' => true,
        ]);

        return redirect()->route('public.store-requests.thank-you', $store);
    }

    public function show(Store $store)
    {
        return view('stores.show', compact('store'));
    }

    public function edit(Store $store)
    {
        $pricingTiers = PricingTier::where('is_active', true)->orderBy('sort_order')->get();
        return view('stores.edit', compact('store', 'pricingTiers'));
    }

    public function update(UpdateStoreRequest $request, Store $store)
    {
        $validated = $request->validated();
        
        $wasApproved = $store->approved;
        $nowApproved = $request->boolean('approved');
        
        // If the store is being approved for the first time
        if (!$wasApproved && $nowApproved && !$store->database_created) {
            $store->update($validated);
            $this->createDatabase($store);
            Log::info("Store approved and database created", ['store_id' => $store->id, 'slug' => $store->slug]);
            return redirect()->route('stores.index')
                ->with('success', 'Store updated and database created successfully.');
        }

        $store->update($validated);
        
        return redirect()->route('stores.index')
            ->with('success', 'Store updated successfully.');
    }

    public function destroy(Store $store)
    {
        // Delete database if it exists
        if ($store->database_created) {
            try {
                $databaseName = 'tenant_' . $store->slug;
                $this->databaseManager->dropDatabase($databaseName);
                Log::info("Deleted database for store", ['store_id' => $store->id, 'database' => $databaseName]);
            } catch (\Exception $e) {
                Log::error("Failed to delete database: " . $e->getMessage(), [
                    'store_id' => $store->id, 
                    'database' => 'tenant_' . $store->slug
                ]);
            }
        }

        $store->delete();
        
        return redirect()->route('stores.index')
            ->with('success', 'Store deleted successfully.');
    }

    public function approve(ApproveStoreRequest $request, Store $store)
    {
        if (!$store->approved) {
            // Update the store status to approved
            $store->update([
                'approved' => true,
                'status' => 'active' // Automatically set to active when approved
            ]);
            
            // Create database if not already created
            if (!$store->database_created) {
                $this->createDatabase($store);
            }
            
            // Check if the email already exists in the main database
            $existingUser = \App\Models\User::where('email', $store->email)->first();
            
            // Generate a unique store-specific password
            $storePassword = Str::random(10);
            
            if ($existingUser) {
                // User already exists in the main database
                $user = $existingUser;
                Log::info("Using existing user for store approval", [
                    'store_id' => $store->id,
                    'user_id' => $user->id
                ]);
            } else {
                // Create a new user in the main database
                $user = \App\Models\User::create([
                    'name' => $request->input('owner_name', 'Store Owner'),
                    'email' => $store->email,
                    'password' => bcrypt($storePassword),
                    'store_id' => $store->id,  // Set this to the latest store they own
                ]);
                
                // Assign owner role
                $user->assignRole('manager');
                
                Log::info("Created new user for store owner", [
                    'store_id' => $store->id,
                    'user_id' => $user->id
                ]);
            }
            
            // Create a store-user relationship record
            \App\Models\StoreUser::create([
                'store_id' => $store->id,
                'user_id' => $user->id,
                'role' => 'manager',
                'access_level' => 'full',
                'store_password' => bcrypt($storePassword), // Store unique password for this store
            ]);
            
            try {
                $tenantDB = 'tenant_' . $store->slug;
                \Illuminate\Support\Facades\Config::set('database.connections.tenant.database', $tenantDB);
                \Illuminate\Support\Facades\DB::purge('tenant');
                
                // Create the user in the tenant database with the store-specific password
                \Illuminate\Support\Facades\DB::connection('tenant')->table('users')->insert([
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => bcrypt($storePassword), // Use store-specific password
                    'role' => 'manager',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Find the user ID in tenant database
                $tenantUserId = \Illuminate\Support\Facades\DB::connection('tenant')
                    ->table('users')
                    ->where('email', $user->email)
                    ->value('id');
                    
                // Assign owner role in tenant database
                if ($tenantUserId) {
                    $roleId = \Illuminate\Support\Facades\DB::connection('tenant')
                        ->table('roles')
                        ->where('name', 'manager')
                        ->value('id');
                        
                    if ($roleId) {
                        \Illuminate\Support\Facades\DB::connection('tenant')->table('model_has_roles')->insert([
                            'role_id' => $roleId,
                            'model_type' => 'App\\Models\\User',
                            'model_id' => $tenantUserId
                        ]);
                    }
                }
                
                Log::info("User added to tenant database", [
                    'store_id' => $store->id, 
                    'user_id' => $user->id,
                    'tenant_user_id' => $tenantUserId ?? null
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to add user to tenant database: " . $e->getMessage(), [
                    'store_id' => $store->id,
                    'user_id' => $user->id,
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            // Store login details for notification
            $loginDetails = [
                'email' => $store->email,
                'password' => $storePassword
            ];
            
            $emailSent = false;
            if ($store->email) {
                 
                try {
                    // Send email immediately without queueing
                    Mail::to($store->email)->send(new StoreApproved($store, $loginDetails));
                    
                    $emailSent = true;
                    Log::info("Approval email sent to store owner", [
                        'store_id' => $store->id, 
                        'email' => $store->email
                    ]);
                } catch (\Exception $e) {
                    $emailSent = false;
                    Log::error("Failed to send store approval email: " . $e->getMessage(), [
                        'store_id' => $store->id,
                        'email' => $store->email,
                        'exception' => get_class($e),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
                
            return redirect()->route('stores.index')
                ->with('success', 'Store approved and database created successfully.' . 
                    ($emailSent ? ' Owner has been notified via email.' : ' Unable to send email notification.'));
        }
        
        return redirect()->route('stores.index')
            ->with('info', 'Store was already approved.');
    }

    public function rebuildDatabase(Store $store)
    {
        if (!$store->approved) {
            return redirect()->route('stores.show', $store)
                ->with('error', 'Store must be approved before creating a database.');
        }
        
        $success = $this->createDatabase($store);
        
        if ($success) {
            return redirect()->route('stores.show', $store)
                ->with('success', 'Database rebuilt successfully.');
        } else {
            return redirect()->route('stores.show', $store)
                ->with('error', 'Failed to rebuild database. Check logs for details.');
        }
    }

    public function resetDatabase(Store $store)
    {
        try {
            $databaseName = 'tenant_' . $store->slug;
            
            // Drop existing database
            $this->databaseManager->dropDatabase($databaseName);
            
            // Create new database
            $success = $this->createDatabase($store);
            
            if ($success) {
                return redirect()->route('stores.show', $store)
                    ->with('success', 'Database reset successfully.');
            } else {
                return redirect()->route('stores.show', $store)
                    ->with('error', 'Failed to reset database. Check logs for details.');
            }
        } catch (\Exception $e) {
            Log::error("Failed to reset database: " . $e->getMessage(), [
                'store_id' => $store->id, 
                'database' => 'tenant_' . $store->slug,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('stores.show', $store)
                ->with('error', 'Failed to reset database: ' . $e->getMessage());
        }
    }

    protected function createDatabase(Store $store)
    {
        try {
            $result = $this->databaseManager->createTenantDatabase($store);
            
            if ($result) {
                $store->update(['database_created' => true]);
                Log::info("Created database for store", ['store_id' => $store->id, 'slug' => $store->slug]);
                return true;
            }
            
            Log::error("Failed to create tenant database", ['store_id' => $store->id, 'slug' => $store->slug]);
            return false;
        } catch (\Exception $e) {
            Log::error("Exception creating tenant database: " . $e->getMessage(), [
                'store_id' => $store->id,
                'slug' => $store->slug,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function thankYou(Store $storeRequest)
    {
        return view('public.store-requests.thank-you', compact('storeRequest'));
    }

    public function showStoreRequestForm()
    {
        return view('public.store-requests.create');
    }
        
    public function updatePricingTier(UpdatePricingTierRequest $request, Store $store)
    {
        // Log what we're receiving
        Log::debug('Pricing tier update request', [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'request_data' => $request->all()
        ]);
        
        // Get validated data
        $validated = $request->validated();
        
        // Get the tier for logging/feedback
        $pricingTier = PricingTier::find($validated['pricing_tier_id']);
        
        // Use direct query builder like in the test route that works
        DB::table('stores')
            ->where('id', $store->id)
            ->update([
                'pricing_tier_id' => $validated['pricing_tier_id'],
                'billing_cycle' => $validated['billing_cycle'],
                'auto_renew' => $validated['auto_renew'] ? 1 : 0,
            ]);
        
        // Update dates if requested
        if ($validated['reset_dates']) {
            $now = now();
            $end = $validated['billing_cycle'] === 'monthly' ? $now->copy()->addMonth() : $now->copy()->addYear();
            
            DB::table('stores')
                ->where('id', $store->id)
                ->update([
                    'subscription_start_date' => $now,
                    'subscription_end_date' => $end
                ]);
        }
        
        // Refresh the store data
        $store = $store->fresh();
        
        // Log the result
        Log::debug('Pricing tier update completed', [
            'store' => $store->id,
            'new_pricing_tier' => $store->pricing_tier_id,
            'tier_exists' => $pricingTier ? true : false,
            'tier_name' => $pricingTier ? $pricingTier->name : 'Unknown'
        ]);
        
        // Redirect with success message
        return redirect()->route('stores.index')
            ->with('success', "Pricing tier for '{$store->name}' updated to '{$pricingTier->name}'");
    }
}