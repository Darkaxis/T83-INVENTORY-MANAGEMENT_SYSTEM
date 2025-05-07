<?php
// app/Models/SupportTicket.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SupportTicket extends Model
{
    protected $fillable = [
        'store_id', 'tenant_user_id', 'subject', 'status', 'priority', 'category', 'user_id'
    ];
    
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    
    public function messages()
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id');
    }
    
    /**
     * Get the admin user who manages this ticket
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the tenant user from the tenant database
     */
    public function getTenantUserAttribute()
    {
        $store = $this->store;
        if (!$store) return null;
        
        // Get all available fields from the tenant user table
        $tenantUser = DB::connection('tenant')
            ->table('users')
            ->where('id', $this->tenant_user_id)
            ->first();
            
        // Return the user data even if it doesn't have expected columns
        return $tenantUser;
    }
    
    /**
     * Get the tenant user identifier (whatever is available)
     */
    public function getTenantUserIdentifierAttribute()
    {
        $user = $this->tenant_user;
        if (!$user) return 'Unknown User';
        
        // Try common user identifier fields
        if (isset($user->name)) return $user->name;
        if (isset($user->username)) return $user->username;
        if (isset($user->email)) return $user->email;
        if (isset($user->user_id)) return "User #{$user->user_id}";
        
        // Last resort - return the ID
        return "User #{$this->tenant_user_id}";
    }
    
    /**
     * Get tenant user contact info (whatever is available)
     */
    public function getTenantUserContactAttribute()
    {
        $user = $this->tenant_user;
        if (!$user) return 'No contact info';
        
        // Try common contact fields
        if (isset($user->email)) return $user->email;
        if (isset($user->contact)) return $user->contact;
        if (isset($user->phone)) return $user->phone;
        
        // Last resort
        return "ID: {$this->tenant_user_id}";
    }
}