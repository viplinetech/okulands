<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers;

use App\Support\Audit;
use App\Support\AuthGuards;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

/**
 * Shared by both the admin and realtor security pages under one /account/password URL, which
 * can't say by itself which area a request is for when both are signed in at once in one browser
 * (see AuthGuards) — the form carries an explicit "area" hint instead of relying on $request->user().
 */
class PasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $guard = AuthGuards::fromHint($request);

        $data = $request->validate([
            'current_password' => ['required', 'current_password:'.$guard],
            'password' => ['required', 'confirmed', Password::defaults(), 'different:current_password'],
        ], [
            'current_password.current_password' => 'Your current password is not correct.',
            'password.different' => 'Choose a password you have not used just now.',
        ]);

        $user = Auth::guard($guard)->user();
        $user->forceFill(['password' => $data['password']])->save();

        // A new session id after a credential change limits the damage of a hijacked session.
        $request->session()->regenerate();

        Audit::log('auth.password_changed', $user, 'Password changed');

        return back()->with('success', 'Your password has been updated.');
    }
}
