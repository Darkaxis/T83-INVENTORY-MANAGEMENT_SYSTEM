<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\TenantDatabaseManager;
class PricingTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'monthly_price',
        'annual_price',
        'product_limit',
        'user_limit',
        'is_active',
        'features_json',
        'sort_order',
    ];
    
    protected $casts = [
        'monthly_price' => 'float',
        'annual_price' => 'float',
        'product_limit' => 'integer',
        'user_limit' => 'integer',
        'is_active' => 'boolean',
        'features_json' => 'array',
    ];
    
    /**
     * Get the stores that belong to this pricing tier.
     */
    public function stores()
    {
        return $this->hasMany(Store::class);
    }
    
    /**
     * Check if a store is within the product limit.
     */
    public function isWithinProductLimit(Store $store): bool
    {
        if ($this->product_limit === null || $this->product_limit === -1) {
            return true; // Unlimited
        }
        
        // Get the tenant database manager from the service container
        $databaseManager = app(TenantDatabaseManager::class);
        
        // Count products from the tenant database
        $productCount = $databaseManager->countTenantRecords($store, 'products');
        
        return $productCount < $this->product_limit;
    }
    
    /**
     * Check if a store is within the user limit.
     */
    public function isWithinUserLimit(Store $store): bool
    {
        if ($this->user_limit === null || $this->user_limit === -1) {
            return true; // Unlimited
        }
        
        // Get the tenant database manager from the service container
        $databaseManager = app(TenantDatabaseManager::class);
        
        // Count users from the tenant database
        $userCount = $databaseManager->countTenantRecords($store, 'users');
        
        return $userCount < $this->user_limit;
    }
}