<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Store;
use Illuminate\Support\Facades\Artisan;

class SetupDemoStore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:demo {--no-products : Skip product seeding} {--no-users : Skip user seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup and seed the demo store in one command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up demo store...');
        
        // Run the DemoStoreWithProductsSeeder to ensure the store exists
        $this->info('Creating demo store if it doesn\'t exist...');
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DemoStoreWithProductsSeeder']);
        $this->info(Artisan::output());
        
        // Create the tenant database
        $this->info('Creating tenant database for demo store...');
        Artisan::call('tenant:create-databases', ['--store' => 'demo']);
        $this->info(Artisan::output());
        
        // Migrate the tenant database
        $this->info('Running migrations for demo tenant database...');
        Artisan::call('tenant:migrate', ['--store' => 'demo']);
        $this->info(Artisan::output());
        
        // Seed products for the demo store if not skipped
        if (!$this->option('no-products')) {
            $this->info('Seeding products for demo store...');
            Artisan::call('tenant:seed-products', [
                '--store' => 'demo', 
                '--count' => 50
            ]);
            $this->info(Artisan::output());
        }
        
        // Seed users for the demo store if not skipped
        if (!$this->option('no-users')) {
            $this->info('Seeding users for demo store...');
            Artisan::call('tenant:seed-products', [
                '--store' => 'demo', 
                '--only-users' => true,
                '--users' => 10
            ]);
            $this->info(Artisan::output());
        }
        
        $this->info('Demo store setup complete!');
        
        // Show access details
        $store = Store::where('slug', 'demo')->first();
        if ($store) {
            $this->info("\n--- Demo Store Access Details ---");
            $this->info("URL: http://demo.inventory.test");
            $this->info("Admin Login: owner@demo.example.com / password");
            $this->info("Manager Login: manager@demo.example.com / password");
            $this->info("Store Status: " . ($store->status === 'active' ? 'Active' : 'Inactive'));
            $this->info("Database: " . ($store->database_created ? 'Created' : 'Not Created'));
        }
        
        return 0;
    }
}