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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}