<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'role',
        'store_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    public function isManager()
    {
        return $this->role === 'manager';
    }
    
    public function isStaff()
    {
        return $this->role === 'staff';
    }
    
    public function belongsToStore($storeId)
    {
        return $this->store_id === $storeId;
    }

    public function assignRole($role)
    {
        $this->role = $role;
        $this->save();
    }
    
    
    public function stores()
    {
        return $this->belongsToMany(Store::class, 'store_users')
            ->withPivot('role', 'access_level')
            ->withTimestamps();
    }
    
    /**
     * Get store-specific details including store-specific password
     */
    public function storeAccess($storeId)
    {
        return $this->hasMany(StoreUser::class)
            ->where('store_id', $storeId)
            ->first();
    }
}