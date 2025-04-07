<?php
// filepath: d:\WST\inventory-management-system\app\Http\Controllers\StoreController.php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\TenantDatabaseManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    protected $databaseManager;
    
    public function __construct(TenantDatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }
    
    /**
     * Display a listing of the stores.
     */
    public function index()
    {
        try {
            // The accessors will be automatically called when needed
            $stores = Store::all();
            
            // Check each store's database connection status
            foreach ($stores as $store) {
                try {
                    // Check database connection status without modifying properties directly
                    // This uses the accessor we defined in the Store model
                    $store->database_connected;
                } catch (\Exception $e) {
                    Log::warning("Error checking database for store: {$store->id}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            return view('stores.index', compact('stores'));
        } catch (\Exception $e) {
            Log::error("Error in store index: " . $e->getMessage());
            return view('stores.index', [
                'stores' => [],
                'error' => 'Error loading stores: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Show the form for creating a new store.
     */
    public function create()
    {
        return view('stores.create');
    }
    
    /**
     * Store a newly created store in storage.
     */
        /**
     * Store a newly created store in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:stores|alpha_dash',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Create the store in the main database with only validated data
            $store = new Store();
            $store->name = $validated['name'];
            $store->slug = $validated['slug'];
            $store->email = $validated['email'] ?? null;
            $store->phone = $validated['phone'] ?? null;
            $store->address = $validated['address'] ?? null;
            $store->city = $validated['city'] ?? null;
            $store->state = $validated['state'] ?? null;
            $store->zip = $validated['zip'] ?? null;
            $store->status = $validated['status'];
            
            // Explicitly save the store
            $saved = $store->save();
            
            if (!$saved) {
                throw new \Exception("Failed to save store record");
            }
            
            Log::info("Store created successfully", ['store_id' => $store->id, 'slug' => $store->slug]);
            
            // Database creation should be handled in the model's created event
            // But let's make sure it happens
            $result = $this->databaseManager->createTenantDatabase($store);
            
            if (!$result) {
                throw new \Exception("Failed to create store database");
            }
            
            DB::commit();
            
            // Verify the database was created successfully
            try {
                $this->databaseManager->switchToTenant($store);
                $this->databaseManager->switchToMain();
                
                Log::info("Store database connected successfully", ['store_id' => $store->id]);
            } catch (\Exception $e) {
                Log::error("Database connection verification failed for store: {$store->id}", [
                    'error' => $e->getMessage()
                ]);
                
                return redirect()
                    ->route('stores.index')
                    ->with('warning', 'Store created but there may be issues with the database. Please verify it works correctly.');
            }
            
            return redirect()
                ->route('stores.index')
                ->with('success', 'Store created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Failed to create store: " . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create store: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified store.
     */
    public function show(Store $store)
    {
        try {
            // Check database connection status
            $dbExists = $store->database_connected;
            
            return view('stores.show', compact('store', 'dbExists'));
        } catch (\Exception $e) {
            Log::error("Error showing store: " . $e->getMessage(), [
                'store_id' => $store->id
            ]);
            
            return redirect()
                ->route('stores.index')
                ->with('error', 'Error loading store details: ' . $e->getMessage());
        }
    }
    
    /**
     * Show the form for editing the specified store.
     */
    public function edit(Store $store)
    {
        return view('stores.edit', compact('store'));
    }
    
    /**
     * Update the specified store in storage.
     */
    public function update(Request $request, Store $store)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
        ]);
        
        try {
            // Don't allow changing the slug as it would break database connectivity
            $request->request->remove('slug');
            
            $store->update($request->all());
            
            return redirect()
                ->route('stores.index')
                ->with('success', 'Store updated successfully!');
        } catch (\Exception $e) {
            Log::error("Error updating store: " . $e->getMessage(), [
                'store_id' => $store->id,
                'request' => $request->all()
            ]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error updating store: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified store from storage.
     */
    public function destroy(Store $store)
    {
        try {
            Log::info("Attempting to delete store and database", ['store_id' => $store->id]);
            
            // The store's deleted event will handle dropping the database
            $store->delete();
            
            return redirect()
                ->route('stores.index')
                ->with('success', 'Store deleted successfully!');
                
        } catch (\Exception $e) {
            Log::error("Failed to delete store: " . $e->getMessage(), [
                'store_id' => $store->id,
                'exception' => $e
            ]);
            
            return redirect()
                ->route('stores.index')
                ->with('error', 'Failed to delete store: ' . $e->getMessage());
        }
    }
    
    /**
     * Rebuild a store's database if it's missing or corrupted.
     */
    public function rebuildDatabase(Store $store)
    {
        try {
            // Drop the database if it exists
            $this->databaseManager->dropTenantDatabase($store);
            
            // Create a new database
            $result = $this->databaseManager->createTenantDatabase($store);
            
            if (!$result) {
                return redirect()
                    ->route('stores.show', $store)
                    ->with('error', 'Failed to rebuild database for this store.');
            }
            
            return redirect()
                ->route('stores.show', $store)
                ->with('success', 'Database rebuilt successfully!');
        } catch (\Exception $e) {
            Log::error("Failed to rebuild database: " . $e->getMessage(), [
                'store_id' => $store->id
            ]);
            
            return redirect()
                ->route('stores.show', $store)
                ->with('error', 'Failed to rebuild database: ' . $e->getMessage());
        }
    }
    
    /**
     * Display tenant dashboard
     * This is accessed from the tenant subdomain
     */
    public function dashboard()
    {
        // This would be accessed from a tenant subdomain
        // Authentication should already be handled by middleware
        
        // Get the current store from session (set by middleware)
        $storeId = session('current_store_id');
        
        if (!$storeId) {
            return redirect('/login')->with('error', 'Store session not found');
        }
        
        try {
            // We're already in the tenant database context thanks to middleware
            $user = Auth::user();
            
            return view('tenant.dashboard', [
                'user' => $user,
                'store_name' => session('current_store_name')
            ]);
        } catch (\Exception $e) {
            Log::error("Error loading tenant dashboard: " . $e->getMessage());
            return redirect('/login')->with('error', 'Error loading dashboard');
        }
    }
    
    /**
     * Tenant selector for admin user
     */
    public function tenantSelector()
    {
        $stores = Store::where('status', 'active')->get();
        return view('admin.tenant-selector', compact('stores'));
    }
}