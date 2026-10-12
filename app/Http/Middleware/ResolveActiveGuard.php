<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Middleware;

use App\Support\AuthGuards;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admins and realtors each have their own guard now (so both can be signed in at once in one
 * browser). Routes shared by both areas (logout, the two-factor challenge, the idle-timeout and
 * suspended-account checks, …) still just call auth()/$request->user() without naming a guard,
 * which resolves to whichever guard Auth::shouldUse() points at. This runs first and points it at
 * 'admin' only when that guard is actually the one signed in on this request; everyone else
 * (including a realtor who happens to also be signed in as admin elsewhere) stays on the normal
 * 'web' default.
 */
class ResolveActiveGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        Auth::shouldUse(AuthGuards::active($request));

        return $next($request);
    }
}
