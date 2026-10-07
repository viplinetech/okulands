<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\RequireStepUp;
use App\Services\TwoFactorService;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/** "Confirm it is you": a fresh authenticator (or recovery) code before a sensitive admin action. */
class StepUpController extends Controller
{
    public function show(Request $request)
    {
        return view('admin.confirm', [
            'recovery' => $request->boolean('recovery'),
            'minutes' => intdiv(RequireStepUp::WINDOW, 60),
        ]);
    }

    public function verify(Request $request, TwoFactorService $twoFactor): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:20']]);
        $recovery = $request->boolean('recovery');
        $user = $request->user();
        $key = 'step-up:'.$user->id;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['code' => 'Too many attempts. Try again in '.RateLimiter::availableIn($key).' seconds.']);
        }

        $ok = $recovery ? $twoFactor->useRecoveryCode($user, $data['code']) : $twoFactor->verify($user, $data['code']);

        if (! $ok) {
            RateLimiter::hit($key, 300);
            Audit::log('two_factor.step_up_failed', $user, 'Wrong code entered to confirm a sensitive action');

            return back()->withErrors(['code' => $recovery
                ? 'That recovery code is not valid.'
                : 'That code is not correct. If you just signed in, wait for the next code to appear in your app and try again.']);
        }

        RateLimiter::clear($key);
        $request->session()->put(RequireStepUp::SESSION_KEY, time());
        Audit::log('two_factor.step_up', $user, 'Confirmed a sensitive action with a two-factor code');

        return redirect()->to($request->session()->pull('step_up_intended', route('admin.dashboard')))
            ->with('success', 'Confirmed. You can continue for the next '.intdiv(RequireStepUp::WINDOW, 60).' minutes.');
    }
}
