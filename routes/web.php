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
use App\Http\Middleware\EnsureTenantSession;


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


Route::post('/stores/{store}/status', [StoreController::class, 'status'])->name('stores.toggleStatus');
Route::post('/stores/{store}/approve', [StoreController::class, 'approve'])->name('stores.approve');
// Products management per store from admin panel
Route::get('/stores/{store}/products', [StoreProductController::class, 'adminIndex'])->name('admin.stores.products.index');

// Tenant/subdomain routes
// Route::middleware(['store.check'])->group(function () {
//     // Store public pages
//     Route::get('/', function (Request $request) {
//         $store = $request->store;
//         return view('stores.storefront', compact('store'));
//     });
    
//     // Store admin panel - requires authentication
//     Route::middleware(['auth'])->prefix('admin')->group(function () {
//         // Store dashboard
//         Route::get('/dashboard', function (Request $request) {
//             $store = $request->store;
            
//             if (!$store) {
//                 return redirect('/')->with('error', 'Store not found');
//             }
            
//             // Since we're already connected to the tenant database via middleware,
//             // we can directly query the tables
//             $lowStockCount = DB::table('products')->whereRaw('stock <= min_stock')->count();
//             $recentProducts = DB::table('products')->orderBy('created_at', 'desc')->limit(5)->get();
//             $categoriesCount = DB::table('categories')->count();
            
//             return view('stores.dashboard', compact('store', 'lowStockCount', 'recentProducts', 'categoriesCount'));
//         })->name('store.dashboard');
        
//         // Products management for this store only
//         Route::resource('products', ProductController::class);
        
//         // Staff management for this store only
//         Route::resource('staff', StaffController::class);
//     });
// });


Route::middleware(['web', 'guest'])->group(function () {
    Route::get('/', function (Request $request) {
        // Check if we're on a subdomain
        $host = $request->getHost();
        $parts = explode('.', $host);
        $isSubdomain = count($parts) > 2 || (count($parts) > 1 && $parts[0] !== 'www' && $parts[0] !== 'localhost');
        
        if ($isSubdomain) {
            // For subdomain, pass the store to the login view
            $subdomain = $parts[0];
            $store = \App\Models\Store::where('slug', $subdomain)->first();
            
            if (!$store) {
                abort(404, 'Store not found');
            }
            
            
            session(['tenant_store' => $store->id]);
            session(['tenant_slug' => $store->slug]);
            return view('login', compact('store'));  
        }
        
        // Regular login for main domain
        return view('login');
    })->name('login');
    
    // Make sure this POST route includes the 'web' middleware for CSRF protection
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
});


// Google authentication
Route::get('auth/google', [LoginController::class, 'redirectToGoogle'])->name('login.google');
Route::get('auth/google/callback', [LoginController::class, 'handleGoogleCallback']);

// Logout
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// Debugging route






// Subdomain routes for tenant stores
Route::domain('{subdomain}.localhost')->middleware(['web', 'tenant'])->group(function () {
    // This ensures the web middleware is applied, which includes session and CSRF handling
    
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });
    
    // Add a tenant debug route
    Route::get('/debug-tenant', function (Request $request, $subdomain) {
        $store = \App\Models\Store::where('slug', $subdomain)->first();
        
        if ($store) {
            session(['tenant_store' => $store->id]);
            session(['tenant_slug' => $store->slug]);
        }
        
        return [
            'csrf_token' => csrf_token(),
            'session_id' => session()->getId(),
            'tenant_store' => session('tenant_store'),
            'tenant_slug' => session('tenant_slug'),
            'subdomain' => $subdomain,
            'store' => $store ? $store->toArray() : null,
        ];
    });
});

Route::domain('{subdomain}.localhost')->middleware(['web'])->group(function () {
    Route::get('/test-form', function () {
        return '<form method="POST" action="/test-form-submit">
            '.csrf_field().'
            <input type="text" name="test" value="test">
            <button type="submit">Submit</button>
        </form>';
    });
    
    Route::post('/test-form-submit', function (Request $request) {
        return "Form submitted successfully with data: " . $request->test;
    });
});


Route::get('/request-store', [App\Http\Controllers\StoreController::class, 'showStoreRequestForm'])
    ->name('public.store-requests.create');
Route::post('/request-store', [App\Http\Controllers\StoreController::class, 'publicStorecreate'])
    ->name('public.store-requests.store');
Route::get('/request-store/thank-you/{storeRequest}', [App\Http\Controllers\StoreController::class, 'thankYou'])
    ->name('public.store-requests.thank-you');

    // Debug route - remove in production
Route::get('/test-email', [StoreController::class, 'testEmail'])
    ->name('test.email');