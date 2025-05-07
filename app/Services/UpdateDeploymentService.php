<?php

namespace App\Services;

use App\Models\Store;
use App\Models\SystemUpdate;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class UpdateDeploymentService
{
    /**
     * Download source code from GitHub release
     */
    public function downloadSourceCode(SystemUpdate $update)
    {
        try {
            // Get repo and release info
            $repository = config('system.github_repository');
            $version = $update->version;
            
            // Determine GitHub API URL for release assets
            $url = "https://api.github.com/repos/{$repository}/releases/tags/{$version}";
            $headers = ['Accept' => 'application/vnd.github.v3+json'];
            
            if ($token = config('system.github_token')) {
                $headers['Authorization'] = "token {$token}";
            }
            
            // Get the release
            $response = Http::withHeaders($headers)->get($url);
            
            if (!$response->successful()) {
                Log::error('Failed to get GitHub release', [
                    'version' => $version,
                    'status' => $response->status()
                ]);
                return false;
            }
            
            $release = $response->json();
            
            // Find sourcecode.zip asset
            $sourcecodeAsset = null;
            foreach ($release['assets'] as $asset) {
                if ($asset['name'] === 'sourcecode.zip') {
                    $sourcecodeAsset = $asset;
                    break;
                }
            }
            
            if (!$sourcecodeAsset) {
                Log::error('No sourcecode.zip found in release assets', ['version' => $version]);
                return false;
            }
            
            // Create storage directory if it doesn't exist
            $downloadPath = storage_path("app/updates/{$version}");
            if (!File::isDirectory($downloadPath)) {
                File::makeDirectory($downloadPath, 0755, true);
            }
            
            // Download the asset
            $zipPath = "{$downloadPath}/sourcecode.zip";
            $assetUrl = $sourcecodeAsset['browser_download_url'];
            
            // Use file_get_contents for simplicity, but could use streamed download for large files
            $content = Http::withHeaders($headers)->get($assetUrl)->body();
            File::put($zipPath, $content);
            
            // Update the database
            $update->downloaded_at = now();
            $update->save();
            
            return $zipPath;
        } catch (\Exception $e) {
            Log::error('Failed to download source code', [
                'version' => $update->version,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Deploy update to a specific tenant store
     */
    public function deployToStore(Store $store, SystemUpdate $update)
    {
        try {
            // Get zip file path
            $zipPath = storage_path("app/updates/{$update->version}/sourcecode.zip");
            
            // Check if file exists, download if necessary
            if (!File::exists($zipPath)) {
                $zipPath = $this->downloadSourceCode($update);
                if (!$zipPath) {
                    return false;
                }
            }
            
            // Extract tenant specific files
            $extractPath = storage_path("app/updates/{$update->version}/extracted");
            if (!File::isDirectory($extractPath)) {
                File::makeDirectory($extractPath, 0755, true);
                
                $zip = new ZipArchive;
                if ($zip->open($zipPath) === true) {
                    $zip->extractTo($extractPath);
                    $zip->close();
                } else {
                    throw new \Exception("Failed to open zip file: {$zipPath}");
                }
            }
            
            // Deploy to tenant
            $this->deployTenantFiles($store, $extractPath);
            
            // Update store version
            $store->version = $update->version;
            $store->save();
            
            Log::info("Update deployed successfully", [
                'store' => $store->id,
                'version' => $update->version
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to deploy update to store", [
                'store' => $store->id,
                'version' => $update->version,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Deploy files to tenant directory
     */
    private function deployTenantFiles(Store $store, $sourcePath)
    {
        // Determine tenant directory (adjust based on your tenant structure)
        $tenantPath = storage_path("app/tenants/{$store->id}");
        
        // Copy tenant-specific files
        // This will depend on your specific application structure
        // Example file structure:
        //   - /views (tenant-specific views)
        //   - /assets (tenant-specific assets)
        //   - tenant-config.php (tenant configuration)
        
        // Create tenant directory if it doesn't exist
        if (!File::isDirectory($tenantPath)) {
            File::makeDirectory($tenantPath, 0755, true);
        }
        
        // Copy files
        // Assuming tenant files are in a /tenant directory in the zip
        if (File::isDirectory("{$sourcePath}/tenant")) {
            File::copyDirectory("{$sourcePath}/tenant", $tenantPath);
        }
        
        // Run any tenant-specific database migrations
        // This assumes you're using a tenant database connection
        if (File::exists("{$sourcePath}/migrations")) {
            $this->runTenantMigrations($store, "{$sourcePath}/migrations");
        }
        
        // Clear tenant cache
        $this->clearTenantCache($store);
    }
    
    /**
     * Run tenant-specific migrations
     */
    private function runTenantMigrations(Store $store, $migrationsPath)
    {
        // Switch to tenant database connection
        // This is pseudocode - adjust based on your tenancy system
        config(['database.connections.tenant.database' => "tenant_{$store->id}"]);
        
        // Run the migrations
        // You might need to create a custom migration runner for this
        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--path' => $migrationsPath,
            '--database' => 'tenant',
            '--force' => true,
        ]);
    }
    
    /**
     * Clear tenant cache
     */
    private function clearTenantCache(Store $store)
    {
        // Clear any tenant-specific caches
        // This is pseudocode - adjust based on your caching system
    }
}