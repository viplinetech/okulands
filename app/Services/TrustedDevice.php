<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * "Trust this device": after a correct two-factor code the user can skip the code on that browser for a while.
 *
 * The cookie is encrypted by Laravel and carries a signature over the user, the expiry, their two-factor secret and
 * their password hash. So it cannot be forged, and it stops working everywhere the moment the user changes their
 * password or their two-factor is reset or set up again.
 */
class TrustedDevice
{
    public const COOKIE = 'oku_2fa_trust';

    public function days(): int
    {
        return max(1, (int) config('security.trusted_device_days', 30));
    }

    public function issue(User $user): Cookie
    {
        $expires = now()->addDays($this->days())->timestamp;

        return cookie(self::COOKIE, $user->id.'|'.$expires.'|'.$this->sign($user, $expires), $this->days() * 1440, '/', null, (bool) config('session.secure'), true, false, 'lax');
    }

    public function valid(Request $request, User $user): bool
    {
        $parts = explode('|', (string) $request->cookie(self::COOKIE));

        if (count($parts) !== 3 || (int) $parts[0] !== $user->id || (int) $parts[1] < now()->timestamp) {
            return false;
        }

        return hash_equals($this->sign($user, (int) $parts[1]), (string) $parts[2]);
    }

    private function sign(User $user, int $expires): string
    {
        return hash_hmac('sha256', $user->id.'|'.$expires.'|'.$user->two_factor_secret.'|'.$user->password, (string) config('app.key'));
    }
}
