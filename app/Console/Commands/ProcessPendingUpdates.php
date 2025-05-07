<?php

namespace App\Console\Commands;

use App\Models\SystemUpdate;
use App\Services\UpdateDeploymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessPendingUpdates extends Command
{
    protected $signature = 'updates:process';
    protected $description = 'Process system updates that are in "checking" status';

    public function handle(UpdateDeploymentService $deploymentService)
    {
        $updates = SystemUpdate::where('status', 'checking')->get();
        
        if ($updates->isEmpty()) {
            $this->info('No pending updates to process.');
            return self::SUCCESS;
        }
        
        $this->info('Processing ' . $updates->count() . ' pending updates...');
        
        foreach ($updates as $update) {
            $this->info('Processing update: ' . $update->version);
            
            try {
                // Download source code from GitHub
                $this->info('Downloading source code...');
                $zipPath = $deploymentService->downloadSourceCode($update);
                
                if (!$zipPath) {
                    throw new \Exception("Failed to download source code for version {$update->version}");
                }
                
                $this->info('✓ Source code downloaded to: ' . $zipPath);
                
                // Mark update as ready to deploy
                $update->status = 'completed';
                $update->downloaded_at = now();
                $update->save();
                
                $this->info('✓ Marked update ' . $update->version . ' as completed.');
                Log::info('System update processed successfully', ['version' => $update->version]);
            } catch (\Exception $e) {
                $update->status = 'failed';
                $update->save();
                
                $this->error('✗ Failed to process update: ' . $e->getMessage());
                Log::error('Failed to process system update', [
                    'version' => $update->version,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return self::SUCCESS;
    }
}