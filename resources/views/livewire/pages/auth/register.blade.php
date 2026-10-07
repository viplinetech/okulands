<?php

use App\Models\SiteSetting;
use App\Models\User;
use App\Rules\NotDisposableEmail;
use App\Services\ReferralAttribution;
use App\Services\Turnstile;
use App\Support\Audit;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $phone_code = '+234';

    public string $gender = '';

    public string $password = '';

    public string $password_confirmation = '';

    /** Honeypot: humans never see or fill this. */
    public string $website = '';

    /** Cloudflare Turnstile token, filled in by the widget. */
    public string $cfToken = '';

    public ?string $invitedBy = null;

    public function mount(ReferralAttribution $attribution): void
    {
        session(['register_shown_at' => time()]);

        $visit = $attribution->visit(request());
        $this->invitedBy = $visit?->referrer?->name;
    }

    /**
     * Every sign-up becomes a realtor with instant access. If this browser arrived through
     * another realtor's link, the new realtor is placed in that realtor's downline.
     */
    public function register(ReferralAttribution $attribution, Turnstile $turnstile): void
    {
        if (! (SiteSetting::current()->realtor_registration_enabled ?? true)) {
            $this->addError('email', 'Registration is closed right now. Please check back soon.');

            return;
        }

        $key = 'register:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Too many sign-ups from this connection. Please try again later.');

            return;
        }

        if ($this->website !== '') {
            return; // a bot filled the hidden field
        }

        // Humans need a few seconds to fill this in; scripts submit instantly.
        $minSeconds = (int) config('auth.register_min_seconds', 3);
        if ($minSeconds > 0 && time() - (int) session('register_shown_at', time()) < $minSeconds) {
            $this->addError('email', 'That was a little too fast. Please try again.');

            return;
        }

        if (! $turnstile->passes($this->cfToken, request()->ip())) {
            $this->cfToken = '';
            $this->addError('email', 'We could not confirm you are human. Please wait a moment for the check to finish and try again.');
            $this->js('window.turnstile && window.turnstile.reset()');

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'string', 'lowercase', app()->isProduction() ? 'email:rfc,dns' : 'email:rfc', 'max:255', 'unique:'.User::class, new NotDisposableEmail],
            'phone' => ['required', 'string', 'regex:/^[0-9]{7,11}$/'],
            'gender' => ['required', 'in:male,female'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ], [
            'gender.required' => 'Please choose your gender.',
            'gender.in' => 'Please choose your gender.',
            'phone.regex' => 'Enter the number in digits only, up to 11 digits, without the country code or the first 0.',
        ]);

        RateLimiter::hit($key, 3600);

        $validated['phone'] = \App\Support\Countries::combine($this->phone_code, $validated['phone']);

        $user = new User(collect($validated)->only(['name', 'email', 'phone', 'gender', 'password'])->all());
        $user->forceFill(['role' => 'realtor', 'status' => 'active'])->save();

        $attribution->attachToNewUser($user, request());

        // The account is created either way; a mail problem should never block sign-up or crash the page.
        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            report($e);
            session()->flash('mail_error', 'Your account was created, but we could not send the verification email right now. Use "Resend verification email" on the next page to try again.');
        }

        Auth::login($user);
        session()->regenerate();
        Audit::log('auth.registered', $user, $user->name.' joined as a realtor'.($user->referred_by ? ' (referred)' : ''), [], $user->id);

        // event(Registered) has just emailed the verification link; the app opens once it is confirmed.
        $this->redirect(route('verification.notice'), navigate: false);
    }
}; ?>

<div>
    @if ($invitedBy)
        <div class="mb-6 flex items-center gap-3 rounded-2xl border border-brand/25 bg-brand/10 px-4 py-3 text-sm">
            <x-icon name="users" class="h-5 w-5 shrink-0 text-brand" />
            <span class="text-ink">You were invited by <strong>{{ $invitedBy }}</strong>. You&rsquo;ll join their team automatically.</span>
        </div>
    @endif

    <form wire:submit="register" class="space-y-5" novalidate>
        <div>
            <label for="name" class="field-label">Full name</label>
            <x-text-input wire:model="name" id="name" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="grid gap-5">
            <div>
                <label for="email" class="field-label">Email</label>
                <x-text-input wire:model="email" id="email" type="email" name="email" required autocomplete="username" placeholder="you@example.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div>
                <label for="phone" class="field-label">Phone / WhatsApp</label>
                <div class="flex gap-2">
                    {{-- Realtors are paid in Nigeria, so the code is fixed. --}}
                    <span class="field !w-auto shrink-0 flex items-center gap-1.5 font-semibold text-ink" aria-label="Country code: Nigeria +234">🇳🇬 +234</span>
                    <input wire:model="phone" id="phone" type="tel" name="phone" required aria-required="true" autocomplete="tel-national" inputmode="numeric" pattern="[0-9]*" maxlength="11" placeholder="8050822237" class="field min-w-0 flex-1" data-digits-only data-strip-leading-zero>
                </div>
                <p class="mt-1.5 text-xs text-mute">Digits only. Leave out the first 0 (for example 8050822237).</p>
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
        </div>

        <div>
            <label for="gender" class="field-label">Gender</label>
            <select wire:model="gender" id="gender" name="gender" required aria-required="true" class="field">
                <option value="">Choose a gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
            <x-input-error :messages="$errors->get('gender')" class="mt-2" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="password" class="field-label">Password</label>
                <x-text-input wire:model="password" id="password" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div>
                <label for="password_confirmation" class="field-label">Confirm password</label>
                <x-text-input wire:model="password_confirmation" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>
        </div>
        <p class="-mt-2 text-xs text-mute">At least 10 characters with upper and lower case letters and a number.</p>

        <div class="hidden" aria-hidden="true"><input type="text" wire:model="website" tabindex="-1" autocomplete="off"></div>

        @if (app(Turnstile::class)->enabled())
            <div wire:ignore>
                <div class="cf-turnstile" data-sitekey="{{ app(Turnstile::class)->siteKey() }}" data-callback="okuTurnstile" data-theme="auto"></div>
            </div>
            @script
            <script>
                window.okuTurnstile = (token) => $wire.set('cfToken', token);
                if (! document.querySelector('script[data-turnstile]')) {
                    const s = document.createElement('script');
                    s.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
                    s.async = true; s.defer = true; s.dataset.turnstile = '1';
                    document.head.appendChild(s);
                }
            </script>
            @endscript
        @endif

        <x-primary-button class="w-full">
            {{ __('Create my account') }}
            <x-icon name="arrow" class="arrow h-4 w-4" />
        </x-primary-button>

        <p class="text-center text-xs leading-relaxed text-mute">By creating an account you agree to represent Oku Lands honestly and not to make promises on the company&rsquo;s behalf.</p>
    </form>
</div>
