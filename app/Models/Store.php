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

}