<?php

namespace App\Mail;

use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeStaffMail extends Mailable
{
    use Queueable, SerializesModels;

    public $store;
    public $password;
    public $loginUrl;

    /**
     * Create a new message instance.
     *
     * @param Store $store
     * @param string $password
     * @return void
     */
    public function __construct(Store $store, $password)
    {
        $this->store = $store;
        $this->password = $password;
        $this->loginUrl = "http://{$store->slug}.inventory.test";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Welcome to {$this->store->name}")
                    ->markdown('emails.stores.welcome-staff')
                    ->with([
                        'store' => $this->store,
                        'password' => $this->password,
                        'loginUrl' => $this->loginUrl,
                    ]);
    }
}