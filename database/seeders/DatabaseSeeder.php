<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        AdminUser::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
        
        // Run seeders
        $this->call([
            PricingTierSeeder::class,
            DemoStoreWithProductsSeeder::class,  // Creates a demo store with products
            ProductSeeder::class,                // Seeds products for all stores respecting tier limits
            SeasonalProductSeeder::class,        // Adds seasonal products
        ]);

        $this->command->info('Admin user created! You can log in with admin@example.com and password: admin123');
    }
}

