<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirect(route("dashboard", absolute: false));

            return;
        }

        try {
            Auth::user()->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);
            Session::flash('mail_error', 'We could not send that email right now. Please try again in a few minutes.');

            return;
        }

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    @if (session('mail_error'))
        <div class="mb-4 flex items-start gap-3 rounded-2xl border border-flag-500/30 bg-flag-500/10 px-4 py-4 text-sm leading-relaxed text-ink">
            <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-flag-500" />
            <span>{{ session('mail_error') }}</span>
        </div>
    @endif

    <div class="flex items-start gap-3 rounded-2xl border border-brand/25 bg-brand/10 px-4 py-4 text-sm leading-relaxed text-ink">
        <x-icon name="mail" class="mt-0.5 h-5 w-5 shrink-0 text-brand" />
        <span>We sent a confirmation link to <strong>{{ auth()->user()->email }}</strong>. Open it to activate your realtor account and referral link. Check your spam folder if it does not arrive within a minute.</span>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-4 rounded-xl bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-600 dark:text-emerald-400">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <x-primary-button wire:click="sendVerification">
            {{ __('Resend verification email') }}
        </x-primary-button>

        <button wire:click="logout" type="button" class="text-sm font-semibold text-mute underline hover:text-ink">
            {{ __('Log out') }}
        </button>
    </div>
</div>
