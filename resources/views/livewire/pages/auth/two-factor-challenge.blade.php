<?php

use App\Http\Middleware\EnforceTwoFactor;
use App\Services\TrustedDevice;
use App\Services\TwoFactorService;
use App\Support\Audit;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $code = '';

    public bool $recovery = false;

    /** "Trust this device": skip the code on this browser for a while. */
    public bool $trust = false;

    public function mount(): void
    {
        $user = auth()->user();

        // Nothing to do if this account has no 2FA, or the challenge was already passed this session.
        if (! $user->hasTwoFactorEnabled() || session(EnforceTwoFactor::SESSION_KEY)) {
            $this->redirect(route($user->homeRoute()), navigate: false);
        }
    }

    public function verify(TwoFactorService $twoFactor): void
    {
        $this->validate(['code' => ['required', 'string', 'max:20']]);

        $user = auth()->user();
        $key = 'two-factor-challenge:'.$user->id;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('code', 'Too many attempts. Try again in '.RateLimiter::availableIn($key).' seconds.');

            return;
        }

        $ok = $this->recovery ? $twoFactor->useRecoveryCode($user, $this->code) : $twoFactor->verify($user, $this->code);

        if (! $ok) {
            RateLimiter::hit($key, 300);
            Audit::log('two_factor.failed', $user, 'Wrong two-factor code entered');
            $this->addError('code', $this->recovery ? 'That recovery code is not valid.' : 'That code is not correct. Wait for a fresh one and try again.');

            return;
        }

        RateLimiter::clear($key);
        session()->put(EnforceTwoFactor::SESSION_KEY, true);
        session()->regenerate();
        Audit::log('two_factor.passed', $user, 'Two-factor check passed'.($this->trust ? ' (device trusted)' : ''));

        if ($this->trust) {
            cookie()->queue(app(TrustedDevice::class)->issue($user));
        }

        $this->redirectIntended(default: route($user->homeRoute()), navigate: false);
    }
}; ?>

<div>
    <form wire:submit="verify" class="space-y-5">
        <div>
            <x-input-label for="code" :value="$recovery ? 'Recovery code' : '6-digit code'" />
            <x-text-input wire:model="code" id="code" type="text" name="code" required autofocus autocomplete="one-time-code"
                          :inputmode="$recovery ? 'text' : 'numeric'" :placeholder="$recovery ? 'xxxxx-xxxxx' : '123456'" class="text-center font-mono text-xl tracking-[0.3em]" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-ink/10 bg-soft px-4 py-3 text-sm">
            <input type="checkbox" wire:model="trust" class="mt-0.5 h-4 w-4 shrink-0 rounded border-ink/30 text-brand focus:ring-brand">
            <span class="leading-snug text-ink"><strong class="font-semibold">Trust this device for {{ app(TrustedDevice::class)->days() }} days</strong><span class="block text-xs text-mute">Only on your own computer or phone. You won&rsquo;t be asked for a code here again until then.</span></span>
        </label>

        <x-primary-button class="w-full">
            Verify and continue
            <x-icon name="arrow" class="arrow h-4 w-4" />
        </x-primary-button>
    </form>

    <div class="mt-6 flex flex-col items-center gap-3 text-sm">
        <button type="button" wire:click="$toggle('recovery')" class="font-semibold text-brand hover:underline">
            {{ $recovery ? 'Use my authenticator app instead' : 'Lost your phone? Use a recovery code' }}
        </button>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" class="text-mute hover:text-ink">Cancel and sign out</button>
        </form>
    </div>
</div>
