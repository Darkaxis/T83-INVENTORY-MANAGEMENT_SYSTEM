<?php
// filepath: d:\WST\inventory-management-system\bootstrap\app.php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Console\Commands\CreateTenantDatabases;
use App\Console\Commands\TenantMigrate;
use App\Console\Commands\TenantDebug;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'store.subdomain' => \App\Http\Middleware\StoreSubdomainMiddleware::class,
            'store.access' => \App\Http\Middleware\StoreAccessMiddleware::class,
            'store.check' => \App\Http\Middleware\CheckStoreSubdomain::class,
            'store.approval' => \App\Http\Middleware\CheckStoreApprovalMiddleware::class,
            'tenant' => \App\Http\Middleware\EnsureTenantSession::class,
            'tenant.manager' => \App\Http\Middleware\TenantManagerMiddleware::class,
            'tenant.check' => \App\Http\Middleware\CheckTenantAccount::class,
            'subscription.tier' => \App\Http\Middleware\CheckSubscriptionTier::class,
    
        ])
        ->validateCsrfTokens(except: [
            'products',
            'products/*',
            'login',
            'login/*',
            'logout',
        ]);
        ;
        
      

        // Add middleware to the web group that checks store approval
        $middleware->web([
            \App\Http\Middleware\CheckStoreApprovalMiddleware::class,
            \App\Http\Middleware\VerifyCsrfToken::class

        ]);
    })
    
    ->withCommands([
        CreateTenantDatabases::class,
        TenantMigrate::class,
        \App\Console\Commands\TestGmail::class,
        \App\Console\Commands\SeedTenantProducts::class,
        \App\Console\Commands\SetupDemoStore::class,
      
    ])
    //aliases

    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();