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
 *  - A user (admin or realtor) who has turned 2FA on must pass the challenge once per session.
 *  - 2FA is optional, never forced, for every account including admins.
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

        return $next($request);
    }
}
