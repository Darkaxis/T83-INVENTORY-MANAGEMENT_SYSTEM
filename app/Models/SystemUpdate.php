<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'version_from',
        'version_to',
        'requested_by',
        'status',
        'notes',
        'error_message',
        'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];
    
    /**
     * Get the user who requested the update.
     */
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
