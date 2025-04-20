<?php
// filepath: d:\WST\inventory-management-system\app\Mail\StoreApproved.php

namespace App\Mail;

use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class StoreApproved extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The store instance.
     *
     * @var Store
     */
    public $store;

    /**
     * Login details for the store owner.
     *
     * @var array|null
     */
    public $loginDetails;

    /**
     * Create a new message instance.
     *
     * @param  Store  $store
     * @param  array|null  $loginDetails
     * @return void
     */
    public function __construct(Store $store, array $loginDetails = null)
    {
        $this->store = $store;
        $this->loginDetails = $loginDetails;
        
        // Log email construction for debugging
        Log::info("Creating StoreApproved email", [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'recipient' => $store->email
        ]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Generate store URL
        $storeUrl = url('/stores/' . $this->store->slug);
        
        // Log email build process
        Log::info("Building StoreApproved email", [
            'store_url' => $storeUrl,
            'has_custom_url' => isset($this->store->url) && !empty($this->store->url)
        ]);
        
        // Set priority to high to avoid spam filters
        return $this->subject('Your Store Has Been Approved!')
            ->priority(1)
            ->markdown('emails.stores.approved')
            ->with([
                'storeName' => $this->store->name,
                'storeUrl' => isset($this->store->url) && $this->store->url ? $this->store->url : $storeUrl,
                'hasCustomUrl' => isset($this->store->url) && $this->store->url,
                'loginDetails' => $this->loginDetails,
                'appName' => config('app.name')
            ]);
    }
}