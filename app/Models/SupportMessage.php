<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SupportMessage extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'tenant_user_id', // Add this line
        'message',
        'is_admin'
    ];
    
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, 'message_id');
    }
    
    /**
     * Get the tenant user from the tenant database
     */
    public function getTenantUserAttribute()
    {
        $ticket = $this->ticket;
        if (!$ticket || !$ticket->store) return null;
        
        return DB::connection('tenant')
            ->table('users')
            ->where('id', $this->tenant_user_id)
            ->first();
    }
}