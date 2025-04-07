<?php
// filepath: d:\WST\inventory-management-system\app\Models\Store.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\TenantDatabaseManager;
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
    ];
    
    // Add this to make these attributes accessible
    protected $appends = ['users_count', 'products_count'];
    
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // Create a database for the store when it's created
        static::created(function ($store) {
            $databaseManager = app(TenantDatabaseManager::class);
            $result = $databaseManager->createTenantDatabase($store);
            
            if (!$result) {
                Log::error("Failed to create database for store", ['store_id' => $store->id]);
            }
        });
        
        // Delete the database when the store is deleted
        static::deleted(function ($store) {
            $databaseManager = app(TenantDatabaseManager::class);
            $databaseManager->dropTenantDatabase($store);
        });
    }
    
    /**
     * Get the users for the store.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    /**
     * Get the products for the store.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    /**
     * Get the categories for the store.
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }
    
    /**
     * Get users count from tenant database
     */
    public function getUsersCountAttribute()
    {
        $databaseManager = app(TenantDatabaseManager::class);
        try {
            $databaseManager->switchToTenant($this);
            $count = DB::table('users')->count();
            $databaseManager->switchToMain();
            return $count;
        } catch (\Exception $e) {
            Log::error("Error counting users for store", [
                'store_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
    
    /**
     * Get products count from tenant database
     */
    public function getProductsCountAttribute()
    {
        $databaseManager = app(TenantDatabaseManager::class);
        try {
            $databaseManager->switchToTenant($this);
            $count = DB::table('products')->count();
            $databaseManager->switchToMain();
            return $count;
        } catch (\Exception $e) {
            Log::error("Error counting products for store", [
                'store_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
    
    /**
     * Check if database exists and is connected
     */
    public function getDatabaseConnectedAttribute()
    {
        $databaseManager = app(TenantDatabaseManager::class);
        try {
            $databaseManager->switchToTenant($this);
            $tableCount = count(DB::select('SHOW TABLES'));
            $databaseManager->switchToMain();
            return true;
        } catch (\Exception $e) {
            Log::error("Error checking database connection for store", [
                'store_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}