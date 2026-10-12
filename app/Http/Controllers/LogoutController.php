<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers;

use App\Support\AuthGuards;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        // Which door's "Sign out" was actually clicked: every sign-out form says so explicitly
        // (see AuthGuards::fromHint()), because when both the admin and realtor areas are signed
        // in at once in one browser, that can't be worked out from session state alone.
        $guard = AuthGuards::fromHint($request);

        // Only this guard's login is cleared: if the admin and realtor areas are both signed in
        // in this same browser, signing out of one leaves the other untouched.
        AuthGuards::logout($request, $guard);

        // Admins land back on their own sign-in; everyone else on the website.
        return redirect()->route($guard === 'admin' ? 'admin.login' : 'home');
    }
}
