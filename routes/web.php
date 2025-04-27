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

Route::domain('{subdomain}.inventory.test')->middleware(['web','tenant.check' , 'tenant'])->group(function () {
    // Product management
    Route::get('/tenantdashboard', [TenantDashboardController::class, 'index'])->name('tenant.dashboard');
    Route::prefix('/products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product_id}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product_id}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product_id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product_id}', [ProductController::class, 'destroy'])->name('destroy');
    });
        // Add this with your other product routes
    Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('tenant.subscription');
    Route::post('/subscription/upgrade', [SubscriptionController::class, 'upgrade'])->name('tenant.subscription.upgrade');
    // Add this to routes/web.php inside the subdomain route group
    

    // Staff management
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{staff_id}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{staff_id}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{staff_id}', [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::post('/staff/{staff_id}/reset-password', [StaffController::class, 'resetPassword'])->name('staff.reset-password');
        
    // Starter tier routes
        Route::get('/checkout/history', [CheckoutController::class, 'history'])
->name('checkout.history')
->middleware(['auth', 'subscription.tier']);
    // Profile - Password change
    Route::get('/profile/password', [ProfileController::class, 'showChangePasswordForm'])->name('profile.password');
    Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.update-password');
    
    // Checkout routes
    // Free tier routes (no middleware needed as this is the base access level)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');

// Starter tier routes
Route::get('/checkout/history', [CheckoutController::class, 'history'])
    ->name('checkout.history')
    ->middleware([ 'subscription.tier:starter']);

// Manager-only routes that also need starter tier
Route::group(['middleware' => ['subscription.tier:starter']], function () {
    Route::get('/settings', [StoreSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [StoreSettingsController::class, 'update'])->name('settings.update');
});

// Pro tier routes
Route::get('/reports', [ReportController::class, 'index'])
    ->name('reports.index')
    ->middleware([ 'subscription.tier:pro']);



        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::get('/checkout/search', [CheckoutController::class, 'searchProducts'])->name('checkout.search');
        Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
        Route::get('/checkout/receipt/{sale_id}', [CheckoutController::class, 'receipt'])->name('checkout.receipt');
        Route::get('/checkout/history', [CheckoutController::class, 'history'])->name('checkout.history');
   
});

/**
 * Debug/Testing Routes
 * Remove these in production
 */
Route::domain('{subdomain}.inventory.test')->group(function () {
    // Existing routes...
    
    // Add this new route
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
});

Route::domain('{subdomain}.inventory.test')->middleware(['web'])->group(function () {
    // CSRF test form
    
    Route::get('/test-form', function () {
        return '<form method="POST" action="/test-form-submit">
            '.csrf_field().'
            <input type="text" name="test" value="test">
            <button type="submit">Submit</button>
        </form>';
    });
    
  
});

Route::get('/session-test-page', function() {
    // Get current value first before modifying
    $currentCount = session('page_loads', 0);
    $newCount = $currentCount + 1;
    
    // Set values individually with explicit save
    session(['page_loads' => $newCount]);
    session(['is_tenant' => true]);
    session(['test_time' => date('H:i:s')]);
    
    // Force save the session after updates
    session()->save();
    
    // Build a simple HTML page
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Session Test</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            div { margin-bottom: 20px; }
            pre { background: #f5f5f5; padding: 10px; }
        </style>
    </head>
    <body>
        <h1>Session Test Page</h1>
        
        <div>
            <h3>Session ID: ' . session()->getId() . '</h3>
        </div>
        
        <div>
            <h3>Current Session Data:</h3>
            <pre>' . json_encode(session()->all(), JSON_PRETTY_PRINT) . '</pre>
        </div>
        
        <div>
            <h3>Page load count: ' . $newCount . '</h3>
            <p>Previous count: ' . $currentCount . '</p>
            <p>Last loaded at: ' . date('H:i:s') . '</p>
        </div>
        
        <div>
            <h3>Test Links (click these to test session persistence):</h3>
            <ul>
                <li><a href="/session-test-page">Reload this page</a></li>
                <li><a href="/session-debug">View raw session debug data</a></li>
                <li><a href="/clear-session">Clear session</a></li>
            </ul>
        </div>
    </body>
    </html>';
    
    return $html;
});

// Add a session clearing route
Route::get('/clear-session', function() {
    session()->flush();
    return redirect('/session-test-page')->with('message', 'Session cleared');
});
// Add with other public routes
Route::get('/clear-session', function() {
    session()->flush();
    return redirect('/session-test-page')->with('message', 'Session cleared');
});

Route::get('/session-debug', function() {
    // Try setting a value
    $testValue = 'test-' . time();
    session(['debug_value' => $testValue]);
    
    // Get session driver and storage details
    $sessionDriver = config('session.driver');
    $sessionPath = storage_path('framework/sessions');
    $sessionFiles = [];
    
    if ($sessionDriver == 'file') {
        if (is_dir($sessionPath)) {
            $files = scandir($sessionPath);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $sessionFiles[] = $file;
                }
            }
        }
    }
    
    // Force save
    session()->save();
    
    return [
        'test_value_set' => $testValue,
        'session_id' => session()->getId(),
        'test_retrieved' => session('debug_value'),
        'page_loads' => session('page_loads'),
        'driver' => $sessionDriver,
        'all_data' => session()->all(),
        'is_started' => session()->isStarted(),
        'session_files_count' => count($sessionFiles),
        'recent_session_files' => array_slice($sessionFiles, -5),
    ];
});

Route::get('/test-update-pricing/{storeId}/{tierId}', function($storeId, $tierId) {
    $store = \App\Models\Store::find($storeId);
    $result = DB::statement("UPDATE stores SET pricing_tier_id = ? WHERE id = ?", [$tierId, $storeId]);
    
    return [
        'success' => $result,
        'store' => \App\Models\Store::find($storeId),
        'pricing_tier' => \App\Models\PricingTier::find($tierId)
    ];
});

