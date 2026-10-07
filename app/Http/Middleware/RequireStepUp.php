<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Step-up check for sensitive admin actions (money, realtor accounts, exports).
 *
 * Signing in with a 2FA code is not enough: before one of these actions the admin must confirm with a
 * fresh code. That confirmation then stays valid for WINDOW seconds. The sign-in code deliberately does
 * not count, so the admin always makes a conscious confirmation before touching anything sensitive.
 */
class RequireStepUp
{
    public const SESSION_KEY = 'step_up_at';

    /** How long a confirmation lasts (seconds). */
    public const WINDOW = 600;

    public static function isFresh(Request $request): bool
    {
        return time() - (int) $request->session()->get(self::SESSION_KEY, 0) < self::WINDOW;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('security.step_up')) {
            return $next($request); // switched off: the sign-in code is enough
        }

        $user = $request->user();

        // Admins without 2FA never get this far (EnforceTwoFactor sends them to set it up first).
        if (! $user || ! $user->hasTwoFactorEnabled() || self::isFresh($request)) {
            return $next($request);
        }

        // Page views come straight back after confirming; for form submissions the admin returns to the form and repeats it.
        $request->session()->put('step_up_intended', $request->isMethod('GET') ? $request->fullUrl() : url()->previous(route('admin.dashboard')));

        return redirect()->route('admin.confirm')
            ->with('warning', 'This is a sensitive action. Please confirm it is you with a code from your authenticator app.');
    }
}
