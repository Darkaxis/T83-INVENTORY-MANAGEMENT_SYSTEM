<?php
// filepath: d:\WST\inventory-management-system\app\Models\Store.php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'status',
        'approved',
        'database_created',
        'pricing_tier_id',  // This must be included
        'billing_cycle',
        'subscription_start_date',
        'subscription_end_date',
        'auto_renew',
        'accent_color',
        'logo',
        'logo_binary',
        'logo_mime_type',
    ];

    protected $casts = [
        'approved' => 'boolean',
        'database_created' => 'boolean',
    ];

    /**
     * Check if the store is active and approved.
     *
     * @return bool
     */
    public function isAccessible(): bool
    {
        return $this->approved && $this->status === 'active';
    }

    /**
     * Check if the store has a valid database connection.
     *
     * @return bool
     */
    public function getDatabaseConnectedAttribute(): bool
    {
        return $this->database_created && $this->approved;
    }

    /**
     * Get the store URL with subdomain.
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        $host = parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST);
        return 'http://' . $this->slug . '.' . ($host ?: 'localhost');
    }
    
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function owner()
    {
        return $this->storeUsers()->wherePivot('role', 'manager')->first();
    }
    public function storeUsers()
    {
        return $this->belongsToMany(User::class, 'store_users')
            ->withPivot('role', 'access_level')
            ->withTimestamps();
    }
    public function pricingTier()
    {
        return $this->belongsTo(PricingTier::class);
    }

    public function deploymentRing()
    {
        return $this->belongsTo(DeploymentRing::class);
    }

    /**
     * Helper method to get store's current version
     */
    public function getCurrentVersion()
    {
        return $this->deploymentRing?->version ?? config('app.version');
    }

    /**
     * Check if store is within product limit
     */
    public function canAddProducts(): bool
    {
        if (!$this->pricingTier) {
            return true; // No tier restrictions
        }
        
        return $this->pricingTier->isWithinProductLimit($this);
    }

    /**
     * Check if store is within user limit
     */
    public function canAddUsers(): bool
    {
        if (!$this->pricingTier) {
            return true; // No tier restrictions
        }
        
        return $this->pricingTier->isWithinUserLimit($this);
    }

    /**
     * Get remaining products allowed
     */
    public function remainingProducts(): int
    {
        if (!$this->pricingTier || $this->pricingTier->product_limit === null || $this->pricingTier->product_limit === -1) {
            return -1; // Unlimited
        }
        
        $productCount = Product::where('store_id', $this->id)->count();
        return max(0, $this->pricingTier->product_limit - $productCount);
    }

    /**
     * Get the count of users from the tenant database
     * 
     * @return int
     */
    public function getTenantUserCount()
    {
        // If database isn't connected, return 0
        if (!$this->approved || !$this->database_connected) {
            return 0;
        }
        
        try {
            // Get the database manager
            $dbManager = app(\App\Services\TenantDatabaseManager::class);
            
            // Switch to tenant database
            $dbManager->switchToTenant($this);
            
            // Count users
            $count = DB::connection('tenant')->table('users')->count();
            
            // Switch back to main
            $dbManager->switchToMain();
            
            return $count;
        } catch (\Exception $e) {
            // Log error
            Log::error("Error getting users count for store {$this->id}: {$e->getMessage()}");
            
            // Make sure we switch back
            try {
                $dbManager->switchToMain();
            } catch (\Exception $e2) {
                // Already logged
            }
            
            return 0;
        }
    }

    /**
     * Get the count of products from the tenant database
     * 
     * @return int
     */
    public function getTenantProductCount()
    {
        // If database isn't connected, return 0
        if (!$this->approved || !$this->database_connected) {
            return 0;
        }
        
        try {
            // Get the database manager
            $dbManager = app(\App\Services\TenantDatabaseManager::class);
            
            // Switch to tenant database
            $dbManager->switchToTenant($this);
            
            // Count products
            $count = DB::connection('tenant')->table('products')->count();
            
            // Switch back to main
            $dbManager->switchToMain();
            
            return $count;
        } catch (\Exception $e) {
            // Log error
            Log::error("Error getting product count for store {$this->id}: {$e->getMessage()}");
            
            // Make sure we switch back
            try {
                $dbManager->switchToMain();
            } catch (\Exception $e2) {
                // Already logged
            }
            
            return 0;
        }
    }

    /**
     * Get logo URL.
     *
     * @return string
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        
        return asset('assets/img/logo-ct-dark.png');
    }

    /**
     * Get accent color CSS values derived from the main accent color
     */
    public function getAccentColorCss()
    {
        // Use store accent color or fallback to default blue
        $primaryColor = $this->accent_color ?? '#4e73df';
        
        // Convert hex to RGB for calculations
        list($r, $g, $b) = sscanf($primaryColor, "#%02x%02x%02x");
        
        // Create slightly darker shade for secondary (darken by 8%)
        $rDark = max(0, $r - ($r * 0.08));
        $gDark = max(0, $g - ($g * 0.08));
        $bDark = max(0, $b - ($b * 0.08));
        $secondaryColor = sprintf("#%02x%02x%02x", $rDark, $gDark, $bDark);
        
        // Create even darker shade for tertiary (darken by 12%)
        $rDarker = max(0, $r - ($r * 0.12));
        $gDarker = max(0, $g - ($g * 0.12));
        $bDarker = max(0, $b - ($b * 0.12));
        $tertiaryColor = sprintf("#%02x%02x%02x", $rDarker, $gDarker, $bDarker);
        
        // Create highlight color (25% opacity of primary)
        $highlightColor = "rgba($r, $g, $b, 0.25)";
        
        return [
            'primary' => $primaryColor,
            'secondary' => $secondaryColor,
            'tertiary' => $tertiaryColor,
            'highlight' => $highlightColor,
            'rgb' => "$r,$g,$b"
        ];
    }

    /**
     * Get color variations based on the store's accent color.
     *
     * @return array
     */
    public function getAccentColorCssAttribute()
    {
        $hex = $this->accent_color;
        
        // Check if the accent_color is already a hex value
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $hex)) {
            // Extract RGB values
            $r = hexdec(substr($hex, 1, 2));
            $g = hexdec(substr($hex, 3, 2));
            $b = hexdec(substr($hex, 5, 2));
            
            // Create darker variations
            $darken10 = $this->shadeHexColor($hex, -10);
            $darken15 = $this->shadeHexColor($hex, -15);
            
            return [
                'primary' => $hex,
                'secondary' => $darken10,
                'tertiary' => $darken15,
                'highlight' => "rgba($r, $g, $b, 0.25)",
            ];
        }
        
        // Fall back to pre-defined colors for backward compatibility
        $predefinedColors = [
            // Your existing color definitions
        ];
        
        return $predefinedColors[$hex] ?? $predefinedColors['blue'];
    }

    /**
     * Helper function to darken/lighten a hex color.
     *
     * @param string $color Hex color code
     * @param int $percent Percentage to darken/lighten
     * @return string
     */
    private function shadeHexColor($color, $percent) 
    {
        $R = hexdec(substr($color, 1, 2));
        $G = hexdec(substr($color, 3, 2));
        $B = hexdec(substr($color, 5, 2));

        $R = (int)($R * (100 + $percent) / 100);
        $G = (int)($G * (100 + $percent) / 100);
        $B = (int)($B * (100 + $percent) / 100);

        $R = min(255, max(0, $R));
        $G = min(255, max(0, $G));
        $B = min(255, max(0, $B));

        $RR = dechex($R);
        $GG = dechex($G);
        $BB = dechex($B);

        return '#' . 
            (strlen($RR) < 2 ? '0' : '') . $RR . 
            (strlen($GG) < 2 ? '0' : '') . $GG . 
            (strlen($BB) < 2 ? '0' : '') . $BB;
    }
}