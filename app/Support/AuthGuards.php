<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Admins and realtors each have their own guard (see config/auth.php), both stored in the same
 * session cookie, so both can be signed in at once in the same browser. These helpers keep every
 * place that logs a guard out from accidentally wiping the *other* guard's login out of that
 * shared session too.
 */
class AuthGuards
{
    /**
     * Which guard this request is actually for. Decided by the URL first (an /adminbackend/*
     * page always means the admin guard, a /realtor/* page always means the realtor guard), since
     * when both are signed in at once in one browser, "whichever guard happens to be logged in"
     * is not a safe way to tell them apart: it would resolve every request to the same guard
     * regardless of which area the visitor is actually in. Only genuinely shared, area-less
     * routes (logout, the two-factor challenge, /account/*, the generic /dashboard redirect) fall
     * back to "whichever is signed in, admin first" — and those have their own explicit "area"
     * hint where it actually matters (see the sign-out forms, for example).
     */
    public static function active(Request $request): string
    {
        if ($request->is('adminbackend*')) {
            return Auth::guard('admin')->check() ? 'admin' : 'web';
        }

        if ($request->is('realtor*')) {
            return 'web';
        }

        return Auth::guard('admin')->check() ? 'admin' : 'web';
    }

    /**
     * For a shared, area-less route (like /account/password or /account/two-factor/*) whose own
     * URL can't say which area it's for: trusts an explicit "area" hint from the request body
     * (every form on a shared page carries one; see security/panel.blade.php) when it names a
     * guard that is genuinely signed in here, otherwise falls back to active().
     */
    public static function fromHint(Request $request): string
    {
        $hint = $request->string('area')->value();

        return in_array($hint, ['admin', 'web'], true) && Auth::guard($hint)->check()
            ? $hint
            : self::active($request);
    }

    /** The "other" guard name, for checking whether it still needs its session data kept. */
    public static function other(string $guard): string
    {
        return $guard === 'admin' ? 'web' : 'admin';
    }

    /**
     * Log the given guard out. Only fully invalidates the session (new ID, dropped data, fresh
     * CSRF token) when no other guard is still signed in there; otherwise just rotates the
     * session ID for safety, keeping the other guard's login intact.
     */
    public static function logout(Request $request, string $guard): void
    {
        Auth::guard($guard)->logout();

        if (Auth::guard(self::other($guard))->check()) {
            $request->session()->regenerate();
        } else {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }
}
