{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Password, two-factor and sign-in details. Shared by the realtor and admin security pages.
    Expects: $user, $pendingSecret, $qr
--}}
@php $mandatory = $user->isAdmin(); @endphp

{{-- One-time recovery codes (shown right after enabling / regenerating) --}}
@if (session('recovery_codes'))
    <section class="card mb-4 border-amber-500/40 !bg-amber-500/5">
        <div class="flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-600"><x-icon name="alert" class="h-5 w-5" /></span>
            <div>
                <h2 class="card-title">Save your recovery codes</h2>
                <p class="text-xs text-mute">Each works once if you lose your phone. They will not be shown again.</p>
            </div>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
            @foreach (session('recovery_codes') as $code)
                <code class="rounded-xl bg-card px-3 py-2.5 text-center font-mono text-sm font-bold text-ink">{{ $code }}</code>
            @endforeach
        </div>
        <button type="button" data-copy="{{ implode("\n", session('recovery_codes')) }}" class="btn btn-outline btn-sm mt-4"><x-icon name="copy" class="h-4 w-4" /><span data-label>Copy all codes</span></button>
    </section>
@endif

<div class="grid gap-4 lg:grid-cols-2">
    {{-- Two-factor --}}
    <section class="card">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="stat-icon"><x-icon name="shield" class="h-5 w-5" /></span>
                <div>
                    <h2 class="card-title">Two-factor authentication</h2>
                    <p class="text-xs text-mute">A code from your phone in addition to your password.</p>
                </div>
            </div>
            <x-app.badge :status="$user->hasTwoFactorEnabled() ? 'on' : 'off'">{{ $user->hasTwoFactorEnabled() ? 'On' : 'Off' }}</x-app.badge>
        </div>

        @if ($user->hasTwoFactorEnabled())
            <p class="mt-4 text-sm leading-relaxed text-mute">Your account is protected. You have <strong class="text-ink">{{ count($user->two_factor_recovery_codes ?? []) }}</strong> recovery code(s) left.</p>
            <p class="mt-2 text-xs leading-relaxed text-mute">Devices you chose to trust skip the code for {{ app(\App\Services\TrustedDevice::class)->days() }} days. Changing your password, or setting up two-factor again, signs out every trusted device.</p>

            <form method="POST" action="{{ route('two-factor.recovery') }}" class="mt-5 space-y-3">
                @csrf
                <label for="rc-password" class="field-label">Confirm password to make new recovery codes</label>
                <input id="rc-password" type="password" name="password" required autocomplete="current-password" class="field">
                @error('password')<p class="err">{{ $message }}</p>@enderror
                <button type="submit" class="btn btn-outline btn-sm w-full sm:w-auto">Generate new recovery codes</button>
            </form>

            @unless ($mandatory)
                <form method="POST" action="{{ route('two-factor.disable') }}" data-confirm="Turn off two-factor authentication? Your account will be less protected." data-confirm-yes="Turn off" class="mt-6 space-y-3 border-t border-ink/10 pt-5">
                    @csrf @method('DELETE')
                    <p class="text-sm font-bold text-ink">Turn off two-factor</p>
                    <input type="password" name="password" required autocomplete="current-password" placeholder="Your password" class="field">
                    <input type="text" name="code" required inputmode="numeric" autocomplete="one-time-code" placeholder="6-digit code" class="field font-mono">
                    @error('code')<p class="err">{{ $message }}</p>@enderror
                    <button type="submit" class="btn btn-danger btn-sm">Turn off</button>
                </form>
            @else
                <p class="mt-5 rounded-2xl bg-soft px-4 py-3 text-xs text-mute">Two-factor is required for administrators and cannot be turned off.</p>
            @endunless

        @elseif ($pendingSecret)
            <ol class="mt-5 space-y-5 text-sm text-mute">
                <li>
                    <p class="font-bold text-ink">1. Scan this QR code</p>
                    <p>Use Google Authenticator, Microsoft Authenticator, Authy or 1Password.</p>
                    <div class="mt-3 inline-block rounded-3xl bg-white p-3 shadow-soft [&_svg]:h-44 [&_svg]:w-44">{!! $qr !!}</div>
                    <p class="mt-3 text-xs">Can&rsquo;t scan? Enter this key instead:</p>
                    <p class="mt-1 break-all rounded-xl bg-soft px-3 py-2 font-mono text-xs font-bold tracking-wider text-ink">{{ implode(' ', str_split($pendingSecret, 4)) }}</p>
                </li>
                <li>
                    <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-3">
                        @csrf
                        <label for="code" class="font-bold text-ink">2. Enter the 6-digit code</label>
                        <input id="code" name="code" required inputmode="numeric" autocomplete="one-time-code" placeholder="123456" class="field text-center font-mono text-xl tracking-[0.3em]">
                        @error('code')<p class="err">{{ $message }}</p>@enderror
                        <button type="submit" class="btn btn-primary btn-sm w-full sm:w-auto">Turn on two-factor</button>
                    </form>
                </li>
            </ol>
        @else
            <p class="mt-4 text-sm leading-relaxed text-mute">
                @if ($mandatory) <strong class="text-ink">Required for admins.</strong> @endif
                Even if someone learns your password, they cannot sign in without your phone.
                @if ($mandatory) Admins can see realtors&rsquo; bank details and approve payments, so this protects the whole business. @endif
            </p>

            <div class="mt-5 rounded-2xl bg-soft p-4 text-sm">
                <p class="font-bold text-ink">How it works (about 2 minutes, done once)</p>
                <ol class="mt-3 space-y-2.5 text-mute">
                    <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand/15 text-xs font-bold text-brand">1</span><span>Install a free authenticator app on your phone: <strong class="text-ink">Google Authenticator</strong>, <strong class="text-ink">Microsoft Authenticator</strong> or <strong class="text-ink">Authy</strong>.</span></li>
                    <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand/15 text-xs font-bold text-brand">2</span><span>Click <strong class="text-ink">Set up two-factor</strong> below and scan the picture with the app.</span></li>
                    <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand/15 text-xs font-bold text-brand">3</span><span>Type the 6-digit number the app shows, then <strong class="text-ink">save your recovery codes</strong> somewhere safe (not only on that phone).</span></li>
                </ol>
                <p class="mt-3 text-xs text-mute">From then on, signing in asks for the 6-digit number from the app. On your own computer you can tick &ldquo;Trust this device&rdquo; so you are asked only once a month. If you lose your phone, use a recovery code, or ask the site developer to reset it.</p>
            </div>

            <form method="POST" action="{{ route('two-factor.start') }}" class="mt-5">@csrf
                <button type="submit" class="btn btn-primary btn-sm"><x-icon name="lock" class="h-4 w-4" /> Set up two-factor</button>
            </form>
        @endif
    </section>

    {{-- Password --}}
    <section class="card">
        <div class="flex items-center gap-3">
            <span class="stat-icon"><x-icon name="key" class="h-5 w-5" /></span>
            <div>
                <h2 class="card-title">Change password</h2>
                <p class="text-xs text-mute">10+ characters with upper and lower case letters and a number.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('account.password') }}" class="mt-5 space-y-4">
            @csrf
            <div>
                <label for="current_password" class="field-label">Current password</label>
                <input id="current_password" type="password" name="current_password" required autocomplete="current-password" class="field">
                @error('current_password')<p class="err">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="new_password" class="field-label">New password</label>
                <input id="new_password" type="password" name="password" required autocomplete="new-password" class="field">
                @error('password')<p class="err">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="new_password_confirmation" class="field-label">Confirm new password</label>
                <input id="new_password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="field">
            </div>
            <button type="submit" class="btn btn-primary btn-sm w-full sm:w-auto">Update password</button>
        </form>
    </section>
</div>

<section class="card mt-4">
    <h2 class="card-title">Sign-in activity</h2>
    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
        <div class="rounded-2xl bg-soft p-4"><dt class="text-[0.62rem] font-bold uppercase tracking-wider text-mute">Last sign-in</dt><dd class="mt-1 font-bold text-ink">{{ $user->last_login_at?->format('M j, Y · g:ia') ?? 'This session' }}</dd></div>
        <div class="rounded-2xl bg-soft p-4"><dt class="text-[0.62rem] font-bold uppercase tracking-wider text-mute">From IP</dt><dd class="mt-1 font-mono font-bold text-ink">{{ $user->last_login_ip ?? request()->ip() }}</dd></div>
        <div class="rounded-2xl bg-soft p-4"><dt class="text-[0.62rem] font-bold uppercase tracking-wider text-mute">Account created</dt><dd class="mt-1 font-bold text-ink">{{ $user->created_at?->format('M j, Y') }}</dd></div>
    </dl>
    <p class="mt-4 text-xs text-mute">If you see anything you don&rsquo;t recognise, change your password now.</p>
</section>
