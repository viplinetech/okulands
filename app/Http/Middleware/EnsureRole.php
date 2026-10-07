<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps each area to its own people: `role:admin` for /admin, `role:affiliate` for /realtor.
 * Non-admins get a plain 404 on admin URLs so the admin area's existence is not revealed.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if ($role === 'admin') {
            abort_unless($user?->isAdmin(), 404);

            return $next($request);
        }

        if (! $user?->isAffiliate()) {
            return $user ? redirect()->route($user->homeRoute()) : redirect()->route('login');
        }

        return $next($request);
    }
}
