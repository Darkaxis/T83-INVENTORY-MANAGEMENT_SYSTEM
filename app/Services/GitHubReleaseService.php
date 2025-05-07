<?php
namespace App\Services;

use App\Models\SystemUpdate;
use Github\Client;
use Illuminate\Support\Facades\Log;

class GitHubReleaseService
{
    protected $client;
    protected $repository;
    protected $latestRelease;
    protected $config;

    public function __construct()
    {
        // Don't access config in constructor - defer it to methods
        $this->latestRelease = null;
    }
    
    protected function initClient()
    {
        if ($this->client) {
            return;
        }
        
        $this->client = new Client();
        
        // Add authentication if token exists
        $token = config('system.github_token');
        if ($token) {
            $this->client->authenticate($token, null, Client::AUTH_ACCESS_TOKEN);
        }
        
        // Get repository from config
        $this->repository = config('system.github_repository', 'Darkaxis/T83-INVENTORY-MANAGEMENT_SYSTEM');
    }

    public function fetchLatestRelease()
    {
        $this->initClient();
        
        try {
            // Use HTTP client directly instead of the API abstraction
            $url = "https://api.github.com/repos/{$this->repository}/releases/latest";
            $headers = ['Accept' => 'application/vnd.github.v3+json'];
            
            if ($token = config('system.github_token')) {
                $headers['Authorization'] = "token {$token}";
            }
            
            $response = \Illuminate\Support\Facades\Http::withHeaders($headers)->get($url);
            
            if ($response->successful()) {
                $this->latestRelease = $response->json();
                return $this->latestRelease;
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch GitHub releases: ' . $e->getMessage());
        }
        
        return null;
    }

    public function checkForUpdates()
    {
        $this->initClient();
        
        try {
            // Use HTTP client directly instead of the API abstraction
            $url = "https://api.github.com/repos/{$this->repository}/releases";
            $headers = ['Accept' => 'application/vnd.github.v3+json'];
            
            if ($token = config('system.github_token')) {
                $headers['Authorization'] = "token {$token}";
            }
            
            $response = \Illuminate\Support\Facades\Http::withHeaders($headers)->get($url);
            
            if (!$response->successful()) {
                Log::error('GitHub API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return 0;
            }
            
            $releases = $response->json();
            
            // Process releases and create SystemUpdate records
            $newReleases = 0;
            foreach ($releases as $release) {
                // Skip if already in database
                if (SystemUpdate::where('version', $release['tag_name'])->exists()) {
                    continue;
                }
                
                SystemUpdate::create([
                    'version' => $release['tag_name'], 
                    'status' => 'checking',
                    'release_notes' => $release['body'],
                    'checked_at' => now(),
                ]);
                
                Log::info('New system update found', [
                    'version' => $release['tag_name']
                ]);
                
                $newReleases++;
            }
            
            return $newReleases;
        } catch (\Exception $e) {
            Log::error('Error checking for updates: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;
        }
    }
    
    public function getLatestReleaseInfo()
    {
        $this->initClient();
        
        if (!$this->latestRelease) {
            $this->fetchLatestRelease();
        }
        
        return $this->latestRelease;
    }
}