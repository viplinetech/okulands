<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Middleware;

use App\Services\TrustedDevice;
use App\Support\Audit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Two-factor gate for every signed-in request.
 *  - A user with 2FA enabled must pass the challenge once per session.
 *  - An admin without 2FA is sent to set it up before touching anything else (mandatory for admins).
 * The challenge, setup and logout routes are exempt (they are outside this middleware's group or listed here).
 */
class EnforceTwoFactor
{
    public const SESSION_KEY = 'two_factor_passed';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->hasTwoFactorEnabled()) {
            if (! $request->session()->get(self::SESSION_KEY)) {
                // A browser the user chose to trust skips the code (see TrustedDevice).
                if (app(TrustedDevice::class)->valid($request, $user)) {
                    $request->session()->put(self::SESSION_KEY, true);
                    Audit::log('two_factor.trusted_device', $user, 'Signed in from a trusted device');

                    return $next($request);
                }

                return redirect()->route('two-factor.challenge');
            }

            return $next($request);
        }

        // Admins must have 2FA. During the grace period they can work normally (with a reminder banner);
        // afterwards only the security page (where they set it up) and logout are open to them.
        if ($user->isAdmin() && ! $request->routeIs('admin.security*', 'logout', 'two-factor.*', 'account.password')) {
            $days = (int) config('security.admin_2fa_grace_days', 7);

            if ($days > 0) {
                if (! $user->two_factor_grace_ends_at) {
                    $user->forceFill(['two_factor_grace_ends_at' => now()->addDays($days)])->save();
                }

                if (now()->lt($user->two_factor_grace_ends_at)) {
                    return $next($request);
                }
            }

            return redirect()->route('admin.security')->with('warning', 'For your protection, turn on two-factor authentication to continue.');
        }

        return $next($request);
    }
}
