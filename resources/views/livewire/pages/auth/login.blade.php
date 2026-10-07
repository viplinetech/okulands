<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: false);
    }
}; ?>

<div>
    @auth
        <div class="surface p-6 sm:p-8">
            <p class="eyebrow text-brand">Already signed in</p>
            <h2 class="display mt-3 text-3xl text-ink">You are signed in as {{ auth()->user()->name }}.</h2>
            <p class="mt-3 text-sm leading-relaxed text-mute">Realtor sign-in needs a separate session. Sign out first, then sign in with the account you want to use.</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to my dashboard</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn-outline w-full sm:w-auto">Sign out</button></form>
            </div>
        </div>
    @else
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" class="!mb-0" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-brand hover:underline" href="{{ route('password.request') }}" wire:navigate>{{ __('Forgot password?') }}</a>
                @endif
            </div>
            <x-text-input wire:model="form.password" id="password" class="mt-2" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <label for="remember" class="flex items-center gap-3 text-sm text-mute">
            <input wire:model="form.remember" id="remember" type="checkbox" class="h-5 w-5 rounded-md border-ink/25 text-brand focus:ring-brand/30" name="remember">
            {{ __('Keep me signed in') }}
        </label>

        <x-primary-button class="w-full">
            {{ __('Log in') }}
            <x-icon name="arrow" class="arrow h-4 w-4" />
        </x-primary-button>
    </form>
    @endauth
</div>
