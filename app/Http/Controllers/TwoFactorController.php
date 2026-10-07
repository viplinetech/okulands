<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers;

use App\Http\Middleware\EnforceTwoFactor;
use App\Services\TwoFactorService;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Enable, confirm, regenerate and disable two-factor authentication from the security pages. */
class TwoFactorController extends Controller
{
    public function __construct(private TwoFactorService $twoFactor) {}

    private function home(Request $request): string
    {
        return $request->user()->isAdmin() ? route('admin.security') : route('realtor.security');
    }

    /** Step 1: create a secret and show its QR code (kept only in the session until confirmed). */
    public function start(Request $request): RedirectResponse
    {
        if ($request->user()->hasTwoFactorEnabled()) {
            return redirect($this->home($request));
        }

        $request->session()->put('two_factor_pending_secret', $this->twoFactor->newSecret());

        return redirect($this->home($request));
    }

    /** Step 2: the user proves their app works by entering a current code. */
    public function confirm(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:12']]);
        $user = $request->user();
        $secret = $request->session()->get('two_factor_pending_secret');

        if (! $secret) {
            return redirect($this->home($request))->with('error', 'Your setup session expired. Please start again.');
        }

        if (! $this->twoFactor->verifyPending($secret, $data['code'], $user->id)) {
            return redirect($this->home($request))->withErrors(['code' => 'That code is not correct. Check your app and try again.']);
        }

        $codes = $this->twoFactor->newRecoveryCodes();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $codes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->forget('two_factor_pending_secret');
        $request->session()->put(EnforceTwoFactor::SESSION_KEY, true);
        Audit::log('two_factor.enabled', $user, 'Two-factor authentication enabled');

        return redirect($this->home($request))
            ->with('success', 'Two-factor authentication is now on.')
            ->with('recovery_codes', $codes);
    }

    public function regenerate(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']], ['password.current_password' => 'Your password is not correct.']);
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            return redirect($this->home($request));
        }

        $codes = $this->twoFactor->newRecoveryCodes();
        $user->forceFill(['two_factor_recovery_codes' => $codes])->save();
        Audit::log('two_factor.recovery_regenerated', $user, 'Recovery codes regenerated');

        return redirect($this->home($request))->with('success', 'New recovery codes created. The old ones no longer work.')->with('recovery_codes', $codes);
    }

    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Admins must keep two-factor on; it cannot be switched off.
        if ($user->isAdmin()) {
            return redirect($this->home($request))->with('error', 'Two-factor authentication is required for administrators.');
        }

        $data = $request->validate([
            'password' => ['required', 'current_password'],
            'code' => ['required', 'string', 'max:12'],
        ], ['password.current_password' => 'Your password is not correct.']);

        if (! $this->twoFactor->verify($user, $data['code'])) {
            return redirect($this->home($request))->withErrors(['code' => 'That code is not correct.']);
        }

        $user->forceFill(['two_factor_secret' => null, 'two_factor_recovery_codes' => null, 'two_factor_confirmed_at' => null])->save();
        Audit::log('two_factor.disabled', $user, 'Two-factor authentication disabled');

        return redirect($this->home($request))->with('success', 'Two-factor authentication has been turned off.');
    }
}
