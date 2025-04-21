<?php
// filepath: d:\WST\inventory-management-system\app\Models\Store.php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'status',
        'approved',
        'database_created',
        'pricing_tier_id',  // This must be included
        'billing_cycle',
        'subscription_start_date',
        'subscription_end_date',
        'auto_renew',
        'accent_color',
        'logo',
        'logo_binary',
        'logo_mime_type',
    ];

    protected $casts = [
        'approved' => 'boolean',
        'database_created' => 'boolean',
    ];

    /**
     * Check if the store is active and approved.
     *
     * @return bool
     */
    public function isAccessible(): bool
    {
        return $this->approved && $this->status === 'active';
    }

    /**
     * Check if the store has a valid database connection.
     *
     * @return bool
     */
    public function getDatabaseConnectedAttribute(): bool
    {
        return $this->database_created && $this->approved;
    }

    /**
     * Get the store URL with subdomain.
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        $host = parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST);
        return 'http://' . $this->slug . '.' . ($host ?: 'localhost');
    }
    
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function owner()
    {
        return $this->storeUsers()->wherePivot('role', 'manager')->first();
    }
    public function storeUsers()
    {
        return $this->belongsToMany(User::class, 'store_users')
            ->withPivot('role', 'access_level')
            ->withTimestamps();
    }
    public function pricingTier()
    {
        return $this->belongsTo(PricingTier::class);
    }

    /**
     * Check if store is within product limit
     */
    public function canAddProducts(): bool
    {
        if (!$this->pricingTier) {
            return true; // No tier restrictions
        }
        
        return $this->pricingTier->isWithinProductLimit($this);
    }

    /**
     * Check if store is within user limit
     */
    public function canAddUsers(): bool
    {
        if (!$this->pricingTier) {
            return true; // No tier restrictions
        }
        
        return $this->pricingTier->isWithinUserLimit($this);
    }

    /**
     * Get remaining products allowed
     */
    public function remainingProducts(): int
    {
        if (!$this->pricingTier || $this->pricingTier->product_limit === null || $this->pricingTier->product_limit === -1) {
            return -1; // Unlimited
        }
        
        $productCount = Product::where('store_id', $this->id)->count();
        return max(0, $this->pricingTier->product_limit - $productCount);
    }

    /**
     * Get the count of users from the tenant database
     * 
     * @return int
     */
    public function getTenantUserCount()
    {
        // If database isn't connected, return 0
        if (!$this->approved || !$this->database_connected) {
            return 0;
        }
        
        try {
            // Get the database manager
            $dbManager = app(\App\Services\TenantDatabaseManager::class);
            
            // Switch to tenant database
            $dbManager->switchToTenant($this);
            
            // Count users
            $count = DB::connection('tenant')->table('users')->count();
            
            // Switch back to main
            $dbManager->switchToMain();
            
            return $count;
        } catch (\Exception $e) {
            // Log error
            Log::error("Error getting users count for store {$this->id}: {$e->getMessage()}");
            
            // Make sure we switch back
            try {
                $dbManager->switchToMain();
            } catch (\Exception $e2) {
                // Already logged
            }
            
            return 0;
        }
    }

    /**
     * Get the count of products from the tenant database
     * 
     * @return int
     */
    public function getTenantProductCount()
    {
        // If database isn't connected, return 0
        if (!$this->approved || !$this->database_connected) {
            return 0;
        }
        
        try {
            // Get the database manager
            $dbManager = app(\App\Services\TenantDatabaseManager::class);
            
            // Switch to tenant database
            $dbManager->switchToTenant($this);
            
            // Count products
            $count = DB::connection('tenant')->table('products')->count();
            
            // Switch back to main
            $dbManager->switchToMain();
            
            return $count;
        } catch (\Exception $e) {
            // Log error
            Log::error("Error getting product count for store {$this->id}: {$e->getMessage()}");
            
            // Make sure we switch back
            try {
                $dbManager->switchToMain();
            } catch (\Exception $e2) {
                // Already logged
            }
            
            return 0;
        }
    }

    /**
     * Get logo URL.
     *
     * @return string
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        
        return asset('assets/img/logo-ct-dark.png');
    }

    /**
     * Get CSS color variables based on accent color.
     *
     * @return array
     */
    public function getAccentColorCss()
    {
        $colors = [
            'blue' => [
                'primary' => '#4e73df',
                'secondary' => '#2e59d9',
                'tertiary' => '#2653d4',
                'highlight' => 'rgba(78, 115, 223, 0.25)',
            ],
            'indigo' => [
                'primary' => '#6610f2',
                'secondary' => '#520dc2',
                'tertiary' => '#4d0cb3',
                'highlight' => 'rgba(102, 16, 242, 0.25)',
            ],
            'purple' => [
                'primary' => '#6f42c1',
                'secondary' => '#5a359f',
                'tertiary' => '#533291',
                'highlight' => 'rgba(111, 66, 193, 0.25)',
            ],
            'pink' => [
                'primary' => '#e83e8c',
                'secondary' => '#d4317a',
                'tertiary' => '#c42e72',
                'highlight' => 'rgba(232, 62, 140, 0.25)',
            ],
            'red' => [
                'primary' => '#e74a3b',
                'secondary' => '#d13b2e',
                'tertiary' => '#c0372a',
                'highlight' => 'rgba(231, 74, 59, 0.25)',
            ],
            'orange' => [
                'primary' => '#fd7e14',
                'secondary' => '#e96e10',
                'tertiary' => '#d6630f',
                'highlight' => 'rgba(253, 126, 20, 0.25)',
            ],
            'yellow' => [
                'primary' => '#f6c23e',
                'secondary' => '#e9b32d',
                'tertiary' => '#e0ac29',
                'highlight' => 'rgba(246, 194, 62, 0.25)',
            ],
            'green' => [
                'primary' => '#1cc88a',
                'secondary' => '#18a97c',
                'tertiary' => '#169b72',
                'highlight' => 'rgba(28, 200, 138, 0.25)',
            ],
            'teal' => [
                'primary' => '#20c9a6',
                'secondary' => '#1ba393',
                'tertiary' => '#199688',
                'highlight' => 'rgba(32, 201, 166, 0.25)',
            ],
            'cyan' => [
                'primary' => '#36b9cc',
                'secondary' => '#2fa6b9',
                'tertiary' => '#2a98a9',
                'highlight' => 'rgba(54, 185, 204, 0.25)',
            ],
        ];
        
        $colorName = $this->accent_color ?? 'blue';
        
        return $colors[$colorName] ?? $colors['blue'];
    }
}