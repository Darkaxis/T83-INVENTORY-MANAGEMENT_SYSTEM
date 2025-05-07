<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeploymentRing extends Model
{
    protected $fillable = [
        'name',
        'description',
        'order',
        'version',
        'auto_update'
    ];
    
    /**
     * Stores in this deployment ring
     */
    public function stores()
    {
        return $this->hasMany(Store::class);
    }
}