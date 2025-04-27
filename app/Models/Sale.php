<?php
// filepath: d:\WST\inventory-management-system\app\Models\Tenant\Sale.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class Sale extends Model
{
    use HasFactory;
    
    protected $connection = 'tenant';
    
    protected $fillable = [
        'invoice_number',
        'user_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'notes'
    ];
    
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Generate a unique invoice number
     */
    public static function generateInvoiceNumber()
    {
        $prefix = 'INV-';
        $date = date('Ymd');
        
        $latestInvoice = self::where('invoice_number', 'like', $prefix . $date . '%')
            ->orderBy('id', 'desc')
            ->first();
            
        if ($latestInvoice) {
            $number = intval(substr($latestInvoice->invoice_number, -4)) + 1;
            return $prefix . $date . str_pad($number, 4, '0', STR_PAD_LEFT);
        }
        
        return $prefix . $date . '0001';
    }
}