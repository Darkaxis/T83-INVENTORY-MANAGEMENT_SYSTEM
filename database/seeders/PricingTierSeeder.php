<?php

namespace Database\Seeders;

use App\Models\PricingTier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PricingTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Temporarily disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing pricing tiers
        DB::table('pricing_tiers')->truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Free tier
        PricingTier::create([
            'name' => 'Free',
            'description' => 'Basic inventory management for small businesses just getting started.',
            'monthly_price' => 0.00,
            'annual_price' => 0.00,
            'product_limit' => 20,
            'user_limit' => 1,
            'is_active' => true,
            'features_json' => [
                'Basic product management',
                'Single user access',
                'Limited reports',
                'Basic inventory tracking'
            ],
            'sort_order' => 1
        ]);
        
        // Starter tier
        PricingTier::create([
            'name' => 'Starter',
            'description' => 'Perfect for small and growing businesses.',
            'monthly_price' => 29.99,
            'annual_price' => 299.99,
            'product_limit' => 100,
            'user_limit' => 3,
            'is_active' => true,
            'features_json' => [
                'All Free tier features',
                'Multiple user access',
                'Advanced reports',
                'Email notifications'
            ],
            'sort_order' => 2
        ]);
        
        // Professional tier
        PricingTier::create([
            'name' => 'Professional',
            'description' => 'Ideal for established businesses with moderate inventory needs.',
            'monthly_price' => 59.99,
            'annual_price' => 599.99,
            'product_limit' => 500,
            'user_limit' => 10,
            'is_active' => true,
            'features_json' => [
                'All Starter tier features',
                'Advanced analytics',
                'API access',
                'Custom reports',
                'Supplier management',
                'Order tracking',
                'Multiple locations',
                'Enhanced security features'
            ],
            'sort_order' => 3
        ]);
        
       
        // Unlimited tier
        PricingTier::create([
            'name' => 'Unlimited',
            'description' => 'No limits. Perfect for high-volume businesses needing maximum flexibility.',
            'monthly_price' => 299.99,
            'annual_price' => 2999.99,
            'product_limit' => -1, // Unlimited
            'user_limit' => -1, // Unlimited
            'is_active' => true,
            'features_json' => [
                'All Enterprise tier features',
                'Unlimited products',
                'Unlimited users',
            ],
            'sort_order' => 4
        ]);
        
    
    }
}