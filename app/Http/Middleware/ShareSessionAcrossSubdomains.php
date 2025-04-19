<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class ShareSessionAcrossSubdomains
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Only apply in local environment
        if (app()->environment('local') && str_contains($request->getHost(), 'localhost')) {
            // Set a custom cookie to track the session ID across subdomains
            $sessionId = session()->getId();
            
            // Set a cookie that will be shared
            $response->cookie('tenant_session_id', $sessionId, 120, '/', '.localhost', false, false);
            
            // Check if we should restore a session from the cookie
            if (!$request->hasSession() && $request->cookie('tenant_session_id')) {
                session()->setId($request->cookie('tenant_session_id'));
                session()->start();
            }
        }
        
        return $response;
    }
}