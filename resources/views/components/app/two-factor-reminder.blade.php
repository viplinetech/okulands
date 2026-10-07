{{--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Friendly reminder shown to an admin who has not yet set up two-factor (only during the grace period).
--}}
@php
    $u = auth()->user();
    $show = $u && $u->isAdmin() && ! $u->hasTwoFactorEnabled() && ! request()->routeIs('admin.security*');
    $left = $show && $u->two_factor_grace_ends_at ? max(0, (int) ceil(now()->floatDiffInDays($u->two_factor_grace_ends_at, false))) : null;
@endphp
@if ($show)
    <div role="status" class="mb-5 flex flex-col gap-3 rounded-3xl border border-amber-500/40 bg-amber-500/10 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-amber-500/20 text-amber-600"><x-icon name="shield" class="h-5 w-5" /></span>
            <div class="text-sm leading-relaxed text-ink">
                <p class="font-bold">Protect your admin account</p>
                <p class="text-mute">Turn on two-factor sign-in. It takes about 2 minutes, once.
                    @if ($left !== null) It becomes required in <strong class="text-ink">{{ $left }} {{ \Illuminate\Support\Str::plural('day', $left) }}</strong>. @endif
                </p>
            </div>
        </div>
        <a href="{{ route('admin.security') }}" class="btn btn-primary btn-sm shrink-0">Set it up now</a>
    </div>
@endif
