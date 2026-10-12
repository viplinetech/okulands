<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Middleware;

use App\Support\AuthGuards;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** A suspended user is signed out on their very next request, even mid-session. */
class EnsureAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isActive()) {
            $door = $user->isAdmin() ? 'admin.login' : 'login';
            AuthGuards::logout($request, $user->isAdmin() ? 'admin' : 'web');

            return redirect()->route($door)->withErrors(['email' => 'Your account has been suspended. Please contact support.']);
        }

        return $next($request);
    }
}
