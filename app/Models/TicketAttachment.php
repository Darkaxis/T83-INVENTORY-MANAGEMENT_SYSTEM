<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    protected $fillable = [
        'message_id',
        'file_name',
        'file_path',
        'file_type'
    ];

    /**
     * Get the message that owns the attachment.
     */
    public function message()
    {
        return $this->belongsTo(SupportMessage::class, 'message_id');
    }
}