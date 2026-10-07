<?php

namespace App\Livewire\Forms;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Attempt to sign in through one of the two doors:
     *   'realtor' (the public /login)  admits realtors only;
     *   'admin'   (the private admin sign-in)  admits admins only.
     * A right password at the wrong door fails exactly like a wrong password, and no session is ever
     * started for it, so the doors reveal nothing about which accounts exist or what role they hold.
     *
     * @throws ValidationException
     */
    public function authenticate(string $area = 'realtor'): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = ['email' => strtolower(trim($this->email)), 'password' => $this->password];
        $provider = Auth::guard('web')->getProvider();
        $user = $provider->retrieveByCredentials(['email' => $credentials['email']]);

        $belongsHere = $user && ($area === 'admin' ? $user->isAdmin() : $user->isAffiliate());

        if (! $user || ! $provider->validateCredentials($user, $credentials) || ! $belongsHere) {
            RateLimiter::hit($this->throttleKey());
            event(new Failed('web', $user, ['email' => $credentials['email']]));

            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
            ]);
        }

        // A suspended account may never start a session, even with the right password.
        if (! $user->isActive()) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.email' => 'Your account has been suspended. Please contact support.',
            ]);
        }

        Auth::guard('web')->login($user, $this->remember);
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
