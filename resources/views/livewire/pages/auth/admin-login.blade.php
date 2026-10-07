<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

/**
 * The private admin sign-in (/adminbackend). It is not linked from anywhere on the public website,
 * only admins can pass it, and every admin must then complete two-factor authentication.
 */
new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate('admin');

        Session::regenerate();

        $this->redirectIntended(default: route('admin.dashboard', absolute: false), navigate: false);
    }
}; ?>

<div>
    @auth
        <div class="surface p-6 sm:p-8">
            <p class="eyebrow text-brand">Already signed in</p>
            <h2 class="display mt-3 text-3xl text-ink">You are signed in as {{ auth()->user()->name }}.</h2>
            <p class="mt-3 text-sm leading-relaxed text-mute">Admin sign-in needs a separate session. Sign out first, then sign in with the account you want to use.</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to my dashboard</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn-outline w-full sm:w-auto">Sign out</button></form>
            </div>
        </div>
    @else
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('Admin email')" />
            <x-text-input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input wire:model="form.password" id="password" class="mt-2" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">
            {{ __('Sign in') }}
            <x-icon name="lock" class="h-4 w-4" />
        </x-primary-button>
    </form>

    <p class="mt-6 flex items-start gap-2.5 rounded-2xl bg-soft px-4 py-3 text-xs leading-relaxed text-mute">
        <x-icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-brand" />
        Restricted to authorised staff. Every sign-in is recorded and requires a second verification step.
    </p>
    @endauth
</div>
