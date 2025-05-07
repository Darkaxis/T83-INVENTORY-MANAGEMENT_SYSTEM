<?php
// filepath: d:\WST\inventory-management-system\app\Providers\AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TenantDatabaseManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the TenantDatabaseManager as a singleton
        $this->app->singleton(TenantDatabaseManager::class, function ($app) {
            return new TenantDatabaseManager();
        });

        $this->app->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
            \App\Console\Kernel::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        if (app()->environment('local')) {
            // Special handling for localhost development
            $host = request()->getHost();
            
            // Check if we're on a localhost subdomain
            if (str_contains($host, 'localhost')) {
                // Force the session driver to use a domain that works with subdomains
                config(['session.domain' => '.localhost']);
                
                // For some browsers that don't respect the dot prefix on localhost
                config(['session.same_site' => 'none']);
                
                // If you're using HTTP, allow non-secure cookies
                if (!request()->secure()) {
                    config(['session.secure' => false]);
                }
            }
        }
    }
}