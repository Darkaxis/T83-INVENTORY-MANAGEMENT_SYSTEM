<?php
// filepath: d:\WST\inventory-management-system\app\Console\Commands\CreateTenantDatabases.php

namespace App\Console\Commands;

use App\Models\Store;
use App\Services\TenantDatabaseManager;
use Illuminate\Console\Command;

class CreateTenantDatabases extends Command
{
    protected $signature = 'tenant:create-databases {--force : Force creation without asking for confirmation}';
    protected $description = 'Create databases for all approved stores that need them';

    public function handle()
    {
        $stores = Store::approved()->where('database_created', false)->get();
        
        if ($stores->isEmpty()) {
            $this->info('No approved stores without databases found.');
            return 0;
        }
        
        $this->info("Found {$stores->count()} approved stores that need database creation:");
        
        $this->table(
            ['ID', 'Name', 'Slug', 'Status'],
            $stores->map(fn($store) => [
                'id' => $store->id,
                'name' => $store->name,
                'slug' => $store->slug,
                'status' => $store->status,
            ])
        );
        
        if (!$this->option('force') && !$this->confirm('Do you want to create databases for these stores?')) {
            $this->info('Operation cancelled.');
            return 0;
        }
        
        $databaseManager = app(TenantDatabaseManager::class);
        $successCount = 0;
        $failCount = 0;
        
        $this->output->progressStart($stores->count());
        
        foreach ($stores as $store) {
            $this->output->progressAdvance();
            
            $result = $databaseManager->createTenantDatabase($store);
            
            if ($result) {
                $store->update(['database_created' => true]);
                $this->info("Created database for {$store->name} (ID: {$store->id})");
                $successCount++;
            } else {
                $this->error("Failed to create database for {$store->name} (ID: {$store->id})");
                $failCount++;
            }
        }
        
        $this->output->progressFinish();
        
        $this->info("Database creation completed: {$successCount} successful, {$failCount} failed.");
        
        return ($failCount === 0) ? 0 : 1;
    }
}