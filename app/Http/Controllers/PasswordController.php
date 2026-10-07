<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers;

use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults(), 'different:current_password'],
        ], [
            'current_password.current_password' => 'Your current password is not correct.',
            'password.different' => 'Choose a password you have not used just now.',
        ]);

        $user = $request->user();
        $user->forceFill(['password' => $data['password']])->save();

        // A new session id after a credential change limits the damage of a hijacked session.
        $request->session()->regenerate();

        Audit::log('auth.password_changed', $user, 'Password changed');

        return back()->with('success', 'Your password has been updated.');
    }
}
