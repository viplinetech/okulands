<x-layouts.admin title="Confirm it is you">
    <x-app.page-header title="Confirm it is you" kicker="Security check" subtitle="Payments, sales, realtor accounts and exports need a fresh code from your authenticator app, even though you are already signed in." />

    <div class="mx-auto max-w-md">
        <section class="card">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand/10 text-brand"><x-icon name="shield" class="h-6 w-6" /></span>

            <form method="POST" action="{{ route('admin.confirm.verify') }}" class="mt-5 space-y-5" data-single>
                @csrf
                <input type="hidden" name="recovery" value="{{ $recovery ? 1 : 0 }}">
                <div>
                    <label for="code" class="field-label">{{ $recovery ? 'Recovery code' : '6-digit code from your app' }}</label>
                    <input id="code" name="code" type="text" required autofocus autocomplete="one-time-code" maxlength="20"
                           inputmode="{{ $recovery ? 'text' : 'numeric' }}" placeholder="{{ $recovery ? 'xxxxx-xxxxx' : '123456' }}"
                           class="field text-center font-mono text-xl tracking-[0.3em]">
                    @error('code')<p class="mt-2 text-xs font-medium text-flag-500">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn btn-primary w-full">Confirm and continue <x-icon name="arrow" class="arrow h-4 w-4" /></button>
            </form>

            <p class="mt-5 text-center text-xs leading-relaxed text-mute">After you confirm, you can carry on with sensitive actions for {{ $minutes }} minutes.</p>
            <p class="mt-3 text-center text-sm">
                <a href="{{ route('admin.confirm', $recovery ? [] : ['recovery' => 1]) }}" class="font-semibold text-brand hover:underline">
                    {{ $recovery ? 'Use my authenticator app instead' : 'Lost your phone? Use a recovery code' }}
                </a>
            </p>
        </section>
    </div>
</x-layouts.admin>
