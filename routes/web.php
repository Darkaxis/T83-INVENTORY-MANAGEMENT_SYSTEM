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
use App\Http\Controllers\Admin\TenantSettingsController;

/**
 * Public Routes (No Auth Required)
 */
Route::middleware(['web'])->group(function () {
    // Landing page with login
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
    
    // Store request public flow
    Route::get('/request-store', [StoreController::class, 'showStoreRequestForm'])
        ->name('public.store-requests.create');
    Route::post('/request-store', [StoreController::class, 'publicStorecreate'])
        ->name('public.store-requests.store');
    Route::get('/request-store/thank-you/{storeRequest}', [StoreController::class, 'thankYou'])
        ->name('public.store-requests.thank-you');
});

/**
 * Authentication Routes
 */
Route::middleware(['web'])->group(function () {
    // Login/logout routes
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Google authentication
    Route::get('auth/google', [LoginController::class, 'redirectToGoogle'])->name('login.google');
    Route::get('auth/google/callback', [LoginController::class, 'handleGoogleCallback']);
});

/**
 * Main System Admin Routes
 */
Route::middleware(['web', 'auth', 'admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // Tenant settings
    Route::get('/settings/tenant', [TenantSettingsController::class, 'index'])->name('admin.settings.tenant');
    Route::put('/settings/tenant', [TenantSettingsController::class, 'update'])->name('admin.settings.tenant.update');
});

/**
 * Store Management Routes (Admin Panel)
 */
Route::middleware(['web', 'auth.multi'])->group(function () {
    // Store resource
    Route::resource('stores', StoreController::class);
    Route::post('/stores/{store}/status', [StoreController::class, 'status'])->name('stores.toggleStatus');
    Route::post('/stores/{store}/approve', [StoreController::class, 'approve'])->name('stores.approve');
    
    // Store staff management
    Route::prefix('stores/{store}')->group(function () {
        Route::get('/staff', [StoreStaffController::class, 'index'])->name('admin.stores.staff.index');
        Route::get('/staff/create', [StoreStaffController::class, 'create'])->name('admin.stores.staff.create');
        Route::post('/staff', [StoreStaffController::class, 'store'])->name('admin.stores.staff.store');
        Route::get('/staff/{id}', [StoreStaffController::class, 'show'])->name('admin.stores.staff.show');
        Route::get('/staff/{id}/edit', [StoreStaffController::class, 'edit'])->name('admin.stores.staff.edit');
        Route::put('/staff/{id}', [StoreStaffController::class, 'update'])->name('admin.stores.staff.update');
        Route::delete('/staff/{id}', [StoreStaffController::class, 'destroy'])->name('admin.stores.staff.destroy');
        Route::get('/staff/{id}/reset-password', [StoreStaffController::class, 'resetPassword'])->name('admin.stores.staff.reset_password');
        Route::post('/staff/{id}/update-password', [StoreStaffController::class, 'updatePassword'])->name('admin.stores.staff.update_password');
        
        // Products management per store
        Route::get('/products', [StoreProductController::class, 'adminIndex'])->name('admin.stores.products.index');
    });
    
    // Alternative dashboard route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

/**
 * Tenant Subdomain Routes
 */
Route::domain('{subdomain}.localhost')->middleware(['web', 'tenant'])->group(function () {
    // Product management
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product_id}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product_id}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product_id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product_id}', [ProductController::class, 'destroy'])->name('destroy');
    });
    
    // Tenant debug information
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

/**
 * Debug/Testing Routes
 * Remove these in production
 */
Route::domain('{subdomain}.localhost')->middleware(['web'])->group(function () {
    // CSRF test form
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



// Debug route - remove in production
Route::get('/test-email', [StoreController::class, 'testEmail'])->name('test.email');