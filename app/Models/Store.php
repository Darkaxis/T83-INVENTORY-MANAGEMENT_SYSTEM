<?php
// filepath: d:\WST\inventory-management-system\app\Models\Store.php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->storeUsers()->wherePivot('role', 'owner')->first();
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

}