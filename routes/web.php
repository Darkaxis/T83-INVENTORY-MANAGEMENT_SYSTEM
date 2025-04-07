<?php
// filepath: d:\WST\inventory-management-system\routes\web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StoreProductController;
use App\Http\Controllers\StoreStaffController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\TenantDatabaseManager;

// Main domain admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Define the dashboard route
   
});
    
// Store management routes
Route::resource('stores', StoreController::class);

Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
// Staff management per store from admin panel
Route::get('/stores/{store}/staff', [StoreStaffController::class, 'index'])->name('admin.stores.staff.index');
Route::get('/stores/{store}/staff/create', [StoreStaffController::class, 'create'])->name('admin.stores.staff.create');
Route::post('/stores/{store}/staff', [StoreStaffController::class, 'store'])->name('admin.stores.staff.store');
 Route::get('/settings/tenant', [App\Http\Controllers\Admin\TenantSettingsController::class, 'index'])
        ->name('admin.settings.tenant');
    Route::put('/settings/tenant', [App\Http\Controllers\Admin\TenantSettingsController::class, 'update'])
        ->name('admin.settings.tenant.update');
Route::get('/stores/{store}/staff/{id}', [StoreStaffController::class, 'show'])->name('admin.stores.staff.show');
Route::get('/stores/{store}/staff/{id}/edit', [StoreStaffController::class, 'edit'])->name('admin.stores.staff.edit');
Route::put('/stores/{store}/staff/{id}', [StoreStaffController::class, 'update'])->name('admin.stores.staff.update');
Route::delete('/stores/{store}/staff/{id}', [StoreStaffController::class, 'destroy'])->name('admin.stores.staff.destroy');
Route::get('/stores/{store}/staff/{id}/reset-password', [StoreStaffController::class, 'resetPassword'])->name('admin.stores.staff.reset_password');
Route::post('/stores/{store}/staff/{id}/update-password', [StoreStaffController::class, 'updatePassword'])->name('admin.stores.staff.update_password');

// Products management per store from admin panel
Route::get('/stores/{store}/products', [StoreProductController::class, 'adminIndex'])->name('admin.stores.products.index');

// Tenant/subdomain routes
Route::middleware(['store.check'])->group(function () {
    // Store public pages
    Route::get('/', function (Request $request) {
        $store = $request->store;
        return view('stores.storefront', compact('store'));
    });
    
    // Store admin panel - requires authentication
    Route::middleware(['auth'])->prefix('admin')->group(function () {
        // Store dashboard
        Route::get('/dashboard', function (Request $request) {
            $store = $request->store;
            
            if (!$store) {
                return redirect('/')->with('error', 'Store not found');
            }
            
            // Since we're already connected to the tenant database via middleware,
            // we can directly query the tables
            $lowStockCount = DB::table('products')->whereRaw('stock <= min_stock')->count();
            $recentProducts = DB::table('products')->orderBy('created_at', 'desc')->limit(5)->get();
            $categoriesCount = DB::table('categories')->count();
            
            return view('stores.dashboard', compact('store', 'lowStockCount', 'recentProducts', 'categoriesCount'));
        })->name('store.dashboard');
        
        // Products management for this store only
        Route::resource('products', ProductController::class);
        
        // Staff management for this store only
        Route::resource('staff', StaffController::class);
    });
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/', function (Request $request) {
        // Check if we're on a subdomain
        $host = $request->getHost();
        $parts = explode('.', $host);
        $isSubdomain = count($parts) > 2 || ($parts[0] !== 'www' && $parts[0] !== 'localhost');
        
        if ($isSubdomain) {
            // For subdomain, pass the store to the login view
            $subdomain = $parts[0];
            $store = \App\Models\Store::where('slug', $subdomain)->first();
            
            if (!$store) {
                abort(404, 'Store not found');
            }
            
            return view('login', compact('store'));
        }
        
        // Regular login for main domain
        return view('login');
    })->name('login');
    
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
});

// Google authentication
Route::get('auth/google', [LoginController::class, 'redirectToGoogle'])->name('login.google');
Route::get('auth/google/callback', [LoginController::class, 'handleGoogleCallback']);

// Logout
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// Debugging route
Route::get('/debug/database', function(Request $request) {
    if (!app()->environment('local')) {
        abort(403); // Only allow in local environment
    }
    
    // Check for a specific store
    $storeId = $request->get('store_id');
    if ($storeId) {
        $store = \App\Models\Store::find($storeId);
        if (!$store) {
            return "Store not found";
        }
        
        $manager = app(TenantDatabaseManager::class);
        $manager->switchToTenant($store);
        
        $tables = DB::select('SHOW TABLES');
        $data = [];
        
        foreach ($tables as $table) {
            $tableName = array_values(get_object_vars($table))[0];
            $count = DB::table($tableName)->count();
            $data[$tableName] = $count;
        }
        
        $manager->switchToMain();
        
        return [
            'store' => $store->toArray(),
            'database' => 'tenant_' . $store->slug,
            'tables' => $data
        ];
    }
    
    // List all stores
    $stores = \App\Models\Store::all()->map(function($store) {
        return [
            'id' => $store->id,
            'name' => $store->name,
            'slug' => $store->slug,
            'database' => 'tenant_' . $store->slug
        ];
    });
    
    return $stores;
});