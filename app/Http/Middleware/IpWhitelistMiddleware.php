<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\IpWhitelist;
use Symfony\Component\HttpFoundation\Response;

class IpWhitelistMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip IP check for super admin users (already logged in)
        if (auth()->check() && auth()->user()->role === 'super-admin') {
            return $next($request);
        }

        // Get client IP address
        $clientIp = $this->getClientIp($request);
        
        // Check if IP whitelist is enabled
        if (!env('IP_WHITELIST_ENABLED', false)) {
            return $next($request);
        }

        // Check default allowed IPs from environment
        if ($this->isDefaultAllowedIp($clientIp)) {
            return $next($request);
        }

        // Check if IP is whitelisted in database and active
        if (!IpWhitelist::isIpAllowed($clientIp)) {
            return $this->accessDeniedResponse($request, $clientIp);
        }

        return $next($request);
    }

    /**
     * Check if IP is in default allowed IPs from environment
     */
    private function isDefaultAllowedIp(string $clientIp): bool
    {
        $defaultIps = env('DEFAULT_ALLOWED_IPS', '');
        
        if (empty($defaultIps)) {
            return false;
        }

        $allowedIps = array_map('trim', explode(',', $defaultIps));
        return in_array($clientIp, $allowedIps);
    }

    /**
     * Get the real client IP address
     */
    private function getClientIp(Request $request): string
    {
        // Check for various headers in order of preference
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];

        foreach ($ipHeaders as $header) {
            $ip = $request->server($header);
            if ($ip && $this->isValidIp($ip)) {
                // Handle comma-separated IPs
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if ($this->isValidIp($ip)) {
                    return $ip;
                }
            }
        }

        return $request->ip();
    }

    /**
     * Validate if IP is in correct format
     */
    private function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    /**
     * Return access denied response
     */
    private function accessDeniedResponse(Request $request, string $ip): Response
    {
        // Log the access attempt
        \Log::warning('IP Access Denied', [
            'ip' => $ip,
            'url' => $request->fullUrl(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
            'default_ips' => env('DEFAULT_ALLOWED_IPS', 'none'),
            'whitelist_enabled' => env('IP_WHITELIST_ENABLED', false)
        ]);

        // Logout the user if authenticated
        if (auth()->check()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Return JSON response for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Access Denied',
                'message' => 'Your IP address is not authorized to access this resource.',
                'ip' => $ip,
                'contact' => 'Please contact administrator to whitelist your IP address.'
            ], 403);
        }

        // Return enhanced plain text for web requests with more info
        $message = "🚫 Access Denied\n\n";
        $message .= "Your IP Address: {$ip}\n";
        $message .= "Status: Not Authorized\n\n";
        $message .= "This application is restricted to authorized IP addresses only.\n";
        $message .= "Please contact the system administrator to request access.\n\n";
        $message .= "Timestamp: " . now()->format('Y-m-d H:i:s T');

        return response($message, 403)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}