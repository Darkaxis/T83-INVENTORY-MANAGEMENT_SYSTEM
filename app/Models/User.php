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
    
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}