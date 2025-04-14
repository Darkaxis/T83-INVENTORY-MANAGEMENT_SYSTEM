<?php
// filepath: d:\WST\inventory-management-system\app\Models\StoreUser.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'user_id',
        'role',
        'access_level',
        'store_password', // Encrypted password specific to this store
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'store_password',
    ];
    
    /**
     * Get the user that owns the role.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the store that the user has access to.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}