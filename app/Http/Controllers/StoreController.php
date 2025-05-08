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
    {    $pricingTiers = PricingTier::where('is_active', true)->orderBy('sort_order')->get();
        return view('stores.create', compact('pricingTiers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:stores,slug|regex:/^[a-z0-9\-]+$/',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'status' => 'inactive',
            'approved' => 'false',
            'pricing_tier_id' => 'required|exists:pricing_tiers,id',
            'billing_cycle' => 'required|in:monthly,annual',
        ]);

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
        
            return redirect()->route('stores.index' )
            ->with('success', 'Store created successfully. ' . 
                   (!$store->approved ? 'It is pending admin approval.' : 'Database has been created.'));
        

        
       
    }
    public function status(Request $request, Store $store)
    {
    
        //check if store is already approved
        if (!$store->approved) {
            return back()->with('error', 'Store is not approved yet. Cannot change status.');
        }
        $store->update([
            'status' => $store->status === 'active' ? 'inactive' : 'active'
        ]);
        
        return back()->with('success', 'Store status has been updated to ' . $store->status);
    }
    public function publicStorecreate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:stores,slug|regex:/^[a-z0-9\-]+$/',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'status' => 'inactive',
            'approved' => 'false',
            'pricing_tier_id' => 'nullable|exists:pricing_tiers,id',
            'billing_cycle' => 'nullable|in:monthly,annual',
        ]);

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

    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:stores,slug,' . $store->id . '|regex:/^[a-z0-9\-]+$/',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
            'approved' => 'boolean',
        ]);

     
        
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
        try {
            // Start a database transaction
            DB::beginTransaction();
            
            // Store name for confirmation message
            $storeName = $store->name;
            $storeId = $store->id;
            
            Log::info("Starting deletion of store ID {$storeId}: {$storeName}");
            
            // Check for any tickets that might still be linked
            $ticketCount = DB::table('support_tickets')->where('store_id', $storeId)->count();
            Log::info("Found {$ticketCount} support tickets to delete for store {$storeId}");
            
            // Delete with raw queries to bypass any potential Eloquent issues
            if ($ticketCount > 0) {
                // Get all ticket IDs for this store
                $ticketIds = DB::table('support_tickets')
                    ->where('store_id', $storeId)
                    ->pluck('id')
                    ->toArray();
                
                Log::info("Ticket IDs to delete: " . implode(', ', $ticketIds));
                
                // Delete attachments linked to messages in these tickets
                $attachmentsDeleted = DB::delete("
                    DELETE FROM ticket_attachments 
                    WHERE message_id IN (
                        SELECT id FROM support_messages 
                        WHERE ticket_id IN (" . implode(',', $ticketIds) . ")
                    )
                ");
                Log::info("Deleted {$attachmentsDeleted} ticket attachments");
                
                // Delete messages for these tickets
                $messagesDeleted = DB::delete("
                    DELETE FROM support_messages 
                    WHERE ticket_id IN (" . implode(',', $ticketIds) . ")
                ");
                Log::info("Deleted {$messagesDeleted} support messages");
                
                // Delete the tickets themselves with direct query
                $ticketsDeleted = DB::delete("DELETE FROM support_tickets WHERE store_id = ?", [$storeId]);
                Log::info("Deleted {$ticketsDeleted} support tickets");
            }
            
            // Delete any other known relations 
            // Products
            $productsDeleted = DB::table('products')->where('store_id', $storeId)->delete();
            Log::info("Deleted {$productsDeleted} products");
            
            // Categories
            $categoriesDeleted = DB::table('categories')->where('store_id', $storeId)->delete();
            Log::info("Deleted {$categoriesDeleted} categories");
            
            // StoreUser relations
            $storeUsersDeleted = DB::table('store_users')->where('store_id', $storeId)->delete();
            Log::info("Deleted {$storeUsersDeleted} store user relationships");
            
            // Delete the store with direct query
            $storeDeleted = DB::delete("DELETE FROM stores WHERE id = ?", [$storeId]);
            Log::info("Store deletion result: {$storeDeleted}");
            
            // Drop tenant database if it exists
            if ($store->database_created) {
                $databaseName = 'tenant_' . $store->slug;
                $this->databaseManager->dropDatabase($databaseName);
                Log::info("Dropped tenant database {$databaseName}");
            }
            
            DB::commit();
            
            return redirect()->route('stores.index')
                ->with('success', "Store '{$storeName}' and all its data deleted successfully.");
        } 
        catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Failed to delete store: " . $e->getMessage(), [
                'store_id' => $store->id, 
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('stores.index')
                ->with('error', 'Failed to delete store: ' . $e->getMessage());
        }
    }

public function approve(Request $request, Store $store)
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
            $store->update(['database_created' => true]);
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
        
    /**
     * Update the pricing tier for a store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function updatePricingTier(Request $request, Store $store)
    {
        // First, let's log what we're receiving
        Log::debug('Pricing tier update request', [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'request_data' => $request->all()
        ]);
        
        // Validate the request
        $request->validate([
            'pricing_tier_id' => 'required|exists:pricing_tiers,id',
            'billing_cycle' => 'required|in:monthly,annual',
        ]);
        
        // Get the tier for logging/feedback
        $pricingTier = \App\Models\PricingTier::find($request->pricing_tier_id);
        
        // Use direct query builder like in the test route that works
        DB::table('stores')
            ->where('id', $store->id)
            ->update([
                'pricing_tier_id' => $request->pricing_tier_id,
                'billing_cycle' => $request->billing_cycle,
                'auto_renew' => $request->has('auto_renew') ? 1 : 0,
            ]);
        
        // Update dates if requested
        if ($request->has('reset_dates')) {
            $now = now();
            $end = $request->billing_cycle === 'monthly' ? $now->copy()->addMonth() : $now->copy()->addYear();
            
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