<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next, ...$roles)
    // {
    //     if (!auth()->check()) {
    //         abort(403, 'Unauthorized');
    //     }

    //     if (!in_array(auth()->user()->role, $roles)) {
    //         abort(403, 'Access denied');
    //     }

    //     return $next($request);
    // }

    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized');
        }

        $userRole = auth()->user()->role;
        
        // Saare roles ko ek array mein combine karo
        $allowedRoles = [];
        foreach ($roles as $roleString) {
            $allowedRoles = array_merge($allowedRoles, explode('|', $roleString));
        }
        
        if (!in_array($userRole, $allowedRoles)) {
            abort(403, 'Access denied');
        }

        return $next($request);
    }    
}
