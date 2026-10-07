<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Confirms the email address named in the signed link, and signs that account in if it isn't already
     * (the "signed" middleware checks the link was issued by us and hasn't expired or been tampered with; the
     * hash below checks it matches this account's current email). This way the link works on its own, whichever
     * browser or device it is opened in, instead of only when the person is still signed in from registering.
     */
    public function __invoke(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        abort_unless(hash_equals(sha1($user->getEmailForVerification()), $hash), 403);

        if (Auth::id() === $user->id) {
            // Already signed in as this account: reuse that same instance, so the active session
            // reflects the change straight away instead of holding an outdated, unverified copy.
            $user = Auth::user();
        } else {
            Auth::login($user);
            $request->session()->regenerate();
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        // Realtors see their welcome page first; everyone else goes to their usual home.
        if ($user->isAffiliate()) {
            return redirect()->route('realtor.welcome');
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
