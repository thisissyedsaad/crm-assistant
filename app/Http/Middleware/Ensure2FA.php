<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Ensure2FA
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Agar user logged in nahi hai to login page pe bhej do
        if (!$user) {
            return redirect()->route('login');
        }

        // Agar user ka 2FA enabled hai
        if ($user->google2fa_enabled) {
            // Agar 2FA verify nahi hai aur current route 2fa related nahi hai
            if (!session('2fa_verified') && !$request->routeIs('2fa.*') && !$request->routeIs('logout')) {
                return redirect()->route('2fa.verify');
            }
        }

        return $next($request);
    }
}