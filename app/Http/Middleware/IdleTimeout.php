<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Signs the user out after N idle minutes (admin area uses a short window). */
class IdleTimeout
{
    public function handle(Request $request, Closure $next, int $minutes = 30): Response
    {
        $last = $request->session()->get('last_activity');

        if ($request->user() && $last && now()->timestamp - $last > $minutes * 60) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route($request->is('adminbackend*') ? 'admin.login' : 'login')->withErrors(['email' => 'You were signed out after a period of inactivity.']);
        }

        $request->session()->put('last_activity', now()->timestamp);

        return $next($request);
    }
}
