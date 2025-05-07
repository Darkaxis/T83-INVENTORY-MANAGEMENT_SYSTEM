<?php

namespace App\Jobs;

use App\Models\SystemUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSystemUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $updateId;
    public $timeout = 3600; // 1 hour timeout
    public $tries = 1;

    public function __construct($updateId)
    {
        $this->updateId = $updateId;
    }

    public function handle()
    {
        // Get the update record
        $update = SystemUpdate::findOrFail($this->updateId);
        
        // Get the controller instance
        $controller = app(\App\Http\Controllers\Admin\UpdateController::class);
        
        try {
            // Download the update
            $zipPath = $controller->downloadUpdate($update->version_to);
            
            // Backup current application
            $backupPath = $controller->backupCurrentSystem($update->version_from);
            
            // Extract and apply update
            $controller->applyUpdate($zipPath, $update->version_to);
            
            // Run post-update tasks
            $controller->runPostUpdateTasks($update->version_to);
            
            // Update record in database
            $update->update([
                'status' => 'completed',
                'completed_at' => now(),
                'notes' => "Update successfully completed. System backup saved at: {$backupPath}"
            ]);
            
            Log::info("System update to {$update->version_to} completed successfully");
        } catch (\Exception $e) {
            Log::error("Update failed: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            $update->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'notes' => "Update failed. Error: " . $e->getMessage()
            ]);
        }
    }
}