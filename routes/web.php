<?php
// filepath: d:\WST\inventory-management-system\routes\web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TenantDashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StoreProductController;
use App\Http\Controllers\StoreStaffController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\SystemUpdateController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\TenantSettingsController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Admin\PricingTierController;
use App\Http\Controllers\StoreSettingsController;
use App\Models\Store;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ReportController;

/**
 * Public Routes (No Auth Required)
 */
Route::middleware(['web'])->group(function () {
    // Landing page with login
    Route::get('/', function (Request $request) {
        // Check if we're on a subdomain
        $host = $request->getHost();
    
        $segments = explode('.', $host);
        
       
        $subdomain = null;

        
    if (count($segments) === 3 && $segments[1] === 'inventory' && $segments[2] === 'test') {
        $subdomain = $segments[0];
        Log::info('Subdomain detected', ['host' => $host, 'subdomain' => $subdomain]);

        
            $store = Store::where('slug', $subdomain)->first();
            
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
Route::middleware(['web', 'admin'])->prefix('admin')->group(function () {
    // Dashboard
  
    Route::resource('pricing-tiers', PricingTierController::class);
    // Tenant settings
    Route::get('/settings/tenant', [TenantSettingsController::class, 'index'])->name('admin.settings.tenant');
    Route::put('/settings/tenant', [TenantSettingsController::class, 'update'])->name('admin.settings.tenant.update');
});

Route::prefix('system')->name('admin.system.')->middleware([ 'admin'])->group(function () {
    // Updates listing page
    Route::get('/updates', [App\Http\Controllers\Admin\UpdateController::class, 'index'])->name('updates');
    
    // Check for updates
    Route::post('/updates/check', [App\Http\Controllers\Admin\UpdateController::class, 'check'])->name('updates.check');
    
    // Download specific update
    Route::post('/updates/{id}/download', [App\Http\Controllers\Admin\UpdateController::class, 'download'])->name('updates.download');
    
    Route::post('/updates/process', [App\Http\Controllers\Admin\UpdateController::class, 'update'])->name('updates.process');
    
    // Install specific update
    Route::post('/updates/{id}/install', [App\Http\Controllers\Admin\UpdateController::class, 'install'])->name('updates.install');
    
    // Rollback route
    Route::post('/update/rollback', [App\Http\Controllers\Admin\UpdateController::class, 'rollback'])->name('update.rollback');

});

Route::middleware(['web', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('stores', StoreController::class);
    Route::post('/stores/{store}/status', [StoreController::class, 'status'])->name('stores.toggleStatus');
    Route::post('/stores/{store}/approve', [StoreController::class, 'approve'])->name('stores.approve');
    Route::post('/stores/{store}/pricing-tier', [StoreController::class, 'updatePricingTier'])->name('stores.updatePricingTier');
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
    
   
});


Route::get('/favicon/{store}', function (App\Models\Store $store) {
    if ($store->logo_binary) {
        // For favicon, we don't need to send a large image
        // We return the binary data directly with appropriate content type
        return response($store->logo_binary)
            ->header('Content-Type', $store->logo_mime_type)
            ->header('Cache-Control', 'public, max-age=86400'); // Cache for 1 day
    }
    
    // Return default favicon if store has no logo
    return response()->file(public_path('assets/img/favicon.png'));
})->name('store.favicon');

// Add to your routes file
Route::get('/logo/{store}', function (Store $store) {
    if ($store->logo_binary) {
        return response($store->logo_binary)
            ->header('Content-Type', $store->logo_mime_type);
    }
    
    // Return default logo if none exists
    return response()->file(public_path('assets/img/default-logo.png'));
})->name('store.logo');

Route::domain('{subdomain}.inventory.test')->middleware(['web', 'tenant.check', 'tenant'])->group(function () {
    // Dashboard - default landing page
    Route::get('/', [TenantDashboardController::class, 'index'])->name('tenant.dashboard');
    
    // Product management - Available to all tiers
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product_id}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product_id}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product_id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product_id}', [ProductController::class, 'destroy'])->name('destroy');
        Route::get('/search', [ProductController::class, 'search'])->name('search');
    });
    
    // Checkout system - Available to all tiers
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::get('/search', [CheckoutController::class, 'searchProducts'])->name('search');
        Route::post('/process', [CheckoutController::class, 'process'])->name('process');
        Route::get('/receipt/{sale_id}', [CheckoutController::class, 'receipt'])->name('receipt');
        
        // Checkout history - Starter tier and above
        Route::get('/history', [CheckoutController::class, 'history'])
            ->name('history')
            ->middleware(['subscription.tier:starter']);
    });
    
    // Subscription management
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::post('/upgrade', [SubscriptionController::class, 'upgrade'])->name('upgrade');
    });
    
    // Staff management - Manager role
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('index');
        Route::get('/create', [StaffController::class, 'create'])->name('create');
        Route::post('/', [StaffController::class, 'store'])->name('store');
        Route::get('/{staff_id}/edit', [StaffController::class, 'edit'])->name('edit');
        Route::put('/{staff_id}', [StaffController::class, 'update'])->name('update');
        Route::delete('/{staff_id}', [StaffController::class, 'destroy'])->name('destroy');
        Route::post('/{staff_id}/reset-password', [StaffController::class, 'resetPassword'])->name('reset-password');
    });
    
    // Store Settings - Starter tier and above, manager role
    Route::prefix('settings')->name('settings.')->middleware(['subscription.tier:starter',])->group(function () {
        Route::get('/', [StoreSettingsController::class, 'index'])->name('index');
        Route::post('/', [StoreSettingsController::class, 'update'])->name('update');
    });
    
    // Reports - Pro tier only
    Route::prefix('reports')->name('reports.')->middleware(['subscription.tier:pro'])->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/sales/export/pdf', [ReportController::class, 'exportSalesPdf'])->name('sales.export.pdf');
        Route::get('/sales/export/csv', [ReportController::class, 'exportSalesCsv'])->name('sales.export.csv');
        Route::get('/products/export/csv', [ReportController::class, 'exportProductsCsv'])->name('reports.products.export.csv');
    });
    
    // User profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/password', [ProfileController::class, 'showChangePasswordForm'])->name('password');
        Route::post('/password', [ProfileController::class, 'changePassword'])->name('update-password');
    });
});
