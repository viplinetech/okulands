<!--
    Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
    Realtor app shell.
      • Phones/tablets: top bar + floating bottom bar (centre "Share" action + "More" sheet).
        The bar shows more items as the screen gets wider; the rest live in the More sheet.
      • Desktop (≥1024px): fixed sidebar with every menu.
-->
@props(['title' => 'Dashboard', 'wide' => false])
@php
    $user = auth()->user();
    $items = collect(\App\Support\Nav::realtor());
    $unread = $user->unreadNotifications()->count();
    $barClass = ['always' => '', '600' => 'hidden min-[600px]:flex', '840' => 'hidden min-[840px]:flex'];
    $sheetClass = ['always' => 'hidden', '600' => 'min-[600px]:hidden', '840' => 'min-[840px]:hidden'];
    $isActive = fn ($item) => request()->routeIs($item['match']);
    $left = $items->where('side', 'left')->values();
    $right = $items->where('side', 'right')->values();
    $fab = $items->firstWhere('fab', true);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-app.head :title="$title.' | '.$settings->site_name" :settings="$settings" />
</head>
<body class="app-body">
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-[100] focus:rounded-full focus:bg-brand focus:px-4 focus:py-2 focus:text-brand-fg">Skip to content</a>

<div class="app-shell">

    {{-- ============ Desktop sidebar ============ --}}
    <aside class="app-sidebar" aria-label="Realtor menu">
        <a href="{{ route('realtor.dashboard') }}" class="mb-6 flex items-center gap-2.5 px-2 text-ink">
            @if ($settings->logoUrl())
                <img src="{{ $settings->logoUrl() }}" alt="{{ $settings->site_name }}" class="h-9 w-auto dark:hidden">
                <img src="{{ $settings->logoDarkUrl() }}" alt="" aria-hidden="true" class="hidden h-9 w-auto dark:block">
            @else
                <x-brand-mark class="h-9 w-9" />
                <span class="font-serif text-2xl leading-none">Oku Lands</span>
            @endif
        </a>

        @foreach (['Overview', 'Sales', 'Grow', 'Account'] as $group)
            <p class="side-group">{{ $group }}</p>
            @foreach ($items->where('group', $group) as $item)
                <a href="{{ route($item['route']) }}" class="side-link {{ $isActive($item) ? 'is-active' : '' }}" @if ($isActive($item)) aria-current="page" @endif>
                    <x-icon :name="$item['icon']" class="h-[1.15rem] w-[1.15rem]" />
                    {{ $item['key'] === 'properties' ? 'Listings' : ($item['key'] === 'home' ? 'Overview' : $item['label']) }}
                    @if ($item['key'] === 'alerts' && $unread)<span class="side-badge">{{ $unread }}</span>@endif
                </a>
            @endforeach
        @endforeach

        <div class="mt-auto pt-6">
            <div class="rounded-2xl bg-soft p-4">
                <p class="text-[0.62rem] font-bold uppercase tracking-[0.2em] text-mute">Your referral link</p>
                <p class="mt-1.5 truncate font-mono text-xs text-ink">{{ preg_replace('#^https?://#', '', $user->referralLink()) }}</p>
                <button type="button" data-copy="{{ $user->referralLink() }}" class="btn btn-primary btn-sm mt-3 w-full"><x-icon name="copy" class="h-4 w-4" /><span data-label>Copy link</span></button>
            </div>
            <div class="mt-4 flex items-center gap-3 rounded-2xl px-1.5">
                <x-app.avatar :user="$user" />
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-ink">{{ $user->name }}</p>
                    <p class="truncate text-xs text-mute">{{ $user->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="icon-action" aria-label="Sign out" title="Sign out"><x-icon name="logout" class="h-4 w-4" /></button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ============ Main column ============ --}}
    <div class="flex min-w-0 flex-col">
        <header class="app-topbar">
            <div class="mx-auto flex h-16 w-full max-w-6xl items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                <a href="{{ route('realtor.dashboard') }}" class="flex items-center gap-2.5 text-ink lg:hidden" aria-label="Home">
                    @if ($settings->logoUrl())
                        <img src="{{ $settings->logoUrl() }}" alt="{{ $settings->site_name }}" class="h-8 w-auto dark:hidden">
                        <img src="{{ $settings->logoDarkUrl() }}" alt="" aria-hidden="true" class="hidden h-8 w-auto dark:block">
                    @else
                        <x-brand-mark class="h-8 w-8" />
                        <span class="font-serif text-[1.35rem] leading-none">Oku Lands</span>
                    @endif
                </a>
                <p class="hidden truncate text-sm text-mute lg:block">Hello, <span class="font-bold text-ink">{{ $user->firstName() }}</span> <span aria-hidden="true">👋</span></p>

                <div class="flex items-center gap-2">
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="hidden items-center gap-1.5 rounded-full px-3 py-2 text-[0.8rem] font-semibold text-mute transition hover:text-ink sm:inline-flex">View website <x-icon name="external" class="h-3.5 w-3.5" /></a>
                    <x-theme-toggle class="text-ink" />
                    <a href="{{ route('realtor.notifications') }}" class="icon-btn relative text-ink" aria-label="Alerts{{ $unread ? ' ('.$unread.' unread)' : '' }}">
                        <x-icon name="bell" class="h-[1.15rem] w-[1.15rem]" />
                        @if ($unread)<span class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full bg-flag-500 ring-2 ring-page"></span>@endif
                    </a>
                    <a href="{{ route('realtor.profile') }}" aria-label="Your profile" class="hidden sm:block"><x-app.avatar :user="$user" size="h-10 w-10 text-sm" /></a>
                </div>
            </div>
        </header>

        <main id="main" class="app-main app-content {{ $wide ? 'app-content-wide' : '' }}">
            <x-app.flash />
            {{ $slot }}
        </main>
    </div>
</div>

{{-- ============ Phone / tablet bottom bar ============ --}}
<nav class="bottom-nav" aria-label="Main">
    @foreach ($left as $item)
        <a href="{{ route($item['route']) }}" class="bn-item {{ $barClass[$item['bar']] }} {{ $isActive($item) ? 'is-active' : '' }}" @if ($isActive($item)) aria-current="page" @endif>
            <span class="bn-icon"><x-icon :name="$item['icon']" class="h-[1.3rem] w-[1.3rem]" /></span>
            {{ $item['label'] }}
            @if ($item['key'] === 'alerts' && $unread)<span class="bn-dot"></span>@endif
        </a>
    @endforeach

    @if ($fab)
        <a href="{{ route($fab['route']) }}" class="bn-item bn-fab {{ $isActive($fab) ? 'is-active' : '' }}" aria-label="Share your link">
            <span class="bn-icon"><x-icon :name="$fab['icon']" class="h-6 w-6" /></span>
            {{ $fab['label'] }}
        </a>
    @endif

    @foreach ($right as $item)
        <a href="{{ route($item['route']) }}" class="bn-item {{ $barClass[$item['bar']] }} {{ $isActive($item) ? 'is-active' : '' }}" @if ($isActive($item)) aria-current="page" @endif>
            <span class="bn-icon"><x-icon :name="$item['icon']" class="h-[1.3rem] w-[1.3rem]" /></span>
            {{ $item['label'] }}
        </a>
    @endforeach

    <button type="button" data-sheet-open class="bn-item" aria-haspopup="dialog" aria-label="More menu">
        <span class="bn-icon"><x-icon name="more" class="h-[1.4rem] w-[1.4rem]" /></span>
        More
        @if ($unread)<span class="bn-dot min-[840px]:hidden"></span>@endif
    </button>
</nav>

{{-- ============ "More" sheet: everything not shown in the bar at this screen width ============ --}}
<div class="sheet-backdrop" data-sheet-close aria-hidden="true"></div>
<div class="sheet" data-sheet-panel role="dialog" aria-modal="true" aria-label="More options">
    <div class="sheet-handle" aria-hidden="true"></div>

    <div class="flex items-center gap-3 px-1 pb-4">
        <x-app.avatar :user="$user" size="h-12 w-12 text-base" />
        <div class="min-w-0 flex-1">
            <p class="truncate font-serif text-xl leading-tight text-ink">{{ $user->name }}</p>
            <p class="truncate text-xs text-mute">Code: <span class="font-bold text-ink">{{ $user->referral_code }}</span></p>
        </div>
        <button type="button" data-sheet-close class="icon-action" aria-label="Close"><x-icon name="x" class="h-4 w-4" /></button>
    </div>

    <div class="grid grid-cols-3 gap-2.5">
        @foreach ($items->where('fab', '!=', true) as $item)
            @php $cls = $sheetClass[$item['bar'] ?? ''] ?? ''; @endphp
            <a href="{{ route($item['route']) }}" class="sheet-tile {{ $cls === 'hidden' ? 'hidden' : $cls }} {{ $isActive($item) ? 'is-active' : '' }}"
               @if (! empty($item['external'])) target="_blank" rel="noopener" @endif>
                <span class="ti"><x-icon :name="$item['icon']" class="h-5 w-5" /></span>
                {{ $item['label'] }}
                @if ($item['key'] === 'alerts' && $unread)<span class="rounded-full bg-flag-500 px-1.5 text-[0.65rem] text-white">{{ $unread }}</span>@endif
            </a>
        @endforeach
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="sheet-tile">
            <span class="ti"><x-icon name="external" class="h-5 w-5" /></span>Website
        </a>
    </div>

    <div class="mt-4 space-y-2">
        <div class="flex items-center justify-between rounded-2xl border border-ink/10 bg-page px-4 py-3">
            <span class="flex items-center gap-2.5 text-sm font-bold text-ink"><x-icon name="sun" class="h-[1.1rem] w-[1.1rem] text-brand" /> Appearance</span>
            <x-theme-toggle class="text-ink" />
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl border border-flag-500/30 bg-flag-500/5 px-4 py-3.5 text-sm font-bold text-flag-500 transition active:scale-[0.98]">
                <x-icon name="logout" class="h-[1.1rem] w-[1.1rem]" /> Sign out
            </button>
        </form>
    </div>
</div>

<x-app.confirm />
</body>
</html>
