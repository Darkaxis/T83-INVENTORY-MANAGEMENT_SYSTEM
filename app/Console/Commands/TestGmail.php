<?php
// filepath: d:\WST\inventory-management-system\app\Console\Commands\TestGmail.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use App\Models\Store;
use App\Mail\StoreApproved;

class TestGmail extends Command
{
    protected $signature = 'mail:test-gmail 
                            {email : Email address to send test to} 
                            {--raw : Send raw email instead of mailable}';
    protected $description = 'Test Gmail configuration';

    public function handle()
    {
        $email = $this->argument('email');
        $useRaw = $this->option('raw');
        
        $this->info("Testing Gmail configuration...");
        $this->info("From: " . config('mail.from.address'));
        $this->info("To: " . $email);
        
        try {
            config(['mail.mailers.smtp.debug' => true]);
            if ($useRaw) {
                // Send a raw email for testing basic SMTP functionality
                $this->info("Sending raw email...");
                
                Mail::raw('This is a test email sent at ' . now(), function (Message $message) use ($email) {
                    $message->to($email)
                        ->subject('Test Email from Laravel');
                });
                
                $this->info("Raw email sent successfully!");
            } else {
                // Send using the StoreApproved mailable
                $this->info("Sending email using StoreApproved mailable...");
                
                // Get or create a test store
                $store = Store::first();
                
                if (!$store) {
                    $this->error("No store found in database. Creating a test store object...");
                    $store = new Store();
                    $store->id = 999;
                    $store->name = "Test Store";
                    $store->slug = "test-store";
                    $store->email = $email;
                }
                
                $loginDetails = [
                    'email' => $email,
                    'password' => 'testpassword',
                ];
                
                // Send email without queueing for immediate feedback
                Mail::to($email)->send(new StoreApproved($store, $loginDetails));
                

                $this->info("StoreApproved email sent successfully!");
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
            $this->error("Exception type: " . get_class($e));
            $this->error("File: " . $e->getFile() . ":" . $e->getLine());
            
            // Display mail configuration for debugging
            $this->line("\nMail Configuration:");
            $this->line("- Driver: " . config('mail.default'));
            $this->line("- Host: " . config('mail.mailers.smtp.host'));
            $this->line("- Port: " . config('mail.mailers.smtp.port'));
            $this->line("- Username: " . config('mail.mailers.smtp.username'));
            $this->line("- From Address: " . config('mail.from.address'));
            
            Log::error("Email test failed: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }
}